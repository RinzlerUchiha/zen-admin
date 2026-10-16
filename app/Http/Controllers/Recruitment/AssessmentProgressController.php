<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\AssessmentDecision;
use App\Services\Recruitment\AssessmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Assessment review: where the assessments stand, and HR's decision.
 *
 * The engine itself is untouched. This reads it and adds the two questions it
 * does not answer — is anything outstanding, and what did HR conclude.
 *
 * There is no pass mark anywhere in this controller, because there is none in
 * the system. Where the job specification defines an expected profile, the
 * result is shown beside it; where it does not, the comparison is reported as
 * unspecified rather than treated as a pass.
 */
class AssessmentProgressController extends Controller
{
    public function index($application)
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'assessment-review',
            'maincat' => 'assessment-review',
            'page' => 'pages.recruitment.assessment-review.index-content',
            'applicationId' => (int) $application,
        ]);
    }

    public function data($application)
    {
        return response()->json(AssessmentProgress::forApplication((int) $application) + [
            'disclaimer' => 'There is no pass mark for any of these assessments. Where the job '
                . 'specification defines an expected profile the result is shown beside it; '
                . 'where it does not, the comparison is unavailable. Completing them all '
                . 'advances nobody on its own.',
        ]);
    }

    /** Record proceed, hold or stop. A reason is always required. */
    public function decide(Request $request, $application)
    {
        $validated = $request->validate([
            'decision' => 'required|string|in:' . implode(',', AssessmentDecision::DECISIONS),
            'reason' => 'required|string|max:500',
        ]);

        $record = AssessmentProgress::decide(
            (int) $application,
            $validated['decision'],
            $validated['reason'],
            Auth::user()->Emp_No ?? null
        );

        return response()->json([
            'success' => true,
            'decision' => $record->decision,
            'message' => match ($record->decision) {
                AssessmentDecision::PROCEED => 'Recorded. The application is unchanged — '
                    . 'advance the stage when you are ready.',
                AssessmentDecision::HOLD => 'Held. The application stays open and nothing moves '
                    . 'until you decide.',
                default => 'Recorded as not proceeding. The application is closed as Not Selected.',
            },
        ]);
    }
}
