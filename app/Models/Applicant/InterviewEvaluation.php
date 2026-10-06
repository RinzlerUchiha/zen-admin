<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One interviewer's conclusion about one interview.
 *
 * Independent of every other evaluation of the same interview. Two interviewers
 * may disagree and both records stand as written; nothing here averages or
 * ranks them. HR reads them and decides the workflow step's result separately.
 *
 * A draft is editable by its author. A submitted evaluation is immutable — to
 * change what stands, the author files a correction, which is a new record
 * superseding the old one. The original is never rewritten or removed, because
 * an interviewer revising their conclusion is part of the history, not a
 * mistake to be tidied away.
 */
class InterviewEvaluation extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_interview_evaluations';

    protected $fillable = [
        'interview_id', 'evaluator_empno', 'evaluator_name', 'outcome',
        'recommendation', 'remarks', 'submitted_at', 'revision',
        'supersedes_id', 'superseded_at', 'correction_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'superseded_at' => 'datetime',
        'revision' => 'integer',
    ];

    /* This round's judgement — whether the candidate should go on to the next
       step. Not a hiring decision: that belongs to the Offer phase. */
    public const PROCEED = 'proceed';
    public const DO_NOT_PROCEED = 'do_not_proceed';

    /** Neither yes nor no. Kept as its own answer, because forcing a binary
        choice on an undecided interviewer produces a false record. */
    public const HOLD = 'hold';

    public const OUTCOMES = [self::PROCEED, self::DO_NOT_PROCEED, self::HOLD];

    public function interview()
    {
        return $this->belongsTo(Interview::class, 'interview_id');
    }

    /** The evaluation this one corrects, if it is a correction. */
    public function supersedes()
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    /** The correction that replaced this one, if it has been corrected. */
    public function supersededBy()
    {
        return $this->hasOne(self::class, 'supersedes_id');
    }

    /**
     * The evaluations that currently stand.
     *
     * Read from the flag rather than by sorting revisions: a unique index
     * guarantees one per interviewer per interview, so this cannot return two.
     */
    public function scopeEffective($query)
    {
        return $query->whereNull('superseded_at');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopeForInterview($query, int $interviewId)
    {
        return $query->where('interview_id', $interviewId);
    }

    /** Still editable by its author. */
    public function isDraft(): bool
    {
        return $this->submitted_at === null;
    }

    /** Submitted, so its contents can no longer change. */
    public function isLocked(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isCorrection(): bool
    {
        return $this->supersedes_id !== null;
    }

    /** Superseded by a later correction; kept for the record. */
    public function isSuperseded(): bool
    {
        return $this->superseded_at !== null;
    }
}
