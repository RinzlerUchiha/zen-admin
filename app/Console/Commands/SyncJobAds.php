<?php

namespace App\Console\Commands;

use App\Models\Recruitment\JobPosting;
use App\Services\Recruitment\Ad\JobAdComposer;
use Illuminate\Console\Command;

/**
 * Re-composes the public ad of every live posting from its HireFlow Job Spec.
 *
 * Postings whose ad was hand-edited (ad_is_custom) are never overwritten. This
 * is what lets HR correct a Job Spec in HireFlow and have the careers page
 * follow without anyone touching zen-admin.
 *
 * WRITES BY DEFAULT. Use --dry-run for a read-only pass; combine it with
 * --explain and --preview to audit exactly what would be published and why.
 */
class SyncJobAds extends Command
{
    protected $signature = 'recruitment:sync-job-ads
                            {--dry-run : Read-only. Report what would change without saving}
                            {--explain : Show the tier, hook evidence and field contributions}
                            {--preview : Print the full composed ad for each posting}
                            {--id=* : Limit to specific job posting ids}';

    protected $description = 'Refresh auto-composed public job ads from their HireFlow job specifications';

    public function handle(): int
    {
        $query = JobPosting::with('hireflowPosition')
            ->whereIn('status', ['Draft', 'Published']);

        if ($ids = $this->option('id')) {
            $query->whereIn('id', $ids);
        }

        $postings  = $query->get();
        $dryRun    = (bool) $this->option('dry-run');
        $explain   = (bool) $this->option('explain');
        $preview   = (bool) $this->option('preview');

        $refreshed = 0;
        $skipped   = 0;
        $orphaned  = 0;

        if ($dryRun) {
            $this->comment('DRY RUN — no records will be modified.');
            $this->newLine();
        }

        foreach ($postings as $posting) {
            if (!$posting->hireflowPosition) {
                $orphaned++;
                $this->warn("  #{$posting->id} {$posting->posting_title} — no linked HireFlow position, skipped");
                continue;
            }

            $custom = (bool) $posting->ad_is_custom;

            // Reporting runs for every posting, including hand-edited and
            // dry-run ones, so an audit never has to touch the write path.
            if ($explain || $preview) {
                $this->newLine();
                $this->line(str_repeat('=', 72));
                $this->line("#{$posting->id}  {$posting->posting_title}  [{$posting->status}]");
                $this->line(str_repeat('=', 72));
            }

            if ($explain) {
                $this->explain($posting, $custom);
            }

            if ($preview) {
                $this->previewAd($posting);
            }

            if ($custom) {
                $skipped++;
                if ($explain) {
                    $this->line('  ownership: HAND-EDITED — this ad is protected and will not be regenerated.');
                }
                continue;
            }

            if ($dryRun) {
                $fresh = $posting->hireflowPosition->draftPublicAd();
                if ($fresh !== $posting->public_description) {
                    $refreshed++;
                    $this->line("  #{$posting->id} {$posting->posting_title} — would be refreshed");
                }
                continue;
            }

            if ($posting->syncPublicAd()) {
                $refreshed++;
                $this->line("  #{$posting->id} {$posting->posting_title} — refreshed");
            }
        }

        $this->newLine();
        $this->info(sprintf(
            '%s %d posting(s); %d hand-edited skipped, %d without a linked position.',
            $dryRun ? 'Would refresh' : 'Refreshed',
            $refreshed,
            $skipped,
            $orphaned
        ));

        return self::SUCCESS;
    }

    /**
     * Audit trail for one posting: the tier its Job Spec reached, which
     * evidence tag won the hook and exactly which keywords fired, what each
     * field contributed, which raw values were ignored, and whether the
     * safety validator rejected the composed ad.
     */
    private function explain(JobPosting $posting, bool $custom): void
    {
        $position = $posting->hireflowPosition;

        $composer = new JobAdComposer();
        $facts    = $composer->extractor()->extract($position->jobSpec(), $position->positionTitle());
        $profile  = $composer->profileFor($facts);
        $tag      = $profile->primaryTag();

        $composer->compose($facts); // populates lastFallbackReason()

        $this->line('  tier:      ' . strtoupper($profile->tier())
            . " ({$facts->contentBlockCount()} of 5 content fields populated)");
        $this->line('  hook tag:  ' . ($tag ?? 'none — neutral hook'));

        if ($tag !== null) {
            $this->line('  evidence:  ' . implode(', ', $profile->evidenceFor($tag)));
        }

        if ($others = array_diff(array_keys($profile->activeTags()), [$tag])) {
            $this->line('  also matched (lower priority): ' . implode(', ', $others));
        }

        $this->line('  employment: ' . ($facts->employmentStatus ?? '(empty)'));

        $this->newLine();
        $this->line('  field contributions:');

        $contributions = [
            'jspec_duties'           => $facts->duties,
            'jspec_techcompetencies' => $facts->techCompetencies,
            'jspec_competencies'     => $facts->competencies,
            'jspec_computerskill'    => $facts->computerSkills,
            'jspec_otherskill'       => $facts->otherSkills,
            'jspec_education'        => $facts->education,
            'jspec_workexp'          => $facts->workexp,
        ];

        foreach ($contributions as $field => $items) {
            $raw = trim((string) ($facts->raw[$field] ?? ''));

            if ($items === []) {
                $note = $raw === ''
                    ? 'empty in Job Spec'
                    : 'IGNORED — not readable content: "' . mb_substr($raw, 0, 40) . '"';
                $this->line(sprintf('    %-24s 0 items  (%s)', $field, $note));
                continue;
            }

            $this->line(sprintf('    %-24s %d item(s)', $field, count($items)));
            foreach ($items as $item) {
                $this->line('        · ' . $item);
            }
        }

        $reason = $composer->lastFallbackReason();
        $this->newLine();
        $this->line('  validation: ' . ($reason === null
            ? 'PASSED — every factual line traces to the Job Spec'
            : 'FAILED, fell back to minimal ad — ' . $reason));

        if ($custom) {
            $this->line('  note: this posting is hand-edited; the ad above is what WOULD be'
                . ' generated, not what is published.');
        }
    }

    private function previewAd(JobPosting $posting): void
    {
        $this->newLine();
        $this->line('  --- composed ad ---');
        foreach (explode("\n", $posting->hireflowPosition->draftPublicAd()) as $line) {
            $this->line('  ' . $line);
        }
        $this->line('  --- end ---');
    }
}
