<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\JobPosting;
use App\Models\Recruitment\JobPostingChannel;
use Illuminate\Support\Collection;

/**
 * Where a posting was advertised, and what came back.
 *
 * HireFlow cannot publish to LinkedIn, JobStreet or PhilJobNet: that needs
 * each platform's API and we have none. What it does instead:
 *
 *   1. HR ticks the places they are going to post the job.
 *   2. HireFlow gives them one tagged Careers link per place.
 *   3. HR posts that link by hand.
 *   4. Anyone arriving through it is counted against that place, and so is
 *      their application if they make one.
 *
 * Everything counted here is HireFlow's side of the trip. A platform's own
 * impressions and views are not available to us and are never reported as
 * though they were — see config/posting_channels.php.
 */
class PostingChannels
{
    /** The catalogue, keyed by channel key. */
    public function catalogue(): array
    {
        return (array) config('posting_channels.channels', []);
    }

    /** The channel an application is credited to when it arrived untagged. */
    public function defaultKey(): string
    {
        return (string) config('posting_channels.default', 'direct');
    }

    /** The query parameter carrying the channel on a Careers link. */
    public function parameter(): string
    {
        return (string) config('posting_channels.parameter', 'src');
    }

    /** HR's name for a key, falling back to the key when it is not catalogued. */
    public function label(string $key): string
    {
        return $this->catalogue()[$key]['name'] ?? ucwords(str_replace('_', ' ', $key));
    }

    /** Somewhere HR posts by hand — so it gets a link and a Posted tick. */
    public function isExternal(string $key): bool
    {
        return (bool) ($this->catalogue()[$key]['external'] ?? true);
    }

    /**
     * The channels this posting can be advertised through.
     *
     * The catalogue plus anything already recorded against this posting that
     * the catalogue no longer lists, so removing a channel from config never
     * hides numbers already collected under it.
     *
     * @return array<string, array> keyed by channel key
     */
    public function availableFor(JobPosting $posting): array
    {
        $available = $this->catalogue();

        foreach ($this->rowsFor($posting) as $row) {
            if (!isset($available[$row->channel_key])) {
                $available[$row->channel_key] = [
                    'name' => $row->label(),
                    'external' => true,
                    'api' => false,
                    'description' => 'Recorded before this channel was catalogued.',
                ];
            }
        }

        return $available;
    }

    /**
     * This posting's channel rows, `direct` first and the rest by name.
     *
     * `direct` is created on demand: every posting receives untagged traffic
     * whether or not anyone chose to track it, so the bucket has to exist
     * before the first visit lands rather than being conjured by it.
     */
    public function rowsFor(JobPosting $posting): Collection
    {
        $rows = JobPostingChannel::where('job_posting_id', $posting->id)->get();

        $default = $this->defaultKey();

        if (!$rows->contains('channel_key', $default)) {
            $rows->push(JobPostingChannel::create([
                'job_posting_id' => $posting->id,
                'channel_key' => $default,
                'state' => JobPostingChannel::NOT_POSTED,
                'visits' => 0,
                'applications' => 0,
            ]));
        }

        return $rows->sortBy([
            fn ($row) => $row->channel_key === $default ? 0 : 1,
            fn ($row) => $row->label(),
        ])->values();
    }

    /**
     * The tagged Careers link HR copies for one channel.
     *
     * Null when the Careers address is not configured (CAREERS_URL), so the
     * panel can say so rather than hand out a link that goes nowhere. Also
     * null for channels nobody posts a link to.
     *
     * The default channel gets the plain URL: a link tagged `?src=direct`
     * would make traffic that was tracked all along look like it was not.
     */
    public function linkFor(JobPosting $posting, string $key): ?string
    {
        $base = (string) config('posting_channels.careers_url', '');

        if ($base === '' || !$this->isExternal($key)) {
            return null;
        }

        $url = $base . '/careers/' . $posting->id;

        return $key === $this->defaultKey()
            ? $url
            : $url . '?' . $this->parameter() . '=' . urlencode($key);
    }

