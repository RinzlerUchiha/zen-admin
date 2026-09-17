<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;
use App\Services\Recruitment\ApplicationDecision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HR's decisions on ONE application: withdrawing it on the applicant's behalf,
 * or marking it Not Selected.
 *
 * Authorization is on the routes (can:applicant-applications.decide). Either
 * decision closes only the application named in the URL; the applicant's other
 * applications, profile and documents are unaffected.
 */
class ApplicantApplicationController extends Controller
{
    public function withdraw(Request $request, $application)
    {
        $validated = $this->validated($request);

        $closed = ApplicationDecision::withdraw((int) $application, $this->empno(), $validated['note'] ?? null);

        return back()->with('success', $this->describe($closed) . ' has been withdrawn.');
    }

    public function notSelected(Request $request, $application)
    {
        $validated = $this->validated($request);

        $closed = ApplicationDecision::notSelected((int) $application, $this->empno(), $validated['note'] ?? null);

        return back()->with('success', $this->describe($closed) . ' has been marked Not Selected.');
    }

    /** "Bea Browser's application for MIS Staff Jr Technician", for HR's confirmation. */
    private function describe(ApplicantApplication $application): string
    {
        $applicant = $application->applicant;
        $name = trim(($applicant->app_fname ?? '') . ' ' . ($applicant->app_lname ?? ''));
        $title = DB::table('tbl_job_posting')->where('id', $application->job_posting_id)->value('posting_title');

        return ($name !== '' ? $name . "'s application" : 'The application')
            . ($title ? ' for ' . $title : ' #' . $application->id);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);
    }

    private function empno(): string
    {
        return (string) auth()->user()->Emp_No;
    }
}
