<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Barring an applicant from new applications, and letting them back in.
 *
 * The blacklist is about the PERSON, not one of their applications. Barring
 * somebody from a single posting is what ReapplicationPolicy's cooldown already
 * does after a Not Selected; this is the wider judgement that HR does not want
 * this person coming back at the moment, whatever the vacancy.
 *
 * It is written as a history. Every decision is its own row and no row is ever
 * rewritten to undo one: lifting fills in the lifting fields on the row being
 * lifted, and barring somebody again writes a new row. So the table always
 * answers "who barred them, why, when, and who let them back in" rather than
 * only "are they barred now".
 *
 * WHAT IT DOES NOT TOUCH
 *
 * Applications already made, and everything hanging off them -- interviews,
 * evaluations, assessments, documents -- are left exactly as they are. They
 * record things that actually happened. A blacklist stops the NEXT application
 * being created; concluding work already under way is a separate decision HR
 * makes deliberately, and quietly closing it here would destroy history in the
 * name of tidiness.
 */
class ApplicantBlacklist
{
    /** Is this applicant barred right now? */
    public static function isBlacklisted(int $appId): bool
    {
        return self::current($appId) !== null;
    }

    /** The decision in force, if there is one. */
    public static function current(int $appId): ?object
    {
        return DB::connection('applicant')->table('tblapp_blacklist')
            ->where('app_id', $appId)
            ->whereNull('lifted_at')
            ->orderByDesc('id')
            ->first();
    }

    /** Everything ever decided about this applicant, newest first. */
    public static function history(int $appId)
    {
        return DB::connection('applicant')->table('tblapp_blacklist')
            ->where('app_id', $appId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Bar an applicant.
     *
     * A reason is required. A blacklist that does not say why cannot be
     * reviewed, argued with or safely lifted by anybody but its author, so it
     * is not optional.
     */
    public static function add(int $appId, string $reason, string $empno): object
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Give a reason for blacklisting this applicant. It is kept on the record.',
            ]);
        }

        $applicant = DB::connection('applicant')->table('tblapp_persinfo')
            ->where('app_id', $appId)->first(['app_id']);

        if (!$applicant) {
            throw ValidationException::withMessages([
                'applicant' => 'That applicant could not be found.',
            ]);
        }

        if (self::isBlacklisted($appId)) {
            throw ValidationException::withMessages([
                'applicant' => 'This applicant is already blacklisted.',
            ]);
        }

        $id = DB::connection('applicant')->table('tblapp_blacklist')->insertGetId([
            'app_id' => $appId,
            'reason' => $reason,
            'blacklisted_by' => $empno,
            'blacklisted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::connection('applicant')->table('tblapp_blacklist')->find($id);
    }

    /**
     * Let them back in.
     *
     * The row is not deleted and the original reason is not overwritten: the
     * record has to keep saying that this applicant WAS barred and why, or
     * lifting it would quietly erase the decision rather than reverse it.
     */
    public static function lift(int $appId, string $reason, string $empno): void
    {
        $current = self::current($appId);

        if (!$current) {
            throw ValidationException::withMessages([
                'applicant' => 'This applicant is not currently blacklisted.',
            ]);
        }

        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Give a reason for lifting the blacklist. It is kept on the record.',
            ]);
        }

        DB::connection('applicant')->table('tblapp_blacklist')
            ->where('id', $current->id)
            ->update([
                'lifted_at' => now(),
                'lifted_by' => $empno,
                'lift_reason' => $reason,
                'updated_at' => now(),
            ]);
    }

    /**
     * Refuse a new application for a barred applicant.
     *
     * Called where an application is about to be created. The message names
     * nothing from the reason: that is HR's internal note, and the person in
     * front of them should hear it from HR rather than read it off a screen.
     */
    public static function guardNewApplication(int $appId): void
    {
        if (self::isBlacklisted($appId)) {
            throw ValidationException::withMessages([
                'applicant' => 'This applicant is blacklisted, so a new application cannot be recorded for them. '
                    . 'Lift the blacklist first if that is no longer right.',
            ]);
        }
    }

    /** Which of these applicants are barred — for lists, without N+1 queries. */
    public static function flagsFor(array $appIds): array
    {
        if ($appIds === []) {
            return [];
        }

        return DB::connection('applicant')->table('tblapp_blacklist')
            ->whereIn('app_id', $appIds)
            ->whereNull('lifted_at')
            ->pluck('app_id')
            ->map(fn ($id) => (int) $id)
            ->flip()
            ->map(fn () => true)
            ->all();
    }
}
