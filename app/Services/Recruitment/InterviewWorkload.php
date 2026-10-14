<?php

namespace App\Services\Recruitment;

use App\Models\Applicant\ApplicantApplication;
use App\Models\Applicant\ApplicationStep;
use App\Models\Applicant\Interview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What every open application's interviews still need from somebody.
 *
 * The interview engine is entirely application-scoped: every service method
 * takes an $applicationId, and tblapp_interviews reaches an application only
 * through step_id. So the cross-application questions -- what is due, what
 * has no date, who owes an evaluation, which rounds are waiting on HR -- all
 * come back through the same join, and they are derived HERE rather than in
 * the two places that ask them.
 *
 * Both the Interviews workspace and the Work Queue read this. Deriving it
 * twice would mean two definitions of "needs scheduling" drifting apart,
 * which is the duplicate business rule neither page may become.
 *
 * It READS. Nothing in this service writes, and nothing it returns changes an
 * application's status or stage.
 */
class InterviewWorkload
{
    /** Scheduled this many days ahead still counts as "coming up". */
    private const UPCOMING_DAYS = 7;

    /**
     * Everything outstanding across every open application's interviews.
     *
     * One pass: the same rows answer all four questions, and splitting them
     * would mean four copies of the same join and four chances for them to
     * disagree.
     *
     * @return array{due: array, upcoming: array, needs_scheduling: array,
     *               evaluations_outstanding: array, interviewer_load: array}
     */
    public static function collect(): array
    {
        $open = array_values(array_diff(
            array_keys(config('applications.statuses', [])),
            ApplicantApplication::closedStatuses()
        ));

        /*
        | Interviews belong to steps, steps belong to applications.
        |
        | tblapp_interviews carries no application_id -- it reaches one only
        | through step_id -- so every cross-application question has to come
        | back through tblapp_application_steps. That join is the whole reason
        | this controller exists rather than a scope on the model.
        */
        $steps = DB::connection('applicant')->table('tblapp_application_steps as s')
            ->join('tblapp_applications as a', 'a.id', '=', 's.application_id')
            ->leftJoin('tblapp_persinfo as p', 'p.app_id', '=', 'a.app_id')
            ->where('s.kind', 'interview')
            ->whereIn('a.status', $open)
            ->orderBy('s.application_id')
            ->orderBy('s.position')
            ->get([
                's.id as step_id', 's.application_id', 's.stage_key', 's.label', 's.position', 's.state',
                'a.app_id', 'a.status', 'a.job_posting_id', 'a.request_position_id',
                'p.app_fname', 'p.app_mname', 'p.app_lname',
            ]);

        if ($steps->isEmpty()) {
            return self::empty();
        }

        $interviews = DB::connection('applicant')->table('tblapp_interviews')
            ->whereIn('step_id', $steps->pluck('step_id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('step_id');

        $all = $interviews->flatten(1);
        $interviewIds = $all->pluck('id');

        $assignments = DB::connection('applicant')->table('tblapp_interview_interviewers')
            ->whereIn('interview_id', $interviewIds)
            ->get()
            ->groupBy('interview_id');

        /* Live evaluations only: a corrected one supersedes its predecessor,
           and the superseded row must not count as still owed OR as done. */
        $evaluations = DB::connection('applicant')->table('tblapp_interview_evaluations')
            ->whereIn('interview_id', $interviewIds)
            ->whereNull('superseded_at')
            ->get()
            ->groupBy('interview_id');

        $titles = self::postingTitles($steps);
        $labels = self::displayLabels($steps);

        $now = Carbon::now();
        $horizon = $now->copy()->addDays(self::UPCOMING_DAYS);

        $due = [];
        $upcoming = [];
        $needsScheduling = [];
        $outstanding = [];
        $load = [];

        foreach ($steps as $step) {
            $rounds = collect($interviews->get($step->step_id, []));
            $person = self::personFor($step, $titles);

            foreach ($rounds as $round) {
                $assigned = collect($assignments->get($round->id, []));
                $done = collect($evaluations->get($round->id, []))->pluck('evaluator_empno')->all();

                /* Interviewer load counts people who are ON THE HOOK: a
                   cancelled round is nobody's workload. */
                if ($round->attendance === Interview::SCHEDULED || $round->attendance === Interview::HELD) {
                    foreach ($assigned as $a) {
                        $key = $a->empno;
                        $load[$key] = $load[$key] ?? [
                            'empno' => $a->empno,
                            'name' => $a->interviewer_name ?: $a->empno,
                            'scheduled' => 0,
                            'held' => 0,
                            'evaluations_owed' => 0,
                        ];

                        $load[$key][$round->attendance === Interview::SCHEDULED ? 'scheduled' : 'held']++;

                        if ($round->attendance === Interview::HELD && !in_array($a->empno, $done, true)) {
                            $load[$key]['evaluations_owed']++;
                        }
                    }
                }

                if ($round->attendance === Interview::SCHEDULED && $round->scheduled_at) {
                    $when = Carbon::parse($round->scheduled_at);

                    $row = $person + [
                        'interview_id' => $round->id,
                        'round' => $labels[$step->step_id],
                        'scheduled_at' => $round->scheduled_at,
                        'venue' => $round->venue,
                        'has_link' => (bool) $round->meeting_link,
                        'interviewers' => $assigned->map(fn ($a) => $a->interviewer_name ?: $a->empno)->values()->all(),
                        'overdue' => $when->isBefore($now),
                    ];

                    /* Past its time and still only "scheduled": somebody has
                       to say whether it happened. That is the most urgent
                       thing on this page, so it leads. */
                    if ($when->isBefore($now) || $when->isBefore($horizon)) {
                        $due[] = $row;
                    } else {
                        $upcoming[] = $row;
                    }
                }

                /* Held, assigned, and somebody has not written it up. */
                if ($round->attendance === Interview::HELD) {
                    $owed = $assigned->reject(fn ($a) => in_array($a->empno, $done, true));

                    if ($owed->isNotEmpty()) {
                        $outstanding[] = $person + [
                            'interview_id' => $round->id,
                            'round' => $labels[$step->step_id],
                            'held_at' => $round->held_at,
                            'awaiting' => $owed->map(fn ($a) => $a->interviewer_name ?: $a->empno)->values()->all(),
                            'received' => count($done),
                            'assigned' => $assigned->count(),
                            'kind' => 'evaluation',
                        ];
                    } elseif ($step->state === ApplicationStep::STATE_PENDING) {
                        /* Everyone has written up and the round is still open:
                           what is outstanding now is HR's decision on it. */
                        $outstanding[] = $person + [
                            'interview_id' => $round->id,
                            'round' => $labels[$step->step_id],
                            'held_at' => $round->held_at,
                            'awaiting' => [],
                            'received' => count($done),
                            'assigned' => $assigned->count(),
                            'kind' => 'decision',
                        ];
                    }
                }
            }

            /*
            | Nothing live on a round that is still open.
            |
            | A step counts as needing a date when it is pending and has no
            | interview that is scheduled or already held -- so a round whose
            | only attempt was cancelled or a no-show comes back here, which
            | is right: it still has to happen.
            */
            if ($step->state === ApplicationStep::STATE_PENDING) {
                $live = $rounds->contains(
                    fn ($r) => in_array($r->attendance, [Interview::SCHEDULED, Interview::HELD], true)
                );

                if (!$live) {
                    $needsScheduling[] = $person + [
                        'step_id' => $step->step_id,
                        'round' => $labels[$step->step_id],
                        'attempts' => $rounds->count(),
                    ];
                }
            }
        }

        $due = collect($due)->sortBy('scheduled_at')->values();
        $upcoming = collect($upcoming)->sortBy('scheduled_at')->values();
        $outstanding = collect($outstanding)->sortBy('held_at')->values();
        $load = collect($load)->sortByDesc(fn ($l) => $l['evaluations_owed'] * 100 + $l['scheduled'])->values();

        return [
            'due' => $due,
            'upcoming' => $upcoming,
            'needs_scheduling' => $needsScheduling,
            'evaluations_outstanding' => $outstanding,
            'interviewer_load' => $load,
            'counts' => [
                'due' => $due->count(),
                'overdue' => $due->where('overdue', true)->count(),
                'upcoming' => $upcoming->count(),
                'needs_scheduling' => count($needsScheduling),
                'evaluations_outstanding' => $outstanding->count(),
                'interviewer_load' => $load->count(),
            ],
        ];
    }

    private static function empty(): array
    {
        return [
            'due' => [], 'upcoming' => [], 'needs_scheduling' => [],
            'evaluations_outstanding' => [], 'interviewer_load' => [],
            'counts' => [
                'due' => 0, 'overdue' => 0, 'upcoming' => 0,
                'needs_scheduling' => 0, 'evaluations_outstanding' => 0, 'interviewer_load' => 0,
            ],
        ];
    }

    /** The person and the posting, shared by every row this step produces. */
    private static function personFor(object $step, array $titles): array
    {
        return [
            'application_id' => $step->application_id,
            'app_id' => $step->app_id,
            'applicant_name' => trim(implode(' ', array_filter([
                $step->app_fname, $step->app_mname, $step->app_lname,
            ]))) ?: 'Unnamed applicant',
            'posting_title' => $titles['postings'][$step->job_posting_id] ?? null,
            'mr_no' => $titles['mrNos'][$step->request_position_id] ?? null,
        ];
    }

    /**
     * What each round is CALLED, by the engine's own rule.
     *
     * InterviewWorkflow::summary() works this out per application, which over
     * a list would be three queries a row. The rule itself is one public
     * helper -- ordinalLabel() -- so it is applied here to steps already
     * fetched: Initial and Final keep their names, everything between is
     * numbered by where it sits. Reusing the helper rather than re-deriving
     * the names is what keeps this page and the per-application page from
     * calling the same round two different things.
     *
     * @return array<int, string> keyed by step id
     */
    private static function displayLabels($steps): array
    {
        $labels = [];

        foreach ($steps->groupBy('application_id') as $forApplication) {
            $intermediate = 1;

            foreach ($forApplication->sortBy('position') as $step) {
                if ($step->stage_key === 'initial' || $step->stage_key === 'final') {
                    $labels[$step->step_id] = $step->label;
                    continue;
                }

                $intermediate++;
                $labels[$step->step_id] = InterviewWorkflow::ordinalLabel($intermediate);
            }
        }

        return $labels;
    }

    /** Posting titles and MR numbers for the whole list, in two queries. */
    private static function postingTitles($steps): array
    {
        $postingIds = $steps->pluck('job_posting_id')->filter()->unique()->values();
        $positionIds = $steps->pluck('request_position_id')->filter()->unique()->values();

        /* Same lookup the screening queue uses, in the same shape: the join
           needs explicit aliases before pluck, or the MR number comes back
           keyed on an ambiguous column. */
        return [
            'postings' => $postingIds->isEmpty()
                ? []
                : DB::connection('mysql')->table('tbl_job_posting')
                    ->whereIn('id', $postingIds)
                    ->pluck('posting_title', 'id')->all(),

            'mrNos' => $positionIds->isEmpty()
                ? []
                : DB::connection('mysql')->table('tbl_manpower_request_position as pos')
                    ->leftJoin('tbl_manpower_request as r', 'r.id', '=', 'pos.request_id')
                    ->whereIn('pos.id', $positionIds)
                    ->select('pos.id as position_id', 'r.mr_no')
                    ->pluck('mr_no', 'position_id')->all(),
        ];
    }
}
