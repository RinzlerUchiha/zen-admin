<?php

namespace App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;

/**
 * One observation about one interview.
 *
 * Not an evaluation. An evaluation concludes whether the candidate should go
 * on, is filed once and locks; a note records what happened — that the
 * candidate rang ahead, that the interview ran long, that they mentioned a
 * notice period — and can be added by anyone involved, at any point, without
 * ceremony.
 *
 * Each note belongs to its author. There is no shared box to overwrite.
 */
class InterviewNote extends Model
{
    protected $connection = 'applicant';
    protected $table = 'tblapp_interview_notes';

    protected $fillable = [
        'interview_id', 'author_empno', 'author_name', 'body', 'edited_at',
    ];

    protected $casts = ['edited_at' => 'datetime'];

    public function interview()
    {
        return $this->belongsTo(Interview::class, 'interview_id');
    }

    /** The thread for one interview, in the order it was written. */
    public function scopeForInterview($query, int $interviewId)
    {
        return $query->where('interview_id', $interviewId)->orderBy('created_at');
    }

    /** Revised after it was written — worth saying so to a reader. */
    public function wasEdited(): bool
    {
        return $this->edited_at !== null;
    }
}
