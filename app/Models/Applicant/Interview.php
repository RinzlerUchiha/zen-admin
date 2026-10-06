<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One interview occurrence, belonging to one workflow step.
 *
 * There is no applicant key here and there must never be one. An interview
 * reaches its application through its step, which means a query cannot
 * accidentally return another application's interviews — the failing that makes
 * the legacy screen show every application's rounds under every application.
 *
 * Several of these may sit under one step. A cancelled or missed interview is
 * kept rather than erased, and rescheduling creates a new record pointing back
 * at the one it replaces.
 */
class Interview extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_interviews';

    protected $fillable = [
        'step_id', 'scheduled_at', 'duration_minutes', 'venue', 'meeting_link',
        'attendance', 'held_at', 'cancelled_at', 'cancelled_by', 'cancel_reason',
        'rescheduled_from_id', 'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'held_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /** Booked, still to happen. */
    public const SCHEDULED = 'scheduled';

    /** It took place. The evaluations hang off this one. */
    public const HELD = 'held';

    /** Called off. Kept, because it happened to the candidate's week. */
    public const CANCELLED = 'cancelled';

    /** The candidate did not attend, which is itself a fact worth keeping. */
    public const NO_SHOW = 'no_show';

    /**
     * Attendance values that can never change again.
     *
     * Once an interview has happened — or demonstrably has not — that is
     * settled. Putting it back to 'scheduled' would be rewriting the past;
     * the way forward is a new interview that supersedes this one.
     */
    public const TERMINAL = [self::HELD, self::CANCELLED, self::NO_SHOW];

    public function step()
    {
        return $this->belongsTo(ApplicationStep::class, 'step_id');
    }

    public function interviewers()
    {
        return $this->hasMany(InterviewAssignment::class, 'interview_id');
    }

    public function evaluations()
    {
        return $this->hasMany(InterviewEvaluation::class, 'interview_id');
    }

    /** The attempt this one replaced, if it replaced one. */
    public function rescheduledFrom()
    {
        return $this->belongsTo(self::class, 'rescheduled_from_id');
    }

    /** Every interview for one application, across all of its steps. */
    public function scopeForApplication($query, int $applicationId)
    {
        return $query->whereIn('step_id', function ($sub) use ($applicationId) {
            $sub->select('id')->from('tblapp_application_steps')
                ->where('application_id', $applicationId);
        });
    }

    public function scopeForStep($query, int $stepId)
    {
        return $query->where('step_id', $stepId)->orderBy('scheduled_at');
    }

    /** The one still awaiting its date, if any. At most one per step. */
    public function scopeLive($query)
    {
        return $query->where('attendance', self::SCHEDULED);
    }

    public function isTerminal(): bool
    {
        return in_array($this->attendance, self::TERMINAL, true);
    }

    /** Only a held interview can carry evaluations. */
    public function acceptsEvaluations(): bool
    {
        return $this->attendance === self::HELD;
    }
}
