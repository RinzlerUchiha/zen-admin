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

    /**
     * HR records that this applicant was actually hired.
     *
     * A separate milestone from the offer being accepted or the contract being
     * signed: for headcount purposes somebody counts as hired once they have
     * been ORIENTED, which nothing in this system observes. So a person records
     * it, and the note is the only evidence of it.
     *
     * Gated on applicant.hire rather than applicant-applications.decide --
     * hiring is the narrower right, held by fewer people than the one that
     * withdraws or declines an application.
     *
     * It creates no employee record. The 201 handoff stays a separate act.
     */
    public function hired(Request $request, $application)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ], [
            'note.required' => 'Say what happened — when they were oriented, or how you know. '
                . 'It is the only record of it.',
        ]);

        $hired = ApplicationDecision::hired((int) $application, $this->empno(), $validated['note']);

        $message = $this->describe($hired) . ' is recorded as Hired. '
            . 'The application stays open until requirements and the 201 handoff are done.';

        /* The offer workspace calls this with fetch; the profile forms post
           and expect a redirect. Both are served rather than forcing one. */
        return $request->expectsJson()
            ? response()->json(['success' => true, 'status' => $hired->status, 'message' => $message])
            : back()->with('success', $message);
    }

    /** Undo a Hired recorded by mistake. */
    public function unmarkHired(Request $request, $application)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Say why this is being taken back.',
        ]);

        $restored = ApplicationDecision::unmarkHired((int) $application, $this->empno(), $validated['reason']);

        $message = $this->describe($restored)
            . ' is no longer recorded as Hired (now ' . $restored->status . ').';

        return $request->expectsJson()
            ? response()->json(['success' => true, 'status' => $restored->status, 'message' => $message])
            : back()->with('success', $message);
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
