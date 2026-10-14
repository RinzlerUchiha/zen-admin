<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationRecommendation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Recommending an applicant from one application to another job post.
 *
 * HR interviews someone for one role and concludes they would do better in
 * another. That judgement creates a NEW application for the other posting, with
 * its own workflow starting from the beginning. The original application is
 * never rewritten: its interviews, stage history and outcome stay exactly as
 * they were, permanently.
 *
 * Nothing is copied between the two. The applicant genuinely has to go through
 * the other posting's process — a candidate who passed a warehouse interview has
 * not thereby passed a logistics one — and merging the two histories would make
 * both unreadable.
 *
 * The whole operation is atomic. A recommendation that cannot produce an
 * application leaves no trace at all, and the original application untouched
 * and open: HR sees why it failed and can correct it, rather than being left
 * with a half-made record.
 */
class ApplicationRecommendationService
{
    /**
     * Eligible target postings for this application.
     *
     * Eligibility is `status = 'Published'`, which is the system's existing and
     * only definition of a posting that accepts applications — the same rule
     * the Careers portal and HR intake already apply. Headcount is deliberately
     * not consulted: `filled` is not yet maintained, so using it would silently
     * hide postings that are in fact still hiring.
     *
     * Ineligible postings are returned too, each with the reason, so HR can see
     * why the one they were looking for cannot be chosen. A posting that simply
     * vanishes from the list reads as a broken screen.
     *
     * @return array<int, array{id:int,title:string,department:?string,eligible:bool,reason:?string}>
     */
    public static function targetsFor(int $applicationId): array
    {
        $application = ApplicantApplication::find($applicationId);

        if (!$application) {
            throw ValidationException::withMessages(['application' => 'That application could not be found.']);
        }

        /* Postings this applicant already has an OPEN application for. The
           database refuses a second one, so offering them would be a trap. */
        $openPostings = ApplicantApplication::where('app_id', $application->app_id)
            ->whereNull('closed_at')
            ->pluck('job_posting_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        /* Postings already recommended from THIS application. */
        $already = ApplicationRecommendation::fromApplication($applicationId)
            ->pluck('target_job_posting_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $postings = DB::table('tbl_job_posting as p')
            ->leftJoin('tbl_manpower_request_position as rp', 'rp.id', '=', 'p.request_position_id')
            ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'rp.request_id')
            ->leftJoin('tbl_department as d', 'd.Dept_Code', '=', 'r.department_id')
            ->where('p.status', 'Published')
            ->orderBy('d.Dept_Name')
            ->orderBy('p.posting_title')
            ->get([
                'p.id',
                'p.posting_title',
                'p.request_position_id',
                'd.Dept_Name as department',
                // Two postings can carry the same title — two openings for the
                // same role, raised on different manpower requests. The MR
                // number is what tells them apart, and without it HR is
                // choosing between identical-looking lines.
                'r.mr_no',
            ]);

        $sourcePosting = (int) $application->job_posting_id;

        return $postings->map(function ($p) use ($sourcePosting, $openPostings, $already) {
            $id = (int) $p->id;

            /* The reasons, most specific first — HR should be told the most
               informative one, not merely the first that happens to match. */
            $reason = match (true) {
                $id === $sourcePosting => 'This is the current job post',
                in_array($id, $already, true) => 'Already recommended',
                in_array($id, $openPostings, true) => 'Applicant already has an open application',
                default => null,
            };

            return [
                'id' => $id,
                'title' => $p->posting_title ?? 'Untitled posting',
                'mr_no' => $p->mr_no,
                'department' => $p->department,
                'eligible' => $reason === null,
                'reason' => $reason,
            ];
        })->values()->all();
    }

    /**
     * Record the recommendation and create the application it points to.
     *
     * Everything happens inside the applicant's lock and one transaction: the
     * recommendation, the new application, and that application's baseline
     * workflow. If any part fails, none of it happened.
     *
     * @return array{recommendation: ApplicationRecommendation, application: ApplicantApplication}
     */
    public static function recommend(int $sourceApplicationId, int $targetPostingId, string $empno, ?string $reason = null): array
    {
        $source = ApplicantApplication::find($sourceApplicationId);

        if (!$source) {
            throw ValidationException::withMessages(['application' => 'That application could not be found.']);
        }

        return ApplicationDecision::inLock($source->app_id, function () use ($source, $sourceApplicationId, $targetPostingId, $empno, $reason) {
            /*
            | A blacklisted applicant gets no new application, from here either.
            |
            | This is the third and last place an application can be created --
            | Careers apply and HR intake are the other two, and both have
            | asked this question for some time. This one did not, so a bar
            | that stopped somebody applying and stopped HR entering them by
            | hand could still be walked straight past by recommending them
            | onward. The bar is about the person, so every door has to ask.
            |
            | Inside the lock and before anything is written, like the other
            | two: a bar applied in the seconds while HR types a reason has to
            | count. The whole operation is one transaction, so a refusal here
            | leaves no recommendation, no application and no workflow -- the
            | source application is untouched and still open.
            |
            | Deliberately server-side and not merely hidden in the dialog. The
            | dialog says why before HR types (see the controller's `blocked`),
            | but a request that never opened the dialog is refused here.
            */
            ApplicantBlacklist::guardNewApplication((int) $source->app_id);

            /*
            | Eligibility is re-checked HERE, inside the lock, not when the
            | selector was drawn. A posting can be closed, or another
            | application created, in the seconds while HR types a reason — and
            | the rules must hold against what is true at the moment of writing.
            */
            /*
            | A CLOSED application may still recommend the applicant onward.
            |
            | Closing an application ends that application, not the company's
            | interest in the person: HR often realises after marking someone
            | Not Selected for one post that they suit another, and the record
            | of where that idea came from belongs on the application it came
            | from. Blocking it would push HR into creating the new application
            | by hand, losing the link entirely.
            |
            | Nothing about the source is touched — it is not reopened, its
            | status is not rewritten, and its history stands as it was.
            |
            | Also deliberately NOT a check on workflow steps: an application
            | that never entered the Phase 3 workflow is still a real
            | application, and HR must be able to recommend from it.
            */
            $posting = self::eligiblePostingOrFail($source, $sourceApplicationId, $targetPostingId);

            $recommendation = ApplicationRecommendation::create([
                'source_application_id' => $sourceApplicationId,
                'target_job_posting_id' => $targetPostingId,
                'recommended_by' => $empno,
                'recommended_at' => now(),
                'reason' => $reason,
            ]);

            $applicationId = DB::connection('applicant')->table('tblapp_applications')->insertGetId([
                'app_id' => $source->app_id,
                /* Tagged from the posting HR chose, exactly as every other
                   application-creating path does it. */
                'request_position_id' => $posting->request_position_id,
                'job_posting_id' => $posting->id,

                /* A new application starts at the beginning. It does not inherit
                   the source's status, and being recommended is not progress. */
                'status' => ApplicantApplication::APPLIED,
                'applied_at' => now(),

                /*
                | The channel records HOW THE APPLICANT REACHED THE COMPANY,
                | which a recommendation does not change: someone who found us
                | on LinkedIn and was moved between postings still came from
                | LinkedIn. It is inherited rather than set to 'referral', which
                | means an outside referral and would misreport the source.
                | Where this recommendation came from is the recommendation row.
                */
                'source_channel' => $source->source_channel,

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recommendation->update(['target_application_id' => $applicationId]);

            /*
            | Its own workflow, from the baseline. Nothing is carried over from
            | the source: the applicant starts this posting's process at Initial
            | Interview even if they completed three rounds for the other one.
            */
            ApplicationWorkflow::initialize(
                (int) $applicationId,
                $empno,
                'Created from a recommendation on application #' . $sourceApplicationId
            );

            return [
                'recommendation' => $recommendation->fresh(),
                'application' => ApplicantApplication::find($applicationId),
            ];
        });
    }

    /**
     * The target posting, if it may be recommended to right now.
     *
     * Every rule refuses with a sentence HR can act on, because each one
     * describes a situation they can actually resolve — pick another posting,
     * or deal with the application that already exists.
     */
    private static function eligiblePostingOrFail(ApplicantApplication $source, int $sourceApplicationId, int $targetPostingId)
    {
        if ($targetPostingId === (int) $source->job_posting_id) {
            throw ValidationException::withMessages([
                'target_job_posting_id' => 'That is the job post this application is already for.',
            ]);
        }

        $posting = DB::table('tbl_job_posting')->where('id', $targetPostingId)->first();

        if (!$posting) {
            throw ValidationException::withMessages([
                'target_job_posting_id' => 'That job post could not be found.',
            ]);
        }

        /* The same check the Careers portal makes at the moment of applying. */
        if ($posting->status !== 'Published') {
            throw ValidationException::withMessages([
                'target_job_posting_id' => 'That job post is no longer published, so it cannot accept applications.',
            ]);
        }

        /* One OPEN application per posting is enforced by the database; caught
           here so HR gets an explanation instead of an integrity error. */
        $openElsewhere = ApplicantApplication::where('app_id', $source->app_id)
            ->where('job_posting_id', $targetPostingId)
            ->whereNull('closed_at')
            ->exists();

        if ($openElsewhere) {
            throw ValidationException::withMessages([
                'target_job_posting_id' => 'This applicant already has an open application for that job post.',
            ]);
        }

        /* Guarded by a unique index too; caught here for the same reason. */
        $duplicate = ApplicationRecommendation::where('source_application_id', $sourceApplicationId)
            ->where('target_job_posting_id', $targetPostingId)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'target_job_posting_id' => 'This application has already been recommended to that job post.',
            ]);
        }

        return $posting;
    }

