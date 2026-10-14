<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\JobPostingChannel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Applicants HR acquired rather than ones who applied on their own.
 *
 * A walk-in does not come through here. They sit at the office PC and
 * register on the Careers Portal like anybody else; the bookmarked link
 * carries `?src=walk_in`, and that tag is the only thing that records the
 * office as their origin. There is deliberately no separate walk-in
 * application flow to maintain.
 *
 * This is for the people who are NOT at a keyboard: someone met at a job
 * fair, someone an employee referred. HR opens the account so the person can
 * sign in later and carry on like any other applicant.
 *
 * Three things stay strictly separate here, because collapsing them is how a
 * hiring system starts lying:
 *
 *   source   where they came from            (source_channel)
 *   outcome  what was decided about them     (status)
 *   work     what is being done right now    (stage, once Phase 3 lands)
 *
 * A job-fair hire is source=job_fair, outcome=Hired, and still has documents
 * outstanding. All three are true at once.
 */
class AcquiredApplicantIntake
{
    /**
     * What happened when HR met this person.
     *
     * `hired` is the job-fair rule: acquiring someone at the event IS the
     * hiring decision, so they must not be walked back through interviews and
     * an offer afterwards. The application opens already decided, and stays
     * open because requirements, exams and onboarding are still ahead of it.
     */
    public const OUTCOME_HIRED = 'hired';
    public const OUTCOME_NOT_SELECTED = 'not_selected';
    public const OUTCOME_PROCESSING = 'processing';

    /** The application status each outcome opens with. */
    private const OUTCOME_STATUS = [
        self::OUTCOME_HIRED => 'Hired',
        self::OUTCOME_NOT_SELECTED => 'Not Selected',
        self::OUTCOME_PROCESSING => 'Applied',
    ];

    /** Sources HR can record here — people and places, not adverts. */
    public static function sources(): array
    {
        return collect(config('posting_channels.channels', []))
            ->filter(fn ($channel) => ($channel['intake'] ?? false) === true)
            ->map(fn ($channel, $key) => [
                'key' => $key,
                'name' => $channel['name'],
                'description' => $channel['description'] ?? '',
                // Only a job fair decides hiring on the spot. Everyone else
                // recorded here joins the ordinary process.
                'offers_outcomes' => $key === 'job_fair',
            ])->values()->all();
    }