    /**
     * Record where HR is advertising this posting.
     *
     * $selections is [channel_key => ['state' => ..., 'external_url' => ...]].
     * Channels left out are removed — but only if nothing ever arrived
     * through them. A channel with visits or applications is kept and set
     * back to Not Posted instead: the numbers are history, and history is
     * not something an unticked checkbox should delete.
     *
     * @return array{kept: string[], removed: string[], retained: string[]}
     */
    public function sync(JobPosting $posting, array $selections): array
    {
        $default = $this->defaultKey();
        $kept = [];
        $removed = [];
        $retained = [];

        foreach ($selections as $key => $selection) {
            $key = trim((string) $key);

            if ($key === '' || $key === $default) {
                continue;   // the default bucket is not HR's to post to
            }

            $state = ($selection['state'] ?? null) === JobPostingChannel::POSTED
                ? JobPostingChannel::POSTED
                : JobPostingChannel::NOT_POSTED;

            $row = JobPostingChannel::firstOrNew([
                'job_posting_id' => $posting->id,
                'channel_key' => $key,
            ]);

            // First time it is marked Posted, stamp when. Un-ticking and
            // re-ticking keeps the original date: that is when the ad went up.
            if ($state === JobPostingChannel::POSTED && !$row->posted_at) {
                $row->posted_at = now();
            }

            $row->state = $state;

            if (array_key_exists('external_url', $selection)) {
                $url = trim((string) $selection['external_url']);
                $row->external_url = $url !== '' ? $url : null;
            }

            $row->save();
            $kept[] = $key;
        }

        $stale = JobPostingChannel::where('job_posting_id', $posting->id)
            ->where('channel_key', '!=', $default)
            ->whereNotIn('channel_key', $kept)
            ->get();

        foreach ($stale as $row) {
            if ($row->visits > 0 || $row->applications > 0) {
                $row->update(['state' => JobPostingChannel::NOT_POSTED]);
                $retained[] = $row->channel_key;
                continue;
            }

            $row->delete();
            $removed[] = $row->channel_key;
        }

        return ['kept' => $kept, 'removed' => $removed, 'retained' => $retained];
    }

    /**
     * What each channel brought this posting.
     *
     * `visits` counts openings of OUR Careers page that arrived tagged with
     * that channel. It is not the platform's view count, and it is a floor
     * rather than a census: an untagged link, or a platform that strips the
     * query string, lands the visit under `direct` instead.
     */
    public function summaryFor(JobPosting $posting): array
    {
        return $this->rowsFor($posting)->map(fn (JobPostingChannel $row) => [
            'key' => $row->channel_key,
            'name' => $row->label(),
            'external' => $row->isExternal(),
            'state' => $row->state,
            'posted_at' => $row->posted_at?->format('M d, Y'),
            'external_url' => $row->external_url,
            'link' => $this->linkFor($posting, $row->channel_key),
            'visits' => $row->visits,
            'applications' => $row->applications,
            'conversion' => $row->conversionRate(),
        ])->all();
    }

    /**
     * Channel totals across every posting, for "where do applicants come from".
     *
     * Reads the applications straight from the applicant database rather than
     * summing the per-posting counters, so a counter that drifted — a row
     * deleted, a migration replayed — cannot quietly distort the answer.
     * Applications from before channel tracking existed have no source and
     * are reported separately as untracked rather than lumped into `direct`.
     */
    public function overallTotals(): array
    {
        $visits = JobPostingChannel::query()
            ->selectRaw('channel_key, SUM(visits) AS visits')
            ->groupBy('channel_key')
            ->pluck('visits', 'channel_key');

        $applications = \Illuminate\Support\Facades\DB::connection('applicant')
            ->table('tblapp_applications')
            ->selectRaw('source_channel, COUNT(*) AS total')
            ->groupBy('source_channel')
            ->pluck('total', 'source_channel');

        $keys = collect($visits->keys())
            ->merge($applications->keys()->filter())
            ->unique()
            ->values();

        $rows = $keys->map(fn ($key) => [
            'key' => $key,
            'name' => $this->label($key),
            'visits' => (int) ($visits[$key] ?? 0),
            'applications' => (int) ($applications[$key] ?? 0),
        ])->sortByDesc('applications')->values()->all();

        return [
            'channels' => $rows,
            // NULL source: applied before there was anything to record. Saying
            // so is more honest than folding them into a channel.
            'untracked_applications' => (int) ($applications[''] ?? $applications->get(null) ?? 0),
        ];
    }
}
