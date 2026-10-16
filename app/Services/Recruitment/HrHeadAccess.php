<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Facades\DB;

/**
 * Who may approve an offer.
 *
 * WHY THIS READS tbl_manpower_users
 *
 * The HR Head role already exists, in portal_db, and is already assigned
 * through Zen-Hub's own admin screen. Inventing a second table to say the same
 * thing would mean two places to keep in step and two answers to the same
 * question whenever they drifted.
 *
 * WHY IT IS NOT User::userAccess()
 *
 * That resolves HRIS rights from tngc_hrd2, which is read-only to us and
 * governs what HR staff may do in zen-admin. Approving an offer is a different
 * authority held by different people, and widening userAccess() to carry it
 * would change every existing gate. Nothing in that path is touched.
 *
 * WHY THE API RE-CHECKS IT
 *
 * Zen-Hub gates its own page on this role too. That check is necessary but not
 * sufficient: it runs in the other application, on the other side of the
 * bridge. A forged employee number in a signed request would still be refused
 * here, because this is the side that owns the offer.
 */
class HrHeadAccess
{
    /** The one role that may approve, decline or send back an offer. */
    public const ROLE = 'HR Head';

    /**
     * Is this employee an active HR Head?
     *
     * Read on every request rather than cached: an account disabled mid-session
     * must stop working immediately, which is how the rest of the system treats
     * a cleared is_active too.
     */
    public static function isApprover(?string $empno): bool
    {
        $empno = trim((string) $empno);

        if ($empno === '') {
            return false;
        }

        return DB::table('tbl_manpower_users')
            ->where('employee_id', $empno)
            ->where('manpower_role', self::ROLE)
            ->where('is_active', 1)
            ->exists();
    }

    /**
     * Every active HR Head, for an administrative screen to show.
     *
     * Deliberately not used to decide anything — authorisation is always a
     * question about one employee, asked at the moment they act.
     */
    public static function approvers(): array
    {
        return DB::table('tbl_manpower_users')
            ->where('manpower_role', self::ROLE)
            ->where('is_active', 1)
            ->pluck('employee_id')
            ->all();
    }
}
