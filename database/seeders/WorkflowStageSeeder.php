<?php

namespace Database\Seeders;

use App\Models\Recruitment\WorkflowStage;
use Illuminate\Database\Seeder;

/**
 * The starting vocabulary of workflow stages.
 *
 * Three baseline stages every application begins with, and a handful of
 * intermediate interview rounds HR can add to an individual application when
 * that particular hire needs one.
 *
 * Deliberately NOT department-specific. Nothing here says Finance interviews
 * three times and MIS twice. Whether a candidate needs another round is a
 * judgement made on the application in front of you, and baking it into the
 * catalogue would make the common case invisible and the exception impossible.
 *
 * The intermediate rounds are suggestions, not requirements — none is in the
 * baseline, so none appears until somebody chooses it.
 */
class WorkflowStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            /*
            | The baseline: what every application starts with.
            |
            | Exams sits between the two interviews because that is the order
            | the process runs in — but it is a step of its own kind, not part
            | of the interview sequence, so it can be made optional or marked
            | not applicable without disturbing the interviews around it.
            */
            [
                'stage_key' => 'initial',
                'label' => 'Initial Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => true,
                'in_baseline' => true,
                'baseline_order' => 10,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'exams',
                'label' => 'Examinations',
                'kind' => WorkflowStage::KIND_EXAM,
                'is_fixed' => false,
                'in_baseline' => true,
                'baseline_order' => 20,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'final',
                'label' => 'Final Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => true,
                'in_baseline' => true,
                'baseline_order' => 90,
                'required_by_default' => true,
            ],

            /*
            | Intermediate rounds HR can add to one application.
            |
            | Ordered between Exams and Final only as a hint for where they
            | usually belong; the actual position is chosen per application
            | when the step is added.
            */
            [
                'stage_key' => 'second',
                'label' => '2nd Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => false,
                'in_baseline' => false,
                'baseline_order' => 30,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'prelim',
                'label' => 'Preliminary Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => false,
                'in_baseline' => false,
                'baseline_order' => 40,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'technical',
                'label' => 'Technical Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => false,
                'in_baseline' => false,
                'baseline_order' => 50,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'dept_head',
                'label' => 'Department Head Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => false,
                'in_baseline' => false,
                'baseline_order' => 60,
                'required_by_default' => true,
            ],
            [
                'stage_key' => 'panel',
                'label' => 'Panel Interview',
                'kind' => WorkflowStage::KIND_INTERVIEW,
                'is_fixed' => false,
                'in_baseline' => false,
                'baseline_order' => 70,
                'required_by_default' => true,
            ],
        ];

        foreach ($stages as $stage) {
            // updateOrCreate so re-running never duplicates a stage, and never
            // resurrects one an administrator has retired.
            WorkflowStage::updateOrCreate(
                ['stage_key' => $stage['stage_key']],
                $stage
            );
        }
    }
}
