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
     * Give a posting the channels HR will probably use, so they start ticked.
     *
     * A new posting used to arrive with nothing but the `direct` bucket, so
     * publishing it produced no links to copy and HR had to go and tick the
     * places they were about to post to before the panel would give them
     * anything. The places we advertise do not change from posting to
     * posting; the exceptions do.
     *
     * So every EXTERNAL channel in the catalogue gets a row, Not Posted. A
     * row means "this channel is available for this posting and will be
     * offered a link" -- it does NOT mean the ad has gone up, which is what
     * `state` says and what HR ticks when they have actually posted it.
     *
     * Switching one off is deleting its row, which sync() already does, and
     * already refuses to do when anything has arrived through it. Nothing
     * here can lose a number.
     *
     * Idempotent, and it never touches a channel that already exists -- so
     * running it against a posting HR has already curated changes nothing.
     *
     * HR MAY NARROW IT
     *
     * $only, when given, is the set of channel keys HR left ticked on the
     * create form. Every external channel starts ticked there, so passing
     * nothing and passing all of them mean the same thing; unticking one
     * means its row is never created, and it is therefore never offered a
     * link. An empty array is a real answer -- "advertise this nowhere but
     * the Careers page" -- and is not the same as null.
     *
     * @param  string[]|null  $only
     * @return string[] the channel keys it added
     */
    public function seedFor(JobPosting $posting, ?array $only = null): array
    {
        $default = $this->defaultKey();

        $existing = JobPostingChannel::where('job_posting_id', $posting->id)
            ->pluck('channel_key')->all();

        $added = [];

        foreach ($this->catalogue() as $key => $channel) {
            if ($only !== null && !in_array($key, $only, true)) {
                continue;
            }

            /* The default bucket is not somewhere HR posts, and rowsFor()
               creates it on demand anyway. */
            if ($key === $default || !($channel['external'] ?? true)) {
                continue;
            }

            if (in_array($key, $existing, true)) {
                continue;
            }

            JobPostingChannel::create([
                'job_posting_id' => $posting->id,
                'channel_key' => $key,
                'state' => JobPostingChannel::NOT_POSTED,
                'visits' => 0,
                'applications' => 0,
            ]);

            $added[] = $key;
        }

        return $added;
    }

    /**
     * Seed a posting that is still being advertised and has never been
     * given channels.
     *
     * Postings created before seeding existed would otherwise keep their
     * empty panel for ever, which would leave two postings behaving
     * differently for no reason a user could see.
     *
     * ONLY WHEN THERE ARE NONE
     *
     * This is the important part, and the first version got it wrong. Seeding
     * on every open re-added whatever HR had just unticked, because unticking
     * a channel IS deleting its row -- so the panel quietly undid the
     * decision it had been opened to make, and nothing on screen said so.
     * Seeding only when the posting has no external channels at all means it
     * happens once, to a posting that genuinely never had any.
     *
     * The remaining edge: a draft whose channels HR removed every one of
     * would be seeded again on the next open. That is deliberate -- an empty
     * panel is indistinguishable from a broken one -- and a posting
     * advertised nowhere at all is not a case the Careers flow supports.
     *
     * DRAFT AND PUBLISHED, NEVER CLOSED
     *
     * This began as drafts only, on the reasoning that a published posting
     * had been through HR's hands already and adding channels after the fact
     * would change what it offers. In practice that reasoning was backwards.
     * The postings that predate seeding are exactly the ones already
     * published, and leaving them out meant the live job -- the one with
     * applicants against it -- was the single posting in the system with no
     * channels, a bare panel, and a publish that handed over no links at all.
     * Seeding it adds Not Posted rows: somewhere HR *may* advertise. It
     * claims nothing was posted and changes no figure.
     *
     * Closed is still left alone. A closed posting is not being advertised
     * any more, so offering it somewhere new to be advertised is meaningless,
     * and sync() refuses to change a closed posting's channels anyway.
     *
     * @return string[] the channel keys it added
     */
    public function seedOpen(JobPosting $posting): array
    {
        if (!in_array($posting->status, ['Draft', 'Published'], true)) {
            return [];
        }

        $hasAny = JobPostingChannel::where('job_posting_id', $posting->id)
            ->where('channel_key', '!=', $this->defaultKey())
            ->exists();

        return $hasAny ? [] : $this->seedFor($posting);
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
    /**
     * Switch this posting's channels on as it goes live.
     *
     * HR chose where to advertise on the create form; having chosen, they
     * then had to go into the panel afterwards and tick every one of those
     * same sites by hand before it counted as switched on. The posting was
     * live, the links existed, and every channel sat there saying Not Posted.
     * Choosing a site IS switching it on; the second act of ticking said
     * nothing the first had not.
     *
     * Only at the moment it FIRST goes live, and only for channels that
     * already have a row -- a site HR unticked has no row and is not brought
     * back. Afterwards the ticks are HR's own: unticking one later is a
     * deliberate act and nothing here overrides it, because a posting only
     * goes live for the first time once.
     *
     * @return string[] the keys it switched on
     */
    public function enableOnPublish(JobPosting $posting): array
    {
        $default = $this->defaultKey();
        $turnedOn = [];

        $rows = JobPostingChannel::where('job_posting_id', $posting->id)
            ->where('channel_key', '!=', $default)
            ->get();

        foreach ($rows as $row) {
            if (!$this->isExternal($row->channel_key) || $row->state === JobPostingChannel::POSTED) {
                continue;
            }

            if (!$row->posted_at) {
                $row->posted_at = now();
            }

            $row->state = JobPostingChannel::POSTED;
            $row->save();

            $turnedOn[] = $row->channel_key;
        }

        return $turnedOn;
    }

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
