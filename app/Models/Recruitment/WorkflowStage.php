<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * A stage that CAN exist in a hiring workflow.
 *
 * This is the vocabulary, not a sequence. An application's actual workflow is
 * its own rows in tblapp_application_steps, copied from the baseline here and
 * then owned outright — so editing this catalogue never reaches an application
 * that already exists.
 *
 * Nothing here is department-specific. Whether a particular hire needs a second
 * interview round is a judgement HR makes on that application, not a rule this
 * table asserts.
 */
class WorkflowStage extends Model
{
    protected $table = 'tbl_workflow_stage';

    protected $fillable = [
        'stage_key', 'label', 'kind', 'is_fixed', 'in_baseline', 'baseline_order',
        'required_by_default', 'satisfied_by', 'created_by', 'retired_at',
    ];

    protected $casts = [
        'is_fixed' => 'boolean',
        'in_baseline' => 'boolean',
        'required_by_default' => 'boolean',
        'retired_at' => 'datetime',
    ];

    public const KIND_INTERVIEW = 'interview';
    public const KIND_EXAM = 'exam';
    public const KIND_ADMIN = 'admin';

    /* The two ends of every hiring conversation. Removable from no application. */
    public const STAGE_INITIAL = 'initial';
    public const STAGE_FINAL = 'final';

    /** Still offered for new use. Retired stages stay for the history they carry. */
    public function scopeAvailable($query)
    {
        return $query->whereNull('retired_at');
    }

    /** The steps a new application starts with, in order. */
    public function scopeBaseline($query)
    {
        return $query->whereNull('retired_at')
            ->where('in_baseline', true)
            ->orderBy('baseline_order');
    }

    /**
     * Stages HR can add to an application by hand.
     *
     * The intermediate rounds — everything that is not already part of every
     * application. Fixed stages are excluded because they are always present.
     */
    public function scopeAddable($query)
    {
        return $query->whereNull('retired_at')
            ->where('is_fixed', false)
            ->orderBy('baseline_order');
    }

    public function isRetired(): bool
    {
        return $this->retired_at !== null;
    }
}
