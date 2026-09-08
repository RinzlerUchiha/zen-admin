<?php

namespace App\Services\Recruitment\Ad;

use App\Services\Recruitment\JobSpec\SpecFacts;

/**
 * Chooses which sections appear and in what order.
 *
 * The order changes with how much the Job Spec actually contains, so a rich
 * spec and a sparse one produce differently shaped ads rather than the same
 * skeleton with gaps:
 *
 *   rich    → leads with the work itself (duties), then skills, then quals
 *   partial → leads with the skills that exist, then quals
 *   sparse  → leads with qualifications, because that is all there is
 *
 * A section is only planned when its source items exist, so nothing renders
 * an empty heading.
 */
class AdSectionPlanner
{
    public function plan(SpecFacts $facts, AdContentProfile $profile): array
    {
        $head = ['hook', 'headline', 'location', 'department', 'employment', 'spacer'];
        $tail = ['spacer', 'benefits', 'spacer', 'apply'];

        $body = match ($profile->tier()) {
            AdContentProfile::TIER_RICH => [
                'duties', 'tech', 'competencies', 'computer', 'other',
                'spacer', 'education', 'experience',
            ],
            AdContentProfile::TIER_PARTIAL => [
                'computer', 'tech', 'competencies', 'other', 'duties',
                'spacer', 'education', 'experience',
            ],
            default => [
                'education', 'experience',
            ],
        };

        return array_values(array_filter(
            array_merge($head, $body, $tail),
            fn (string $section) => $this->hasContentFor($section, $facts)
        ));
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
