<?php

/*
|--------------------------------------------------------------------------
| Public advertisement templates — the registry
|--------------------------------------------------------------------------
|
| Three shapes of public ad, and the rules that pick between them. Previously
| AdSectionPlanner held one hard-coded section order per tier; this makes the
| set open to editing without touching the composer.
|
| WHY CONFIG AND NOT A DATABASE TABLE
| -----------------------------------
| Every value in 'sections' is a section key that JobAdComposer::renderSection()
| must have a match arm for. A row in a table could name a section the renderer
| cannot build, and the failure would appear on the careers page. Templates also
| change with a deployment, not at runtime, and they benefit from code review and
| version history. So this is a registry in the same sense a table would be —
| one place listing every template, with its selection rule and structure — but
| it cannot fall out of step with the renderer, and it needs no migration.
| If HR ever needs to edit ads without a deploy, revisit it then.
|
| SELECTION
| ---------
| The signals already existed: AdContentProfile computes a tier from how much
| the Job Spec contains, and a primary tag from the keyword sets in
| config/job_ad.php. Templates are tested in 'priority' order (low first); the
| first whose rule matches wins, and 'detailed' is the guaranteed fallback.
|
|   tiers   match AdContentProfile::TIER_* — empty means any tier
|   tags    match the keys of config('job_ad.tags') — empty means any tag
|
| SECTIONS
| --------
| 'head', 'body' and 'tail' are concatenated. A section is dropped when the
| Job Spec has nothing for it, so no template ever renders an empty heading.
|
*/

return [

    'default' => 'detailed',

    'templates' => [

        /*
        | The full ad: enough duties and skills on the spec to describe the
        | work itself, so it leads with the work and qualifications follow.
        */
        'detailed' => [
            'name' => 'Detailed / Professional',
            'description' => 'Leads with the duties, then skills, then qualifications. For specs with plenty of detail.',
            // Ahead of Simple on purpose: a spec with plenty of detail earns
            // the full ad even if its wording also reads as a high-volume
            // role. Order of precedence is technical tag, then tier, then
            // volume tag. It is also the fallback when nothing matches.
            'priority' => 20,
            'when' => [
                'tiers' => ['rich'],
                'tags' => [],
            ],
            'head' => ['hook', 'headline', 'location', 'department', 'employment', 'spacer'],
            'body' => ['duties', 'tech', 'competencies', 'computer', 'other', 'spacer', 'education', 'experience'],
            'tail' => ['spacer', 'benefits', 'spacer', 'apply'],
        ],

        /*
        | Technical and trade roles, where the tools and skills are what a
        | reader scans for — so those come first, before the duties.
        */
        'skills_first' => [
            'name' => 'Skills-first / Technical',
            'description' => 'Puts the required skills and tools above the duties. For technical, trade and software roles.',
            'priority' => 10,
            'when' => [
                // 'partial' reproduces the planner's old behaviour: a spec
                // with some skills but not much else led with those skills.
                'tiers' => ['partial'],
                'tags' => ['programming', 'technical_repair', 'design_layout', 'office_software'],
                'match' => 'any',
            ],
            'head' => ['hook', 'headline', 'location', 'department', 'employment', 'spacer'],
            'body' => ['tech', 'computer', 'other', 'competencies', 'spacer', 'duties', 'spacer', 'education', 'experience'],
            'tail' => ['spacer', 'benefits', 'spacer', 'apply'],
        ],

        /*
        | High-volume and thin specs: a short ad reads better than a long one
        | padded out, and a sparse spec has little to pad with anyway.
        */
        'simple' => [
            'name' => 'Simple / High-volume',
            'description' => 'A short ad: the essentials and how to apply. For sparse specs and high-volume roles.',
            'priority' => 50,
            'when' => [
                // Sparse only. A 'partial' spec has enough to say to earn the
                // skills-first layout instead.
                'tiers' => ['sparse'],
                'tags' => ['sales_customer', 'inventory_logistics', 'data_records'],
                // Either signal is enough here, unlike Detailed where every
                // listed condition must hold.
                'match' => 'any',
            ],
            'head' => ['hook', 'headline', 'location', 'department', 'employment', 'spacer'],
            'body' => ['duties', 'education', 'experience'],
            'tail' => ['spacer', 'benefits', 'spacer', 'apply'],
        ],
    ],
];
