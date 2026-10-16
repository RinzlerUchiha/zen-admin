<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
| The interviewer bridge for Zen-Hub.
|
| Zen-Hub has its own login and no access to the applicant database, so these
| endpoints are the only way an interviewer reaches their work — and a thin way:
| every rule about notes and evaluations lives in the services behind them,
| shared with HR's own screens.
|
| The signature establishes WHO is asking. It never establishes what they may
| see: each endpoint resolves that from the interviews actually assigned to that
| employee, so an edited id reaches nothing.
*/
Route::prefix('hireflow/interviewer')
    /* The shared per-IP limiter is replaced, not added to: every interviewer
       arrives from the same Zen-Hub server, so counting by IP would have them
       throttling one another. */
    ->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api')
    ->middleware(['throttle:hireflow', 'hireflow.signed'])
    ->name('api.hireflow.interviewer.')
    ->group(function () {
        $c = \App\Http\Controllers\Api\InterviewerApiController::class;

        Route::get('/me', [$c, 'me'])->name('me');
        Route::get('/interviews', [$c, 'index'])->name('index');
        Route::get('/interviews/{interview}', [$c, 'show'])->whereNumber('interview')->name('show');

        Route::get('/interviews/{interview}/notes', [$c, 'notes'])->whereNumber('interview')->name('notes');
        Route::post('/interviews/{interview}/notes', [$c, 'addNote'])->whereNumber('interview')->name('notes.add');
        Route::patch('/notes/{note}', [$c, 'editNote'])->whereNumber('note')->name('notes.edit');

        Route::get('/interviews/{interview}/evaluation', [$c, 'evaluation'])->whereNumber('interview')->name('evaluation');
        Route::post('/interviews/{interview}/evaluation', [$c, 'saveEvaluation'])->whereNumber('interview')->name('evaluation.save');
        Route::post('/evaluations/{evaluation}/correct', [$c, 'correctEvaluation'])->whereNumber('evaluation')->name('evaluation.correct');
    });

/*
| The offer-approval bridge for Zen-Hub.
|
| The HR Head approves offers from Zen-Hub HireFlow, so these endpoints are the
| only way they reach one -- and a thin way: every rule about what may happen to
| an offer lives in OfferWorkflow, shared with HR's own screens.
|
| A SEPARATE ROOT, A SEPARATE SECRET, A SEPARATE AUDIENCE
|
| Not folded into the interviewer group above. That group's final gate is
| "do you have an interview assigned", which an HR Head would fail; and its
| canonical string covers only the path relative to its own root, so sharing
| the format and the secret would let a signature for one root verify under the
| other. This root signs with an audience line and its own secret, either of
| which alone would prevent that.
|
| The signature establishes WHO is asking. It never establishes what they may
| do: the middleware checks the role, and each endpoint then resolves the offer
| from those actually awaiting approval, so an edited id reaches nothing.
*/
Route::prefix('hireflow/approver')
    /* Its own limiter, for the same reason the interviewer group has one: every
       approver arrives from the same Zen-Hub server, so counting by IP would
       have them throttling one another -- and sharing the interviewer's bucket
       would have the two throttling each other. */
    ->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class . ':api')
    ->middleware(['throttle:hireflow-approver', 'hireflow.signed.approver'])
    ->name('api.hireflow.approver.')
    ->group(function () {
        $c = \App\Http\Controllers\Api\ApproverApiController::class;

        Route::get('/me', [$c, 'me'])->name('me');
        Route::get('/offers', [$c, 'index'])->name('index');
        Route::get('/offers/{offer}', [$c, 'show'])->whereNumber('offer')->name('show');

        /* The three answers. Decline and revision each require a reason. */
        Route::post('/offers/{offer}/approve', [$c, 'approve'])
            ->whereNumber('offer')->name('approve');
        Route::post('/offers/{offer}/decline', [$c, 'decline'])
            ->whereNumber('offer')->name('decline');
        Route::post('/offers/{offer}/request-revision', [$c, 'requestRevision'])
            ->whereNumber('offer')->name('request-revision');
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
