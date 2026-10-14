<?php

namespace App\Services\Recruitment;

use Illuminate\Support\Collection;

/**
 * Several manpower requests asking for the SAME position, read as one.
 *
 * Two departments — or the same one twice in a month — can each raise a request
 * for a Junior Programmer. Those are two separate requests with their own
 * approvals, and they must stay that way. But to the person doing the posting
 * they are one job that is wanted three times, and the list used to show them
 * as unrelated rows under different MR numbers, so the real figure had to be
 * worked out by hand.
 *
 * This groups them for READING ONLY. Nothing here writes, merges or alters a
 * record:
 *
 *   - every tbl_manpower_request_position row stays exactly as it is
 *   - every posting keeps its own request_position_id, so each posting still
 *     points at the one request it was raised from
 *   - the Served rule is untouched: a request is served when a position of its
 *     own has a posting, which this does not change
 *
 * The grouping key is jobspec_id, not the position name. The name is free text
 * on the request ("J. DA", "MIS-JPROG") and two people writing the same job
 * differently would not group, while one person reusing a name for a different
 * job would group things that are not the same. The jobspec is the catalogue
 * entry the position was raised against, so it is the identity that means
 * "the same job".
 *
 * Positions with no jobspec are never grouped with anything, including each
 * other: without the catalogue entry there is nothing to say they are the same
 * job, and guessing would quietly merge unrelated vacancies.
 */
class PositionGrouping
{
    /**
     * Group positions by the job they are for.
     *
     * @param  Collection  $positions  HireflowManpowerPosition rows, with `request` loaded
     * @return Collection  one entry per job, ordered by title
     */
    public static function byJob(Collection $positions): Collection
    {
        return $positions
            ->groupBy(fn ($p) => self::keyFor($p))
            ->map(function (Collection $group) {
                $first = $group->first();

                /*
                | Sources, one per contributing request. A request appearing
                | twice (two positions for the same job on one MR) is kept as
                | two lines rather than summed away: they were raised
                | separately and may be replacement and additional.
                */
                $sources = $group
                    ->sortBy(fn ($p) => $p->request?->mr_no ?? '')
                    ->map(fn ($p) => [
                        'position_id' => (int) $p->id,
                        'request_id' => (int) $p->request_id,
                        'mr_no' => $p->request?->mr_no ?? '—',
                        'request_status' => $p->request?->status,
                        'type' => $p->type,
                        'headcount' => (int) $p->headcount,
                        'filled' => (int) $p->filled,
                    ])
                    ->values();

                return [
                    'jobspec_id' => $first->jobspec_id ? (int) $first->jobspec_id : null,
                    'title' => $first->positionTitle(),
                    'code' => $first->position,
                    'headcount' => $sources->sum('headcount'),
                    'filled' => $sources->sum('filled'),
                    'request_count' => $sources->pluck('request_id')->unique()->count(),
                    'is_combined' => $sources->pluck('request_id')->unique()->count() > 1,
                    'sources' => $sources->all(),
                ];
            })
            ->sortBy('title')
            ->values();
    }

    /**
     * How many heads this job is wanted for in total, and by whom — for a
     * posting that has already been created.
     *
     * A posting points at one position, so on its own it can only report that
     * position's headcount. This answers the wider question the posting sits
     * inside: how many of this job are wanted altogether.
     *
     * Only positions on requests that still stand are counted. A cancelled or
     * declined request is no longer asking for anybody, so letting it keep
     * inflating the figure would have HR posting for headcount that nobody
     * wants any more. Its own posting, if one was made, is left alone — that
     * is a decision for HR, not an inference from this.
     */
    public static function forPosting($posting, Collection $allPositions): ?array
    {
        $position = $posting->hireflowPosition;

        if (!$position) {
            return null;
        }

        $key = self::keyFor($position);

        $group = $allPositions->filter(
            fn ($p) => self::keyFor($p) === $key && self::stillStands($p)
        );

        if ($group->count() < 2) {
            return null;   // nothing combined; the posting speaks for itself
        }

        return self::byJob($group)->first();
    }

    /** Requests that have not been called off. */
    public static function stillStands($position): bool
    {
        return !in_array($position->request?->status, ['Cancelled', 'Declined'], true);
    }

    /**
     * The identity of the job. Positions without a jobspec get a key of their
     * own so they never group with anything.
     */
    private static function keyFor($position): string
    {
        return $position->jobspec_id
            ? 'spec:' . $position->jobspec_id
            : 'pos:' . $position->id;
    }
}
