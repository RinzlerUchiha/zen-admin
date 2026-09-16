<?php

namespace App\Services\Recruitment;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/**
 * The deadline an applicant is given to complete their documents.
 *
 * Counted in calendar days: a weekend is an ordinary day and counts, because an
 * applicant can upload a file on a Sunday. A Philippine public holiday does not
 * count — the clock stops for it and the deadline moves out by a day.
 *
 * The holidays come from the calendar HR already maintains in
 * Events > Holiday (tngc_hrd2.tbl_holiday). Only the nationwide scope is used:
 * the other scopes are branch holidays, and an applicant is not attached to a
 * branch while they are applying. Nothing here keeps its own list of dates.
 */
class HolidayAwareDeadline
{
    /**
     * @param  int  $days  Calendar days to allow, holidays not counted.
     */
    public static function from(Carbon|CarbonImmutable|string $start, int $days): Carbon
    {
        $days = max(1, $days);
        $cursor = CarbonImmutable::parse($start)->startOfDay();

        // A window wide enough for the days asked for plus every holiday that
        // could fall inside it, so the calendar is read once.
        $holidays = self::holidaysBetween($cursor, $cursor->addDays($days * 2 + 30));

        $remaining = $days;

        while ($remaining > 0) {
            $cursor = $cursor->addDay();

            // A holiday is not one of the days the applicant was given.
            if (!isset($holidays[$cursor->toDateString()])) {
                $remaining--;
            }
        }

        // Landing on a holiday would hand back a day that was never counted.
        while (isset($holidays[$cursor->toDateString()])) {
            $cursor = $cursor->addDay();
        }

        // The last second of the day, not endOfDay(): that carries .999999
        // microseconds, which a whole-second timestamp column rounds UP — the
        // stored deadline would silently land on the following day.
        return Carbon::parse($cursor->setTime(23, 59, 59));
    }

    /**
     * The holidays that stop the clock, as [Y-m-d => name].
     */
    public static function holidaysBetween(
        Carbon|CarbonImmutable|string $from,
        Carbon|CarbonImmutable|string $to
    ): array {
        $scope = config('applicant_documents.completion.holiday_scope', '#all');

        return Holiday::query()
            ->whereBetween('date', [
                CarbonImmutable::parse($from)->toDateString(),
                CarbonImmutable::parse($to)->toDateString(),
            ])
            ->where('holiday_scope', $scope)
            ->pluck('holiday', 'date')
            ->mapWithKeys(fn ($name, $date) => [CarbonImmutable::parse($date)->toDateString() => $name])
            ->all();
    }
}
