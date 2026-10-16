<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use Illuminate\Support\Facades\DB;

/**
 * The mechanical pre-screen, before anybody looks at qualifications.
 *
 * Narrow and factual on purpose. Every question here has a yes-or-no answer
 * the system already holds: is this application open, are the documents it
 * asked for in, is the posting still open, is the applicant blacklisted, is a
 * cooldown running. None of it is a judgement about the person.
 *
 * IT ADVISES; IT DOES NOT REJECT
 *
 * Nothing here closes an application or removes anybody from a list. It
 * returns findings for HR to read, because every one of these can have a
 * reason behind it that the system does not know — a document chased by
 * phone, a posting about to be extended.
 */
class EligibilityCheck
{
    /**
     * @return array{clear:bool,findings:array<int,array{key:string,severity:string,detail:string}>}
     */
    public static function forApplication(int $applicationId): array
    {
        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            return self::result([[
                'key' => 'missing',
                'severity' => 'blocking',
                'detail' => 'That application could not be found.',
            ]]);
        }

        $findings = [];

        if ($application->is_closed) {
            $findings[] = [
                'key' => 'closed',
                'severity' => 'blocking',
                'detail' => 'This application is already closed as ' . $application->status . '.',
            ];
        }

        /* Documents: read from the existing review, never recounted here. */
        $documents = ApplicantDocumentReview::summary((int) $application->app_id);

        if (!($documents['complete'] ?? false)) {
            $findings[] = [
                'key' => 'documents',
                'severity' => 'attention',
                'detail' => ($documents['required_accepted'] ?? 0) . ' of '
                    . ($documents['required_total'] ?? 0) . ' required documents accepted.',
            ];
        }

        /* The posting they applied to. A closed one does not disqualify the
           applicant — it means this application has nowhere to go. */
        if ($application->job_posting_id) {
            $posting = DB::table('tbl_job_posting')
                ->where('id', $application->job_posting_id)
                ->first(['posting_title', 'status']);

            if ($posting && $posting->status !== 'Published') {
                $findings[] = [
                    'key' => 'posting',
                    'severity' => 'attention',
                    'detail' => 'The posting is ' . strtolower((string) $posting->status)
                        . ', not published.',
                ];
            }
        }

        if (ApplicantBlacklist::isBlacklisted((int) $application->app_id)) {
            $findings[] = [
                'key' => 'blacklist',
                'severity' => 'blocking',
                'detail' => 'This applicant is blacklisted.',
            ];
        }

        return self::result($findings);
    }

    private static function result(array $findings): array
    {
        return [
            /* Clear means nothing mechanical is in the way. It does not mean
               shortlist them: that is a judgement, made elsewhere, by a
               person. */
            'clear' => !collect($findings)->contains(fn ($f) => $f['severity'] === 'blocking'),
            'findings' => $findings,
        ];
    }
}
