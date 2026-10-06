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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
