<?php

namespace Tests\Unit;

use App\Exceptions\AdCompositionException;
use App\Services\Recruitment\Ad\AdBlock;
use App\Services\Recruitment\Ad\AdContentProfile;
use App\Services\Recruitment\Ad\AdSafetyValidator;
use App\Services\Recruitment\Ad\JobAdComposer;
use App\Services\Recruitment\JobSpec\SpecFactExtractor;
use PHPUnit\Framework\TestCase;

/**
 * The public ad must never claim anything the Job Spec does not say.
 * These tests are the enforcement of that rule.
 */
class JobAdComposerTest extends TestCase
{
    private const DELIM = '%#';

    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = require __DIR__ . '/../../config/job_ad.php';
        // Department resolution needs the hrd2 connection; not under test here.
        $this->config['show_department'] = false;
        $this->config['show_section'] = false;
    }

    private function spec(array $overrides): object
    {
        $blank = array_fill_keys([
            'jspec_department', 'jspec_section', 'jspec_emplstat', 'jspec_education',
            'jspec_workexp', 'jspec_duties', 'jspec_techcompetencies', 'jspec_competencies',
            'jspec_computerskill', 'jspec_otherskill', 'jspec_sex', 'jspec_agerange',
            'jspec_headsnum', 'jspec_tapt', 'jspec_enneagram', 'jspec_career',
            'jspec_motivation', 'jspec_personality', 'jspec_ravenl', 'jspec_ravena',
            'jspec_ravenh', 'jspec_leadership', 'jspec_reason', 'jspec_remarks',
            'jspec_created_by', 'jspec_learnstyle', 'jspec_mpa', 'jspec_mpb', 'jspec_mpc',
            'jspec_mpd', 'jspec_mpe', 'jspec_mpf', 'jspec_mpg',
        ], '');

        return (object) array_merge($blank, $overrides);
    }

    private function compose(string $title, array $overrides): string
    {
        $extractor = new SpecFactExtractor($this->config);
        $composer = new JobAdComposer($this->config);

        return $composer->compose($extractor->extract($this->spec($overrides), $title));
    }

    /* ---------------------- no invention from titles ------------------- */

    public function test_a_technical_title_with_a_bare_spec_claims_nothing_technical(): void
    {
        $ad = $this->compose('Computer Technician', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'High School Graduate',
        ]);

        $this->assertStringContainsString('COMPUTER TECHNICIAN', $ad);

        foreach (['troubleshoot', 'repair', 'hardware', 'network', 'install'] as $invented) {
            $this->assertStringNotContainsStringIgnoringCase($invented, $ad);
        }
    }

    public function test_inventory_title_with_a_bare_spec_claims_no_stock_work(): void
    {
        $ad = $this->compose('Inventory Clerk', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'Vocational Course Graduate',
        ]);

        foreach (['stock', 'warehouse', 'counting', 'receiving'] as $invented) {
            $this->assertStringNotContainsStringIgnoringCase($invented, $ad);
        }
    }

    /* ------------------------- omission, not filler -------------------- */

    public function test_missing_sections_are_omitted_rather_than_filled(): void
    {
        $ad = $this->compose('Sales Consultant', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'High School Graduate',
        ]);

        $c = $this->config['connectives'];
        $this->assertStringNotContainsString($c['duties_heading'], $ad);
        $this->assertStringNotContainsString($c['tech_heading'], $ad);
        $this->assertStringNotContainsString($c['computer_heading'], $ad);
    }

    public function test_placeholder_numeric_duties_are_never_published(): void
    {
        $ad = $this->compose('Inventory Clerk', [
            'jspec_emplstat' => 'Regular',
            'jspec_duties' => '23',
        ]);

        $this->assertStringNotContainsString('23', $ad);
        $this->assertStringNotContainsString($this->config['connectives']['duties_heading'], $ad);
    }

    /* ---------------------- role-specific from content ----------------- */

    public function test_the_hook_is_earned_by_spec_content_not_by_the_title(): void
    {
        $technical = $this->compose('Staff A', [
            'jspec_duties' => "Troubleshoots hardware and network issues",
        ]);
        $inventory = $this->compose('Staff A', [
            'jspec_duties' => "Conducts physical stock counting weekly",
        ]);

        $this->assertNotSame($technical, $inventory);
        $this->assertStringContainsString('TROUBLESHOOT', $technical);
        $this->assertStringContainsString('STOCKS', $inventory);
    }

    public function test_supervisory_hook_ignores_reporting_to_a_supervisor(): void
    {
        $ad = $this->compose('Inventory Clerk', [
            'jspec_duties' => "Prepares inventory variance reports for the Supervisor",
        ]);

        $this->assertStringNotContainsString('MAG-LEAD', $ad);
    }

    public function test_richer_specs_produce_longer_ads(): void
    {
        $sparse = $this->compose('Staff A', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'High School Graduate',
        ]);
        $rich = $this->compose('Staff A', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'High School Graduate',
            'jspec_duties' => "Receives deliveries\nCounts stock weekly",
            'jspec_techcompetencies' => 'Inventory monitoring',
            'jspec_competencies' => 'Attention to detail',
        ]);

        $this->assertGreaterThan(
            substr_count($sparse, "\n"),
            substr_count($rich, "\n")
        );
    }

    /* --------------------------- parsing rules ------------------------- */

    public function test_checkbox_fields_split_on_the_hireflow_delimiter(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_computerskill' =>
                'Proficient in MS Office (Word, Excel, Power Point, Acces, Visio, etc. )'
                . self::DELIM
                . 'Proficient in Accounting Software (Peach Tree, Quick Books, SAP, etc.)',
        ]);

        $this->assertStringContainsString('MS Office', $ad);
        $this->assertStringContainsString('Accounting Software', $ad);
        $this->assertStringNotContainsString(self::DELIM, $ad);
    }

    public function test_grouped_lists_are_not_split_on_inner_commas(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_computerskill' => 'Proficient in MS Office (Word, Excel, Visio)',
        ]);

        $this->assertSame(1, substr_count($ad, 'Proficient in MS Office'));
        $this->assertStringNotContainsString("\n💻 Excel", $ad);
    }

    public function test_prose_duties_are_not_fragmented_on_commas(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_duties' => 'Prepares reports, schedules, and summaries for management',
        ]);

        $this->assertStringContainsString(
            'Prepares reports, schedules, and summaries for management',
            $ad
        );
    }

    public function test_approved_typo_fix_is_applied_once(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_computerskill' => 'Proficient in MS Office (Word, Acces, Visio)',
        ]);

        $this->assertStringContainsString('Access', $ad);
        $this->assertStringNotContainsString('Accesss', $ad);
    }

    /* --------------------- storage delimiter handling ------------------ */

    public function test_education_option_and_detail_are_both_kept_and_readable(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_education' => 'College Graduate (4 year course)%&Any IT Technical Course',
        ]);

        $this->assertStringContainsString('College Graduate (4 year course)', $ad);
        $this->assertStringContainsString('Any IT Technical Course', $ad);
        $this->assertStringNotContainsString('%&', $ad);
    }

    public function test_option_detail_renders_as_one_item_not_two_bullets(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_education' => 'College Graduate (4 year course)%&Any IT Technical Course',
        ]);

        $this->assertSame(1, substr_count($ad, '🎓 '));
        $this->assertStringContainsString(
            '🎓 College Graduate (4 year course) — Any IT Technical Course',
            $ad
        );
    }

    public function test_entry_and_detail_delimiters_combine_correctly(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_education' =>
                'High School Graduate'
                . self::DELIM
                . 'College Graduate (4 year course)%&Any IT Technical Course',
        ]);

        $this->assertSame(2, substr_count($ad, '🎓 '));
        $this->assertStringContainsString('High School Graduate', $ad);
        $this->assertStringContainsString('Any IT Technical Course', $ad);
        $this->assertStringNotContainsString('%&', $ad);
        $this->assertStringNotContainsString(self::DELIM, $ad);
    }

    public function test_specify_marker_does_not_swallow_the_detail(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_education' => 'Masterate / Doctoral**Specify%&Master in Information Systems',
        ]);

        $this->assertStringContainsString('Masterate / Doctoral', $ad);
        $this->assertStringContainsString('Master in Information Systems', $ad);
        $this->assertStringNotContainsString('**Specify', $ad);
    }

    public function test_detail_delimiter_in_a_prose_field_does_not_leak(): void
    {
        $ad = $this->compose('Staff A', [
            'jspec_duties' => 'Encodes daily sales data%&Verifies branch documents',
        ]);

        $this->assertStringNotContainsString('%&', $ad);
        $this->assertStringContainsString('Encodes daily sales data', $ad);
        $this->assertStringContainsString('Verifies branch documents', $ad);
    }

    public function test_validator_rejects_a_leaked_storage_delimiter(): void
    {
        $extractor = new SpecFactExtractor($this->config);
        $facts = $extractor->extract(
            $this->spec(['jspec_education' => 'College Graduate (4 year course)%&Any IT Technical Course']),
            'Staff A'
        );

        $validator = new AdSafetyValidator($this->config);

        $this->expectException(AdCompositionException::class);
        $validator->assertSafe(
            [AdBlock::source('College Graduate (4 year course)%&Any IT Technical Course', 'jspec_education')],
            $facts
        );
    }

    /* -------------------------- excluded fields ------------------------ */

    public function test_age_sex_and_assessment_data_never_reach_the_ad(): void
    {
        $ad = $this->compose('Inventory Clerk', [
            'jspec_emplstat' => 'Regular',
            'jspec_education' => 'Vocational Course Graduate',
            'jspec_sex' => 'Male',
            'jspec_agerange' => '12-99',
            'jspec_headsnum' => '3',
            'jspec_career' => 'Technical/Functional Competence' . self::DELIM . 'Autonomy/Independence',
            'jspec_personality' => 'Analyst',
            'jspec_tapt' => 'Extrovert' . self::DELIM . 'Sensitive',
        ]);

        foreach (['Male', '12-99', 'Analyst', 'Extrovert', 'Autonomy'] as $secret) {
            $this->assertStringNotContainsString($secret, $ad);
        }
    }

    /* ------------------------- employment wording ---------------------- */

    public function test_regularization_is_promised_only_for_probationary(): void
    {
        $regular = $this->compose('Staff A', ['jspec_emplstat' => 'Regular']);
        $probationary = $this->compose('Staff A', ['jspec_emplstat' => 'Probationary']);
        $unknown = $this->compose('Staff A', ['jspec_emplstat' => 'Seasonal Helper']);

        $this->assertStringContainsString('Regular employment', $regular);
        $this->assertStringContainsString('regularization', $probationary);
        $this->assertStringNotContainsString('regularization', $regular);
        $this->assertStringNotContainsString('regularization', $unknown);
        $this->assertStringNotContainsString('💼', $unknown);
    }

    public function test_every_hireflow_employment_option_resolves(): void
    {
        $options = [
            'Apprentice - Sales', 'Contractual', 'Full-Time Faculty', 'Part-Time',
            'Part-Time Faculty', 'PartTime-FullLoad', 'Probationary',
            'Probationary FullLoad', 'Regular',
        ];

        foreach ($options as $option) {
            $this->assertStringContainsString(
                '💼',
                $this->compose('Staff A', ['jspec_emplstat' => $option]),
                "HireFlow option '{$option}' produced no employment line"
            );
        }
    }

    /* --------------------------- the validator ------------------------- */

    public function test_validator_rejects_a_source_line_not_present_in_the_spec(): void
    {
        $extractor = new SpecFactExtractor($this->config);
        $facts = $extractor->extract(
            $this->spec(['jspec_duties' => 'Counts stock weekly']),
            'Staff A'
        );

        $validator = new AdSafetyValidator($this->config);

        $this->expectException(AdCompositionException::class);
        $validator->assertSafe(
            [AdBlock::source('Manages the entire warehouse team', 'jspec_duties')],
            $facts
        );
    }

    public function test_validator_rejects_unapproved_company_copy(): void
    {
        $extractor = new SpecFactExtractor($this->config);
        $facts = $extractor->extract($this->spec([]), 'Staff A');

        $validator = new AdSafetyValidator($this->config);

        $this->expectException(AdCompositionException::class);
        $validator->assertSafe([AdBlock::controlled('Free housing provided!')], $facts);
    }

    public function test_composition_is_deterministic(): void
    {
        $args = ['Staff A', [
            'jspec_emplstat' => 'Regular',
            'jspec_duties' => "Counts stock weekly\nReceives deliveries",
            'jspec_computerskill' => 'Proficient in MS Office (Word, Excel)',
        ]];

        $this->assertSame($this->compose(...$args), $this->compose(...$args));
    }

    /* ------------------------------- tiers ----------------------------- */

    public function test_tier_reflects_how_much_content_exists(): void
    {
        $extractor = new SpecFactExtractor($this->config);

        $sparse = new AdContentProfile(
            $extractor->extract($this->spec(['jspec_education' => 'High School Graduate']), 'A'),
            $this->config
        );
        $rich = new AdContentProfile(
            $extractor->extract($this->spec([
                'jspec_duties' => 'Counts stock',
                'jspec_techcompetencies' => 'Inventory monitoring',
                'jspec_competencies' => 'Attention to detail',
            ]), 'A'),
            $this->config
        );

        $this->assertSame(AdContentProfile::TIER_SPARSE, $sparse->tier());
        $this->assertSame(AdContentProfile::TIER_RICH, $rich->tier());
    }
}