    /**
     * Open an account (or reuse one) and record the application.
     *
     * @return array{app_id:int, application_id:int, reused_account:bool, status:string}
     */
    public static function create(array $data, ?string $empno): array
    {
        $source = self::validSource($data['source_channel'] ?? '');
        $outcome = self::validOutcome($source, $data['outcome'] ?? self::OUTCOME_PROCESSING);
        $status = self::OUTCOME_STATUS[$outcome];

        $posting = DB::connection('mysql')->table('tbl_job_posting')
            ->where('id', $data['job_posting_id'])
            ->first();

        if (!$posting) {
            throw ValidationException::withMessages([
                'job_posting_id' => 'That job posting no longer exists.',
            ]);
        }

        if ($posting->status !== 'Published') {
            throw ValidationException::withMessages([
                'job_posting_id' => 'That posting is ' . strtolower($posting->status)
                    . ', so applications cannot be recorded against it.',
            ]);
        }

        $applicantConn = DB::connection('applicant');

        return $applicantConn->transaction(function () use (
            $applicantConn, $data, $posting, $empno, $source, $outcome, $status
        ) {
            $existing = self::findExisting($applicantConn, $data);
            $appId = $existing ? (int) $existing->app_id : self::openAccount($applicantConn, $data);

            /*
            | A blacklisted applicant gets no new application, however they
            | arrived. Checked after the account is found or opened, because
            | the bar is on the person and an existing account is how we know
            | which person this is.
            */
            ApplicantBlacklist::guardNewApplication($appId);

            $alreadyApplied = $applicantConn->table('tblapp_applications')
                ->where('app_id', $appId)
                ->where('job_posting_id', $posting->id)
                ->whereNull('closed_at')
                ->exists();

            if ($alreadyApplied) {
                throw ValidationException::withMessages([
                    'job_posting_id' => 'This applicant already has an open application for that posting.',
                ]);
            }

            $closed = (bool) (config('applications.statuses.' . $status . '.closed') ?? false);

            $applicationId = $applicantConn->table('tblapp_applications')->insertGetId([
                'app_id' => $appId,
                // The tagging: the position comes from the posting HR chose,
                // exactly as the online flow does it.
                'request_position_id' => $posting->request_position_id,
                'job_posting_id' => $posting->id,
                // The outcome, which is NOT the source and NOT the stage.
                'status' => $status,
                'applied_at' => now(),
                // Not Selected is decided and finished in the same moment, so
                // it closes now. Hired is decided but NOT finished —
                // requirements, exams and onboarding are still ahead, so it
                // stays open until the 201 handoff.
                'closed_at' => $closed ? now() : null,
                'closed_by' => $closed ? $empno : null,
                'closed_note' => $closed ? self::noteFor($source) : null,
                'source_channel' => $source,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $applicantConn->table('tblapp_persinfo')
                ->where('app_id', $appId)
                ->update(['app_posapplied' => $posting->posting_title ?? '']);

            /*
            | An application that is still open gets its workflow now, so HR
            | can schedule the Initial Interview against it. Without this the
            | application has no steps at all and the interview page has
            | nothing to offer.
            |
            | A closed one does not: Not Selected at the door is decided and
            | finished, and there is no recruitment process ahead of it to
            | record. Hired stays open, so it does get one -- requirements and
            | exams are still to come.
            */
            if (!$closed) {
                ApplicationWorkflow::initialize(
                    (int) $applicationId,
                    $empno,
                    'Recorded by HR at intake'
                );
            }

            self::countChannelApplication((int) $posting->id, $source);

            return [
                'app_id' => $appId,
                'application_id' => $applicationId,
                'reused_account' => (bool) $existing,
                'status' => $status,
            ];
        });
    }

    /** A source from the catalogue, and one HR is allowed to record by hand. */
    private static function validSource(string $source): string
    {
        $allowed = array_column(self::sources(), 'key');

        if (!in_array($source, $allowed, true)) {
            throw ValidationException::withMessages([
                'source_channel' => 'Choose where this applicant came from.',
            ]);
        }

        return $source;
    }

    /**
     * Only a job fair carries an outcome. Anyone else recorded here joins the
     * ordinary process, so quietly forcing 'processing' is right rather than
     * failing on a field the form never showed.
     */
    private static function validOutcome(string $source, string $outcome): string
    {
        if ($source !== 'job_fair') {
            return self::OUTCOME_PROCESSING;
        }

        if (!array_key_exists($outcome, self::OUTCOME_STATUS)) {
            throw ValidationException::withMessages([
                'outcome' => 'Say what happened at the job fair.',
            ]);
        }

        return $outcome;
    }

    private static function noteFor(string $source): string
    {
        return $source === 'job_fair'
            ? 'Not selected at the job fair.'
            : 'Not selected at intake.';
    }

    /**
     * An account this person already has.
     *
     * tblapp_persinfo has no unique index on email or mobile, and login
     * matches on either and takes the first row — so a second account with the
     * same email would silently shadow the real one. Reusing beats creating.
     */
    private static function findExisting($conn, array $data)
    {
        $email = trim((string) ($data['app_email'] ?? ''));
        $mobile = trim((string) ($data['app_mobile'] ?? ''));

        if ($email === '' && $mobile === '') {
            return null;
        }

        return $conn->table('tblapp_persinfo')
            ->where(function ($q) use ($email, $mobile) {
                if ($email !== '') { $q->orWhere('app_email', $email); }
                if ($mobile !== '') { $q->orWhere('app_mobile', $mobile); }
            })
            ->first();
    }

    /**
     * The account — only what HR can reasonably know when they meet someone.
     *
     * Address, education, employment and government IDs are left empty on
     * purpose: the applicant fills those in themselves once they sign in,
     * through the same forms every other applicant uses. Being hired at a job
     * fair does not mean the paperwork is done, and this is where that starts
     * being true.
     */
    private static function openAccount($conn, array $data): int
    {
        return (int) $conn->table('tblapp_persinfo')->insertGetId([
            'app_lname' => $data['app_lname'],
            'app_fname' => $data['app_fname'] ?? '',
            'app_mname' => $data['app_mname'] ?? '',
            'app_email' => $data['app_email'] ?? '',
            'app_mobile' => $data['app_mobile'] ?? '',
            // The column the portal authenticates against. Hashed here, never
            // stored or logged in the clear.
            'app_code' => Hash::make($data['app_code']),
            'app_posapplied' => '',
            'app_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Count this application against the source it came from.
     *
     * These people never pass through a tagged Careers link, so nothing would
     * count them otherwise and "where do our applicants come from" would omit
     * everyone HR met in person. The row is created on demand because nobody
     * advertises on a job fair the way they advertise on LinkedIn.
     */
    private static function countChannelApplication(int $postingId, string $source): void
    {
        JobPostingChannel::firstOrCreate(
            ['job_posting_id' => $postingId, 'channel_key' => $source],
            ['state' => JobPostingChannel::NOT_POSTED, 'visits' => 0, 'applications' => 0]
        )->increment('applications');
    }
}