    /**
     * Where an application came from, if a recommendation created it.
     *
     * One hop back. Following it repeatedly walks a chain — B came from A, A
     * came from nothing — which is enough for the provenance line HR reads, and
     * avoids holding a whole graph to answer a one-line question.
     *
     * @return array{recommendation: ApplicationRecommendation, source: ?ApplicantApplication, posting_title: ?string}|null
     */
    public static function provenanceOf(int $applicationId): ?array
    {
        $recommendation = ApplicationRecommendation::thatCreated($applicationId)->first();

        if (!$recommendation) {
            return null;
        }

        $source = ApplicantApplication::find($recommendation->source_application_id);

        return [
            'recommendation' => $recommendation,
            'source' => $source,
            'posting_title' => $source
                ? DB::table('tbl_job_posting')->where('id', $source->job_posting_id)->value('posting_title')
                : null,
        ];
    }

    /**
     * Both directions of the recommendation link, for one set of applications.
     *
     * Two queries for the whole set rather than two per application, because
     * every screen that shows this shows a list. Returns, keyed by application
     * id: where it came from, and what came of it.
     *
     * @param  iterable  $applicationIds
     * @return array<int, array{from: ?array, to: array}>
     */
    public static function linksFor($applicationIds): array
    {
        $ids = collect($applicationIds)->map(fn ($id) => (int) $id)->filter()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $incoming = ApplicationRecommendation::whereIn('target_application_id', $ids)->get();
        $outgoing = ApplicationRecommendation::whereIn('source_application_id', $ids)->get();

        /* Posting titles for both ends, in one lookup. */
        $sourceApplications = ApplicantApplication::whereIn('id', $incoming->pluck('source_application_id'))
            ->get()->keyBy('id');

        $postingIds = $sourceApplications->pluck('job_posting_id')
            ->merge($outgoing->pluck('target_job_posting_id'))
            ->filter()->unique()->all();

        $titles = $postingIds
            ? DB::table('tbl_job_posting')->whereIn('id', $postingIds)->pluck('posting_title', 'id')
            : collect();

        $links = [];

        foreach ($incoming as $r) {
            $sourceApplication = $sourceApplications[$r->source_application_id] ?? null;

            $links[(int) $r->target_application_id]['from'] = [
                'application_id' => (int) $r->source_application_id,
                /* The post it came FROM — what HR recognises, far more than an
                   application number. */
                'posting_title' => $sourceApplication
                    ? ($titles[$sourceApplication->job_posting_id] ?? 'a job post no longer listed')
                    : 'a job post no longer listed',
                'recommended_at' => optional($r->recommended_at)->toDateString(),
                'recommended_by' => $r->recommended_by,
                'reason' => $r->reason,
            ];
        }

        foreach ($outgoing as $r) {
            $links[(int) $r->source_application_id]['to'][] = [
                'application_id' => $r->target_application_id ? (int) $r->target_application_id : null,
                'posting_title' => $titles[$r->target_job_posting_id] ?? 'a job post no longer listed',
                'recommended_at' => optional($r->recommended_at)->toDateString(),
                'reason' => $r->reason,
            ];
        }

        /* Both keys always present, so callers never test for absence. */
        foreach ($links as $id => $link) {
            $links[$id] = [
                'from' => $link['from'] ?? null,
                'to' => $link['to'] ?? [],
            ];
        }

        return $links;
    }

    /**
     * What came of an application — every recommendation made from it.
     *
     * Several is normal: one interview can conclude that a candidate suits two
     * other openings.
     */
    public static function madeFrom(int $applicationId): array
    {
        $recommendations = ApplicationRecommendation::fromApplication($applicationId)->get();

        if ($recommendations->isEmpty()) {
            return [];
        }

        $titles = DB::table('tbl_job_posting')
            ->whereIn('id', $recommendations->pluck('target_job_posting_id')->all())
            ->pluck('posting_title', 'id');

        return $recommendations->map(fn ($r) => [
            'recommendation' => $r,
            'target_application_id' => $r->target_application_id,
            'posting_title' => $titles[$r->target_job_posting_id] ?? 'Untitled posting',
            'recommended_at' => $r->recommended_at,
            'recommended_by' => $r->recommended_by,
            'reason' => $r->reason,
        ])->values()->all();
    }
}
