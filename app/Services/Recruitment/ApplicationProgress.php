<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;

/**
 * How far one application has got, in a shape a header can draw.
 *
 * Read-only, and emphatically so. Nothing here advances an application,
 * completes a step or changes a status: it answers "where is this" for
 * somebody looking at a screen. A progress indicator that could move the
 * thing it describes would be a second, accidental workflow.
 *
 * WHERE THE STEPS COME FROM
 *
 * The application's own rows in tblapp_application_steps, in their own order.
 * Not a hard-coded list of stages: an application with a Technical round
 * added for one candidate has four steps and must show four, and one whose
 * Examinations were marked not applicable must not show them as outstanding.
 * The catalogue is irrelevant here — what matters is what THIS application
 * was given.
 *
 * STATUS AND STAGE ARE DIFFERENT QUESTIONS
 *
 * `status` is what was decided; the steps are where it got to. An application
 * can be Withdrawn halfway through its rounds, and both facts are true and
 * both are worth showing. So the disposition is reported alongside the steps
 * rather than replacing them, and a closed application keeps the shape of the
 * journey it had.
 *
 * HIRED IS NOT A ROUND
 *
 * Hired is a status, not a stage, and config/applications.php deliberately
 * leaves it open. It is never rendered as another pip on the end of the
 * sequence: doing so would invent a stage the workflow does not have and
 * would make a hired candidate look like they were waiting for something.
 */
class ApplicationProgress
{
    /**
     * @return array{
     *     steps: array<int, array{key:string,label:string,state:string,result:?string,current:bool}>,
     *     total:int, completed:int, current:?string, percent:int,
     *     closed:bool, disposition:?string, hired:bool
     * }
     */
    public static function forApplication(int $applicationId): array
    {
        $application = ApplicantApplication::find($applicationId);
        $steps = ApplicationStep::forApplication($applicationId)->get();

        $status = (string) ($application->status ?? '');
        $definition = ApplicantApplication::statusDefinition($status) ?: [];
        $closed = (bool) ($definition['closed'] ?? false);

        /*
        | The step the application is waiting on.
        |
        | The first required step still pending — the same question
        | ApplicationWorkflow::nextStep() answers, asked here against the rows
        | already loaded rather than with a second query. Optional steps are
        | skipped because they hold nobody up, and a closed application is
        | waiting on nothing at all.
        */
        $current = null;

        if (!$closed) {
            $current = $steps
                ->first(fn ($s) => $s->state === ApplicationStep::STATE_PENDING && $s->required)
                ?->stage_key;
        }

        $settled = $steps->filter(fn ($s) => $s->state !== ApplicationStep::STATE_PENDING);

        return [
            'steps' => $steps->map(fn ($s) => [
                'key' => $s->stage_key,
                /* The label this step was given when the application got it,
                   so a later rename of the catalogue entry never rewrites
                   what somebody actually went through. */
                'label' => $s->label,
                'kind' => $s->kind,
                'state' => $s->state,
                'result' => $s->result,
                'current' => $s->stage_key === $current,
            ])->values()->all(),

            'total' => $steps->count(),
            'completed' => $settled->count(),
            'current' => $current,

            /* For a bar. Rounded down, and never 100 unless everything really
               is settled -- a bar that reads full while a round is open is
               worse than no bar. */
            'percent' => $steps->count() > 0
                ? (int) floor($settled->count() / $steps->count() * 100)
                : 0,

            'closed' => $closed,

            /*
            | How it ended, when it ended.
            |
            | Null while it is open. Reported separately from the steps
            | because an application that stopped at the second round stopped
            | THERE, and showing only "Withdrawn" would lose where it got to.
            */
            'disposition' => $closed ? $status : null,

            /*
            | Hired is carried as its own fact, never as a step.
            |
            | It is deliberately an open status, so it would otherwise look
            | like an application still waiting on its rounds.
            */
            'hired' => $status === 'Hired',
        ];
    }

    /** The same, for several applications, without a query each. */
    public static function forApplications(iterable $applicationIds): array
    {
        $out = [];

        foreach ($applicationIds as $id) {
            $out[(int) $id] = self::forApplication((int) $id);
        }

        return $out;
    }
}
