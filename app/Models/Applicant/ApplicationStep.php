<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One step in ONE application's workflow.
 *
 * These rows are the application's sequence. There is no shared template
 * underneath them, so HR adding a Technical round to this candidate cannot
 * disturb anybody else's pipeline.
 *
 * Workflow position only. Whether the person is Hired, Withdrawn or Not
 * Selected lives on the application's status and is a different question.
 */
class ApplicationStep extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_application_steps';

    protected $fillable = [
        'application_id', 'stage_key', 'label', 'kind', 'position', 'required',
        'state', 'satisfied_by', 'result', 'notes', 'decided_at', 'decided_by',
        'outcome', 'outcome_by', 'outcome_at',
    ];

    protected $casts = [
        'required' => 'boolean',
        'decided_at' => 'datetime',
        'outcome_at' => 'datetime',
    ];

    public const STATE_PENDING = 'pending';
    public const STATE_COMPLETED = 'completed';

    /*
    | The round's official outcome, which is a different question from its
    | state. State says HR has finished with the round; outcome says what they
    | concluded. NULL means no decision has been recorded -- never "passed".
    */
    public const OUTCOME_PASSED = 'passed';
    public const OUTCOME_FAILED = 'failed';

    public const OUTCOMES = [self::OUTCOME_PASSED, self::OUTCOME_FAILED];

    /** Completed, with HR's decision still outstanding. */
    public function scopeAwaitingOutcome($query)
    {
        return $query->where('state', self::STATE_COMPLETED)->whereNull('outcome');
    }

    public function passed(): bool
    {
        return $this->outcome === self::OUTCOME_PASSED;
    }

    /**
     * Never required on THIS application's path.
     *
     * Distinct from pending on purpose: a job-fair hire's skipped rounds did
     * not happen and are not waiting to. Showing them as pending would put
     * them in queues nobody should be working.
     */
    public const STATE_NOT_APPLICABLE = 'not_applicable';

    /* Ordering gaps, so a step can be inserted between two others without
       renumbering the rest. */
    public const POSITION_GAP = 10;

    public function scopeForApplication($query, int $applicationId)
    {
        return $query->where('application_id', $applicationId)->orderBy('position');
    }

    /** Still to happen. What a queue should be built from. */
    public function scopeOutstanding($query)
    {
        return $query->where('state', self::STATE_PENDING);
    }

    /**
     * Editable by HR.
     *
     * A completed step is history and is left alone; a pending or
     * not-applicable one can still be relabelled, reordered or removed.
     */
    public function isEditable(): bool
    {
        return $this->state !== self::STATE_COMPLETED;
    }

    public function isBlocking(): bool
    {
        return $this->required && $this->state === self::STATE_PENDING;
    }
}
