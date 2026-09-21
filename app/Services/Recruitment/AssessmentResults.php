<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantAssessmentAttempt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Where each of an applicant's eleven assessments stands — read-only, for the
 * applicant profile's assessment menu and the header of each result tab.
 *
 * A result row in the assessment's own table is what counts as taken (results
 * from before attempt tracking have no attempt row). The attempt adds how it
 * ended and how long it took. Nothing here writes.
 */
class AssessmentResults
{
    /** @return Collection<string, object> keyed by assessment key, in menu order */
    public static function summaries(int $appId): Collection
    {
        $attempts = ApplicantAssessmentAttempt::where('app_id', $appId)->get()->keyBy('assessment');
        $db = DB::connection('applicant');

        return collect(config('applicant_assessments.list'))->map(function (array $def, string $key) use ($appId, $attempts, $db) {
            $row = $db->table($def['table'])->where('app_id', $appId)->first([$def['date']]);
            $attempt = $attempts->get($key);

            $status = match (true) {
                $row !== null => $attempt?->status === 'timed_out' ? 'timed_out' : 'submitted',
                $attempt !== null => $attempt->current_status,
                default => 'not_started',
            };
            $taken = $attempt?->ended_at ?? ($row && $row->{$def['date']} ? Carbon::parse($row->{$def['date']}) : null);

            return (object) [
                'key' => $key,
                'label' => $def['label'],
                'tab' => $def['tab'],
                'kind' => $def['kind'],
                'items' => $def['items'] ?? null,
                'hasResult' => $row !== null,
                'status' => $status,
                'badge' => config("applicant_assessments.badges.$status"),
                'takenOn' => $taken,
                // Only a time from the attempt; a date-only legacy result has none.
                'takenAtKnown' => $attempt?->ended_at !== null,
                'attempt' => $attempt,
            ];
        });
    }
}
