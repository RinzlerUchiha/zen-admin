<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Model;

/**
 * One place a job posting was advertised, and what came back from it.
 *
 * HR posts to LinkedIn, JobStreet and the rest by hand — HireFlow has no
 * API to any of them — so `state` is HR's own account of whether they did.
 * `visits` and `applications` are the only numbers HireFlow can vouch for,
 * and they describe our Careers page, never the platform's own reach.
 *
 * @see config/posting_channels.php
 * @see \App\Services\Recruitment\PostingChannels
 */
class JobPostingChannel extends Model
{
    protected $table = 'tbl_job_posting_channel';

    protected $guarded = ['id'];

    protected $casts = [
        'posted_at' => 'datetime',
        'visits' => 'integer',
        'applications' => 'integer',
    ];

    public const POSTED = 'Posted';
    public const NOT_POSTED = 'Not Posted';

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    /** HR's name for this channel, falling back to the key if it is not catalogued. */
    public function label(): string
    {
        return config('posting_channels.channels.' . $this->channel_key . '.name')
            ?? ucwords(str_replace('_', ' ', $this->channel_key));
    }

    /**
     * Somewhere HR posts by hand, so it gets a tagged link and a Posted tick.
     *
     * `direct` and `walk_in` are not: one is traffic that arrives on its own,
     * the other is recorded by HR at intake.
     */
    public function isExternal(): bool
    {
        return (bool) config('posting_channels.channels.' . $this->channel_key . '.external', true);
    }

    /**
     * Visits that went on to apply, as a percentage.
     *
     * Null when nothing has arrived yet — a rate of 0% would read as a
     * failure when the truth is that nobody has visited.
     */
    public function conversionRate(): ?float
    {
        return $this->visits > 0
            ? round($this->applications / $this->visits * 100, 1)
            : null;
    }
}
