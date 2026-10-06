<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * An interviewer assigned to conduct an interview.
 *
 * Its own record because an interview may be conducted by several people, each
 * of whom may record their own evaluation independently.
 *
 * The name is stored alongside the employee number rather than read live from
 * tngc_hrd2: an interview held years ago must still say who conducted it, even
 * after that person has left and their record has gone.
 */
class InterviewAssignment extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_interview_interviewers';

    protected $fillable = [
        'interview_id', 'empno', 'interviewer_name', 'assigned_by',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class, 'interview_id');
    }

    /** What this employee has been asked to conduct. */
    public function scopeForInterviewer($query, string $empno)
    {
        return $query->where('empno', $empno);
    }

    /**
     * Whether this employee is conducting this interview.
     *
     * The check that gates submitting an evaluation: being HR is not enough,
     * the evaluation has to come from someone who was actually in the room.
     */
    public static function isAssigned(int $interviewId, string $empno): bool
    {
        return static::where('interview_id', $interviewId)
            ->where('empno', $empno)
            ->exists();
    }
}
