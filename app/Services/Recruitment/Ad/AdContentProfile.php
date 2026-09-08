<?php

namespace App\Services\Recruitment\Ad;

use App\Services\Recruitment\JobSpec\SpecFacts;

/**
 * Decides what kind of ad the Job Spec can support.
 *
 * Two outputs:
 *   tier()       — how much role content exists (rich / partial / sparse),
 *                  which drives section ordering
 *   primaryTag() — which evidence tag the Job Spec earns, which drives the hook
 *
 * The job title is never an input. A "Computer Technician" whose spec says
 * nothing technical gets the neutral hook, by design.
 */
class AdContentProfile
{
    public const TIER_RICH = 'rich';
    public const TIER_PARTIAL = 'partial';
    public const TIER_SPARSE = 'sparse';

    /** @var array<string,int> tag => hit count */
    private array $scores = [];

    public function __construct(private SpecFacts $facts, private array $config)
    {
        $this->score();
    }

    public function tier(): string
    {
        $blocks = $this->facts->contentBlockCount();

        return match (true) {
            $blocks >= 3 => self::TIER_RICH,
            $blocks >= 1 => self::TIER_PARTIAL,
            default      => self::TIER_SPARSE,
        };
    }

    /** @return array<string,int> active tags with their hit counts */
    public function activeTags(): array
    {
        return $this->scores;
    }

    public function primaryTag(): ?string
    {
        // Config order is priority order; first active tag wins.
        foreach (array_keys($this->config['tags'] ?? []) as $tag) {
            if (isset($this->scores[$tag])) {
                return $tag;
            }
        }

        return null;
    }

    public function hook(): string
    {
        $tag = $this->primaryTag();

        return $tag !== null
            ? ($this->config['tags'][$tag]['hook'] ?? $this->config['neutral_hook'])
            : $this->config['neutral_hook'];
    }

    /** Keywords that fired, for the --explain audit trail. */
    public function evidenceFor(string $tag): array
    {
        $text = $this->facts->evidenceText();
        $hits = [];

        foreach ($this->config['tags'][$tag]['keywords'] ?? [] as $keyword) {
            if (str_contains($text, mb_strtolower($keyword))) {
                $hits[] = $keyword;
            }
        }

        return $hits;
    }

    private function score(): void
    {
        $text = $this->facts->evidenceText();

        if ($text === '') {
            return;
        }

        foreach ($this->config['tags'] ?? [] as $tag => $rule) {
            $hits = 0;
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($text, mb_strtolower($keyword))) {
                    $hits++;
                }
            }

            if ($hits >= ($rule['min_hits'] ?? 1)) {
                $this->scores[$tag] = $hits;
            }
        }
    }
}
