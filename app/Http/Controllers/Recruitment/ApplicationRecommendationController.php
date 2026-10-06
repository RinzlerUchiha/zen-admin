<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\ApplicationDecision;
use App\Services\Recruitment\ApplicationRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HR recommending an applicant from one application to another job post.
 *
 * Authorization is on the routes (can:applicant-applications.decide) — the same
 * right that lets HR withdraw an application or mark it Not Selected, because
 * this is the same kind of decision about the same applicant.
 *
 * Recommending creates the other application; it does NOT close this one. HR
 * normally talks the options over with the applicant, who may still prefer the
 * original post, so closing it is a separate action they take afterwards.
 */
class ApplicationRecommendationController extends Controller
{
    /** The postings HR can choose from, with reasons for those they cannot. */
    public function targets($application)
    {
        return response()->json([
            'targets' => ApplicationRecommendationService::targetsFor((int) $application),
        ]);
    }

    /**
     * Make the recommendation.
     *
     * On any failure the service leaves nothing behind and this application
     * untouched, so the error is simply shown and HR can choose again.
     */
    public function store(Request $request, $application)
    {
        $validated = $request->validate([
            'target_job_posting_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        $result = ApplicationRecommendationService::recommend(
            (int) $application,
            (int) $validated['target_job_posting_id'],
            $this->empno(),
            $validated['reason'] ?? null
        );

        $title = DB::table('tbl_job_posting')
            ->where('id', $result['recommendation']->target_job_posting_id)
            ->value('posting_title');

        /*
        | Name the post, say the application exists, and say what has NOT
        | happened — HR's next question is always whether this closed the
        | original, and answering it here saves them checking.
        |
        | What is true of the original depends on the original. Recommending is
        | allowed from a CLOSED application as well as an open one, and telling
        | HR a closed application "is still open — close it as Recommended"
        | asks them to do something they already did, about a state that is not
        | the case.
        */
        $source = ApplicantApplication::find((int) $application);
        $sourceTitle = $this->sourceTitle((int) $application) ?: 'the original post';

        $aboutSource = $source && $source->closed_at === null
            ? sprintf('Their application for %s is still open — close it as Recommended when they have decided.', $sourceTitle)
            : sprintf('Their application for %s remains closed; it was not reopened.', $sourceTitle);

        return back()->with('success', sprintf(
            'Application created for %s. %s now has an application for this position. %s',
            $title ?: 'the selected job post',
            $this->applicantName($result['application']),
            $aboutSource
        ));
    }

    /**
     * Close this application as Recommended.
     *
     * Separate from making the recommendation, and only offered once one has
     * succeeded — the status means "ended because the applicant was recommended
     * onward", which is only true if that actually happened.
     */
    public function close(Request $request, $application)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $made = ApplicationRecommendationService::madeFrom((int) $application);

        if (empty($made)) {
            return back()->withErrors([
                'application' => 'This application has not been recommended anywhere, so it cannot be closed as Recommended.',
            ]);
        }

        $closed = ApplicationDecision::recommended(
            (int) $application,
            $this->empno(),
            $validated['note'] ?? null
        );

        $went = collect($made)->pluck('posting_title')->filter()->implode(', ');

        return back()->with('success', sprintf(
            "%s's application has been closed as Recommended%s. No waiting period applies.",
            $this->applicantName($closed),
            $went !== '' ? ' — they are going forward for ' . $went : ''
        ));
    }

    /** The post the recommendation came from, for the confirmation message. */
    private function sourceTitle(int $applicationId): ?string
    {
        $source = ApplicantApplication::find($applicationId);

        return $source
            ? DB::table('tbl_job_posting')->where('id', $source->job_posting_id)->value('posting_title')
            : null;
    }

    private function applicantName(ApplicantApplication $application): string
    {
        $applicant = $application->applicant;
        $name = trim(($applicant->app_fname ?? '') . ' ' . ($applicant->app_lname ?? ''));

        return $name !== '' ? $name : 'The applicant';
    }

    private function empno(): string
    {
        return (string) auth()->user()->Emp_No;
    }
}
