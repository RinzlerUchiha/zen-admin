<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\IntakeEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Activating and closing controlled entry points.
 *
 * The kiosk is a single standing entry: activating it when one is already live
 * is a no-op rather than a second row, because two live kiosk URLs would be a
 * way to lose track of which one is bookmarked. Job fairs are the opposite —
 * several can be live at once, because two events can run in the same week.
 *
 * Nothing here is ever deleted. Applications point at these rows, and an
 * event's numbers have to survive the event ending.
 */
class IntakeEntryPoints
{
    /** The kiosk, if it is live. */
    public static function activeWalkIn(): ?IntakeEntry
    {
        return IntakeEntry::active()->where('kind', IntakeEntry::KIND_WALK_IN)->latest('activated_at')->first();
    }

    /** Job fairs that are live now, newest first. Several is normal. */
    public static function activeJobFairs()
    {
        return IntakeEntry::active()->where('kind', IntakeEntry::KIND_JOB_FAIR)
            ->orderByDesc('activated_at')->get();
    }

    /** Job fairs that have ended. Kept for their numbers. */
    public static function pastJobFairs()
    {
        return IntakeEntry::whereNotNull('deactivated_at')
            ->where('kind', IntakeEntry::KIND_JOB_FAIR)
            ->orderByDesc('event_date')->orderByDesc('deactivated_at')->get();
    }

    /**
     * Open the office kiosk.
     *
     * Idempotent on purpose: if HR presses Activate while it is already live,
     * they get the existing entry back rather than a second URL. Rotating the
     * token is a deliberate act (deactivate, then activate), not an accident.
     */
    public static function activateWalkIn(?string $empno): IntakeEntry
    {
        $existing = self::activeWalkIn();

        if ($existing) {
            return $existing;
        }

        return IntakeEntry::create([
            'kind' => IntakeEntry::KIND_WALK_IN,
            'label' => null,
            'token' => IntakeEntry::freshToken(),
            'activated_by' => $empno,
            'activated_at' => now(),
        ]);
    }

    /** Open a named job fair. Each activation is its own event and its own token. */
    public static function activateJobFair(array $data, ?string $empno): IntakeEntry
    {
        $label = trim((string) ($data['label'] ?? ''));

        if ($label === '') {
            throw ValidationException::withMessages([
                'label' => 'Give the event a name, so its applications can be told from another fair\'s.',
            ]);
        }

        return IntakeEntry::create([
            'kind' => IntakeEntry::KIND_JOB_FAIR,
            'label' => $label,
            'event_date' => $data['event_date'] ?? null,
            'token' => IntakeEntry::freshToken(),
            'activated_by' => $empno,
            'activated_at' => now(),
        ]);
    }

    /**
     * Close an entry point.
     *
     * The row stays. Its URL stops working on the next request, and the
     * applications already recorded against it keep pointing at it — that is
     * the event's history and closing the event must not erase it.
     */
    public static function deactivate(int $id, ?string $empno): IntakeEntry
    {
        $entry = IntakeEntry::findOrFail($id);

        if ($entry->isActive()) {
            $entry->update([
                'deactivated_by' => $empno,
                'deactivated_at' => now(),
            ]);
        }

        return $entry->fresh();
    }

    /**
     * Reopen a closed entry point under a NEW token.
     *
     * Deliberately not "un-deactivating": the old token stays dead, so QR
     * codes printed for the earlier run cannot be revived. The reopened run is
     * a new row, and the previous run keeps its own applications.
     */
    public static function reactivate(int $id, ?string $empno): IntakeEntry
    {
        $previous = IntakeEntry::findOrFail($id);

        if ($previous->isActive()) {
            return $previous;
        }

        return IntakeEntry::create([
            'kind' => $previous->kind,
            'label' => $previous->label,
            'event_date' => $previous->event_date,
            'token' => IntakeEntry::freshToken(),
            'activated_by' => $empno,
            'activated_at' => now(),
        ]);
    }

    /**
     * How many applications each entry point produced.
     *
     * One query on the applicant connection, keyed by entry id. Cross-database,
     * so counted here and joined in PHP rather than in SQL.
     */
    public static function applicationCounts($entryIds = null): array
    {
        $query = DB::connection('applicant')->table('tblapp_applications')
            ->whereNotNull('intake_entry_id');

        if ($entryIds !== null) {
            $query->whereIn('intake_entry_id', collect($entryIds)->all());
        }

        return $query->selectRaw('intake_entry_id, COUNT(*) as total')
            ->groupBy('intake_entry_id')
            ->pluck('total', 'intake_entry_id')
            ->all();
    }

    /**
     * The URL an applicant follows, and what the QR encodes.
     *
     * Built from the Careers base URL, because the applicant lands on the
     * portal, not on zen-admin. If that is not configured the entry point
     * cannot be used, and saying so is better than printing a QR code that
     * leads nowhere.
     */
    public static function entryUrl(IntakeEntry $entry): ?string
    {
        $base = rtrim((string) config('posting_channels.careers_url', ''), '/');

        if ($base === '') {
            return null;
        }

        return $base . '/careers/enter/' . $entry->token;
    }
}
