<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\AssessmentAccessCodes;
use App\Services\Recruitment\AssessmentEligibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Issuing an applicant's assessment access code.
 *
 * Authorization is on the route (can:applicant-assessments.issue-access). The
 * code is flashed once for HR to read out or hand over; it is never stored or
 * shown again in plain text.
 *
 * Two different questions are answered here, and both must be satisfied:
 * whether the applicant is ELIGIBLE to be assessed at all (the Initial
 * Interview must be behind them), and — later, in zen-applicants — whether
 * the person entering the code is AUTHORISED. The code is unchanged.
 */
class ApplicantAssessmentAccessController extends Controller
{
    public function issue(Request $request, $id)
    {
        $appId = (int) $id;
        abort_unless(DB::connection('applicant')->table('tblapp_persinfo')->where('app_id', $appId)->exists(), 404);

        /*
        | HR may issue a code to an applicant whose Initial Interview is not
        | recorded complete, but only deliberately and with a reason. The
        | service decides; this only carries HR's answer to it.
        */
        $validated = $request->validate([
            'override_reason' => ['nullable', 'string', 'max:300'],
        ]);

        try {
            $issued = AssessmentAccessCodes::issue(
                $appId,
                (string) auth()->user()->Emp_No,
                $validated['override_reason'] ?? null
            );
        } catch (ValidationException $e) {
            /*
            | Not an error in what HR typed — the applicant simply is not there
            | yet. Send back what is holding it up, and whether an override is
            | available, so the page can offer that rather than just refuse.
            */
            return redirect()
                ->route('applicant.show', ['id' => $appId, 'tab' => 'assessment-access'])
                ->withErrors($e->validator)
                ->with('assessment_prerequisite', AssessmentEligibility::status($appId));
        }

        return redirect()
            ->route('applicant.show', ['id' => $appId, 'tab' => 'assessment-access'])
            ->with('assessment_code', $issued['code'])
            ->with('assessment_code_expires', $issued['expires_at']->format('g:i A'))
            ->with('assessment_code_overridden', $issued['overridden']);
    }
}
