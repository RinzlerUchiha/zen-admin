<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Services\Recruitment\ApplicantBlacklist;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Blacklisting an applicant, and lifting it.
 *
 * Authorization is on the routes (can:applicant-applications.decide) — the same
 * right as HR's other decisions about an applicant.
 *
 * Both actions take a reason and both keep it. The service refuses an empty
 * one: a bar nobody can account for cannot be reviewed or safely lifted.
 */
class ApplicantBlacklistController extends Controller
{
    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Give a reason for blacklisting this applicant. It is kept on the record.',
        ]);

        try {
            ApplicantBlacklist::add((int) $id, $validated['reason'], (string) auth()->user()->Emp_No);
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        return back()->with('status', 'This applicant has been blacklisted. '
            . 'Their existing applications are unchanged.');
    }

    public function lift(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Give a reason for lifting the blacklist. It is kept on the record.',
        ]);

        try {
            ApplicantBlacklist::lift((int) $id, $validated['reason'], (string) auth()->user()->Emp_No);
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        return back()->with('status', 'The blacklist has been lifted. This applicant can apply again.');
    }
}
