<?php

namespace App\Services\Recruitment\Ad;

use App\Services\Recruitment\JobSpec\SpecFacts;

/**
 * Chooses which sections appear and in what order.
 *
 * The order comes from whichever template the Job Spec earns — see
 * config/job_ad_templates.php and AdTemplateRegistry. It used to be one
 * hard-coded order per tier here; templates keep that behaviour for a rich
 * spec while allowing a technical or high-volume role to read differently.
 *
 * A section is only planned when its source items exist, so nothing renders
 * an empty heading whichever template is chosen.
 */
class AdSectionPlanner
{
    public function __construct(private ?AdTemplateRegistry $registry = null)
    {
        $this->registry = $registry ?? new AdTemplateRegistry();
    }

    /** The template the last plan() used, for reporting it to HR. */
    private ?array $lastTemplate = null;

    /** Set when HR asks for a specific template instead of the recommended one. */
    private ?string $forced = null;

    public function forceTemplate(?string $key): void
    {
        $this->forced = $key;
    }

    public function plan(SpecFacts $facts, AdContentProfile $profile): array
    {
        $resolved = $this->registry->resolve($profile, $this->forced);
        $this->lastTemplate = $resolved;

        $t = $resolved['template'];

        return array_values(array_filter(
            array_merge($t['head'] ?? [], $t['body'] ?? [], $t['tail'] ?? []),
            fn (string $section) => $this->hasContentFor($section, $facts)
        ));
    }

    /** @return array{key:string,template:array,reason:string}|null */
    public function lastTemplate(): ?array
    {
        return $this->lastTemplate;
    }

    private function hasContentFor(string $section, SpecFacts $facts): bool
    {
        return match ($section) {
            'department'   => $facts->departmentName !== null || $facts->sectionName !== null,
            'employment'   => $facts->employmentStatus !== null,
            'duties'       => $facts->duties !== [],
            'tech'         => $facts->techCompetencies !== [],
            'competencies' => $facts->competencies !== [],
            'computer'     => $facts->computerSkills !== [],
            'other'        => $facts->otherSkills !== [],
            'education'    => $facts->education !== [],
            'experience'   => $facts->workexp !== [],
            default        => true,
        };
    }
}
