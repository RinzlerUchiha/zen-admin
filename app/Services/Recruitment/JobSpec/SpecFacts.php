<?php

namespace App\Services\Recruitment\JobSpec;

/**
 * Normalised, publication-safe view of one HireFlow Job Specification.
 *
 * Every list here was parsed from a single Job Spec column, and `raw` keeps
 * the original column values so AdSafetyValidator can prove that each line
 * of a finished ad traces back to one of them.
 */
final class SpecFacts
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $departmentName,
        public readonly ?string $sectionName,
        public readonly ?string $employmentStatus,
        /** @var string[] */ public readonly array $education,
        /** @var string[] */ public readonly array $workexp,
        public readonly bool $experienceNotRequired,
        /** @var string[] */ public readonly array $duties,
        /** @var string[] */ public readonly array $techCompetencies,
        /** @var string[] */ public readonly array $competencies,
        /** @var string[] */ public readonly array $computerSkills,
        /** @var string[] */ public readonly array $otherSkills,
        /** @var array<string,string> field => original column value */
        public readonly array $raw,
        /** @var array<string,string> excluded field => value, for leak checks */
        public readonly array $excluded,
    ) {
    }

    /**
     * How many role-content blocks carry usable text. Drives the ad's tier:
     * 3+ is rich, 1-2 partial, 0 sparse.
     */
    public function contentBlockCount(): int
    {
        return count(array_filter([
            $this->duties,
            $this->techCompetencies,
            $this->competencies,
            $this->computerSkills,
            $this->otherSkills,
        ]));
    }

    public function hasAnyContent(): bool
    {
        return $this->contentBlockCount() > 0;
    }

    /**
     * The text evidence tags are scored against. Job title is excluded by
     * design — a hook must be earned by what the Job Spec says, not by what
     * the title implies.
     */
    public function evidenceText(): string
    {
        return mb_strtolower(implode(' ', array_merge(
            $this->duties,
            $this->techCompetencies,
            $this->competencies,
            $this->computerSkills,
            $this->otherSkills,
            $this->workexp,
        )));
    }
}
