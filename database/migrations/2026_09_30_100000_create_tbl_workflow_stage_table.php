<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
| The catalogue of workflow stages that CAN exist.
|
| This is not a sequence. It is the vocabulary: Initial, Exams, Final, and the
| intermediate interview rounds HR may add to an individual application. An
| application's actual workflow lives in tblapp_application_steps, which owns
| its own rows — so editing this catalogue never reaches an application that
| already exists.
|
| Deliberately NOT department-specific. Nothing here decides that Finance
| interviews three times and MIS twice; that is a per-application judgement HR
| makes, not a rule the schema should assert.
|
| Lives on the DEFAULT connection beside tbl_job_posting: HireFlow owns it and
| HR maintains it from zen-admin.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_workflow_stage', function (Blueprint $table) {
            $table->id();

            /* Stable identity. Steps reference this, so reporting can group
               across applications even after a label is changed. */
            $table->string('stage_key', 40)->unique();

            /* What HR reads. An application step SNAPSHOTS this at creation,
               so renaming a stage here never rewrites finished history. */
            $table->string('label', 100);

            /*
            | What kind of thing this is. An exam and an interview are the same
            | structure with different handling, which is what lets Exams sit
            | in the workflow without being part of the interview sequence.
            */
            $table->enum('kind', ['interview', 'exam', 'admin']);

            /*
            | Fixed stages cannot be removed from an application. Initial and
            | Final are the two ends of every hiring conversation; they may be
            | marked not_applicable (a job-fair hire may never have a separate
            | Final) but they are not deletable.
            */
            $table->boolean('is_fixed')->default(false);

            /* Included when a new application's workflow is created. */
            $table->boolean('in_baseline')->default(false);

            /*
            | Ordering, in gaps of 10 so an intermediate step can be inserted
            | between two existing ones without renumbering anything.
            */
            $table->unsignedInteger('baseline_order')->default(0);

            /* Optional stages do not block progression. */
            $table->boolean('required_by_default')->default(true);

            /*
            | For a stage of kind 'exam': which assessment satisfies it. A key
            | from config/applicant_assessments.php. The assessment engine is
            | not touched — this only names what counts as done.
            */
            $table->string('satisfied_by', 60)->nullable();

            $table->string('created_by', 20)->nullable();

            /* Retired, never deleted: applications may still reference it. */
            $table->timestamp('retired_at')->nullable();

            $table->timestamps();

            $table->index(['in_baseline', 'baseline_order'], 'tbl_workflow_stage_baseline_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_workflow_stage');
    }
};
