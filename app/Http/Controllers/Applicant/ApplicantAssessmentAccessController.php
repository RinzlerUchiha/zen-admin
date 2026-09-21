<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\AssessmentAccessCodes;
use Illuminate\Support\Facades\DB;

/**
 * Issuing an applicant's assessment access code.
 *
 * Authorization is on the route (can:applicant-assessments.issue-access). The
 * code is flashed once for HR to read out or hand over; it is never stored or
 * shown again in plain text.
 */
class ApplicantAssessmentAccessController extends Controller
{
    public function issue($id)
    {
        $appId = (int) $id;
        abort_unless(DB::connection('applicant')->table('tblapp_persinfo')->where('app_id', $appId)->exists(), 404);

        $issued = AssessmentAccessCodes::issue($appId, (string) auth()->user()->Emp_No);

        return redirect()
            ->route('applicant.show', ['id' => $appId, 'tab' => 'assessment-access'])
            ->with('assessment_code', $issued['code'])
            ->with('assessment_code_expires', $issued['expires_at']->format('g:i A'));
    }
}
