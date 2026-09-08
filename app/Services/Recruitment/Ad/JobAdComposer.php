<?php

namespace App\Services\Recruitment\Ad;

use App\Exceptions\AdCompositionException;
use App\Models\Recruitment\HireflowManpowerPosition;
use App\Services\Recruitment\JobSpec\SpecFactExtractor;
use App\Services\Recruitment\JobSpec\SpecFacts;
use Illuminate\Support\Facades\Log;

/**
 * Job Spec → public ad.
 *
 *   extract  → what does this Job Spec actually say?
 *   profile  → how much content is there, and what does it evidence?
 *   plan     → which sections, in which order?
 *   render   → build blocks, each tagged with its provenance
 *   validate → prove every factual line traces to the Job Spec
 *
 * Deterministic: same Job Spec always yields the same ad. No API calls.
 */
class JobAdComposer
{
    private array $config;
    private ?string $lastFallbackReason = null;
    private SpecFactExtractor $extractor;
    private AdSectionPlanner $planner;
    private AdSafetyValidator $validator;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? (array) config('job_ad', []);

        // Company copy that lives on the model stays the source of truth for
        // benefits and employment wording; register it so the validator will
        // accept those lines.
        $this->config['runtime_approved'] = $this->companyCopy();

        $this->extractor = new SpecFactExtractor($this->config);
        $this->planner   = new AdSectionPlanner();
        $this->validator = new AdSafetyValidator($this->config);
    }

    public function composeFor(HireflowManpowerPosition $position): string
    {
        $facts = $this->extractor->extract($position->jobSpec(), $position->positionTitle());

        return $this->compose($facts);
    }

    public function compose(SpecFacts $facts): string
    {
        $this->lastFallbackReason = null;

        $profile = new AdContentProfile($facts, $this->config);
        $blocks  = $this->render($facts, $profile);

        try {
            $this->validator->assertSafe($blocks, $facts);
        } catch (AdCompositionException $e) {
            $this->lastFallbackReason = $e->getMessage();

            Log::warning('[job-ad] composition rejected, falling back', [
                'title'  => $facts->title,
                'reason' => $e->getMessage(),
            ]);

            $blocks = $this->minimalBlocks($facts);
            $this->validator->assertSafe($blocks, $facts);
        }

        return $this->serialise($blocks);
    }

    /**
     * Why the last compose() fell back to the minimal ad, or null if the
     * composed ad passed validation. Surfaced by --explain.
     */
    public function lastFallbackReason(): ?string
    {
        return $this->lastFallbackReason;
    }

    /** Exposed for the --explain audit output. */
    public function profileFor(SpecFacts $facts): AdContentProfile
    {
        return new AdContentProfile($facts, $this->config);
    }

    public function extractor(): SpecFactExtractor
    {
        return $this->extractor;
    }

    /* ------------------------------ render ---------------------------- */

    /** @return AdBlock[] */
    private function render(SpecFacts $facts, AdContentProfile $profile): array
    {
        $employment = HireflowManpowerPosition::employmentCopyFor($facts->employmentStatus);
        $blocks = [];

        foreach ($this->planner->plan($facts, $profile) as $section) {
            array_push($blocks, ...$this->renderSection($section, $facts, $profile, $employment));
        }

        return $this->collapseSpacers($blocks);
    }

    /** @return AdBlock[] */
    private function renderSection(
        string $section,
        SpecFacts $facts,
        AdContentProfile $profile,
        array $employment
    ): array {
        $c = $this->config['connectives'] ?? [];

        return match ($section) {
            'spacer'   => [AdBlock::blank()],
            'hook'     => [AdBlock::controlled($profile->hook()), AdBlock::blank()],
            'headline' => [AdBlock::source(mb_strtoupper($facts->title), 'title', '📣 HIRING: ')],
            'location' => [AdBlock::controlled(HireflowManpowerPosition::AD_LOCATION, '📍 ')],

            'department' => $facts->departmentName !== null
                ? [AdBlock::source($facts->departmentName, 'department_name', '🏢 ')]
                : [AdBlock::source((string) $facts->sectionName, 'section_name', '🏢 ')],

            'employment' => $employment['label'] !== null
                ? [AdBlock::controlled($employment['label'], '💼 ')]
                : [],

            // Headings vary with tier so a rich ad and a thin one read
            // differently rather than being the same skeleton with gaps.
            'duties' => $this->listSection(
                $profile->tier() === AdContentProfile::TIER_RICH ? $c['rich_lead'] : $c['duties_heading'],
                $facts->duties,
                'jspec_duties',
                '🔹 '
            ),
            'tech'         => $this->listSection($c['tech_heading'], $facts->techCompetencies, 'jspec_techcompetencies', '✔️ '),
            'competencies' => $this->listSection($c['competencies_heading'], $facts->competencies, 'jspec_competencies', '✔️ '),
            'computer'     => $this->listSection(
                $profile->tier() === AdContentProfile::TIER_PARTIAL ? $c['skills_lead'] : $c['computer_heading'],
                $facts->computerSkills,
                'jspec_computerskill',
                '💻 '
            ),
            'other'        => $this->listSection($c['other_heading'], $facts->otherSkills, 'jspec_otherskill', '✔️ '),
            'education'    => $this->listSection($c['education_heading'], $facts->education, 'jspec_education', '🎓 '),

            'experience' => $facts->experienceNotRequired
                ? [AdBlock::connective($c['no_experience']), AdBlock::blank()]
                : $this->listSection($c['experience_heading'], $facts->workexp, 'jspec_workexp', '🧰 '),

            'benefits' => $this->benefitBlocks($employment),
            'apply'    => [AdBlock::controlled($this->config['apply_line'])],

            default => [],
        };
    }

    /** @return AdBlock[] */
    private function listSection(string $heading, array $items, string $origin, string $bullet): array
    {
        if ($items === []) {
            return [];
        }

        $blocks = [AdBlock::connective($heading)];

        foreach ($items as $item) {
            $blocks[] = AdBlock::source($this->present($item), $origin, $bullet);
        }

        $blocks[] = AdBlock::blank();

        return $blocks;
    }

    /** @return AdBlock[] */
    private function benefitBlocks(array $employment): array
    {
        $blocks = [];

        foreach (HireflowManpowerPosition::AD_PERKS as $perk) {
            $text = $perk === ':growth' ? $employment['perk'] : $perk;
            $blocks[] = AdBlock::controlled($text, '💎 ');
        }

        return $blocks;
    }

    /**
     * Presentation-only cleanup: approved spelling/spacing fixes, and an
     * optional reflow of "X (a, b, c)" into "X, including a, b, c". Adds no
     * words beyond the approved filler list, which the validator enforces.
     */
    private function present(string $text): string
    {
        foreach ($this->config['typo_map'] ?? [] as $from => $to) {
            $text = preg_replace(AdSafetyValidator::typoPattern($from), $to, $text) ?? $text;
        }

        $text = preg_replace('/\s*\(\s*/u', ' (', $text) ?? $text;
        $text = preg_replace('/\s+\)/u', ')', $text) ?? $text;
        $text = preg_replace('/,\s*\)/u', ')', $text) ?? $text;

        if ($this->config['parenthetical_to_prose'] ?? false) {
            $text = preg_replace_callback(
                '/^(.*?)\s*\(([^()]*,[^()]*)\)\s*$/u',
                fn ($m) => rtrim($m[1]) . ', including '
                    . trim(preg_replace('/^using\s+/iu', '', trim($m[2])) ?? trim($m[2])),
                $text
            ) ?? $text;
        }

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /** Last-resort ad: title, employment, approved benefits, apply line. */
    private function minimalBlocks(SpecFacts $facts): array
    {
        $employment = HireflowManpowerPosition::employmentCopyFor($facts->employmentStatus);

        $blocks = [
            AdBlock::controlled($this->config['neutral_hook']),
            AdBlock::blank(),
            AdBlock::source(mb_strtoupper($facts->title), 'title', '📣 HIRING: '),
            AdBlock::controlled(HireflowManpowerPosition::AD_LOCATION, '📍 '),
        ];

        if ($employment['label'] !== null) {
            $blocks[] = AdBlock::controlled($employment['label'], '💼 ');
        }

        $blocks[] = AdBlock::blank();
        array_push($blocks, ...$this->benefitBlocks($employment));
        $blocks[] = AdBlock::blank();
        $blocks[] = AdBlock::controlled($this->config['apply_line']);

        return $blocks;
    }

    /** @param AdBlock[] $blocks */
    private function collapseSpacers(array $blocks): array
    {
        $out = [];
        $previousBlank = true;

        foreach ($blocks as $block) {
            if ($block->isBlank()) {
                if ($previousBlank) {
                    continue;
                }
                $previousBlank = true;
            } else {
                $previousBlank = false;
            }

            $out[] = $block;
        }

        while ($out !== [] && end($out)->isBlank()) {
            array_pop($out);
        }

        return $out;
    }

    private function serialise(array $blocks): string
    {
        return implode("\n", array_map(fn (AdBlock $b) => $b->render(), $blocks));
    }

    private function companyCopy(): array
    {
        $copy = [HireflowManpowerPosition::AD_LOCATION];

        foreach (HireflowManpowerPosition::AD_PERKS as $perk) {
            if ($perk !== ':growth') {
                $copy[] = $perk;
            }
        }

        foreach (HireflowManpowerPosition::AD_EMPLOYMENT_COPY as $entry) {
            $copy[] = $entry['label'];
            $copy[] = $entry['perk'];
        }

        $copy[] = HireflowManpowerPosition::AD_EMPLOYMENT_NEUTRAL['perk'];

        return array_values(array_unique(array_filter($copy)));
    }
}
