<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only pointer to HireFlow's tbl_manpower_request_position (hrd2 connection).
 */
class HireflowManpowerPosition extends Model
{
    use HasFactory;

    protected $connection = 'hrd2';
    protected $table = 'tbl_manpower_request_position';
    protected $guarded = [];
    public $timestamps = false;

    public function request()
    {
        return $this->belongsTo(HireflowManpowerRequest::class, 'request_id', 'id');
    }

    /**
     * Job spec is looked up via query builder (DB::connection('hrd2')->table(...))
     * rather than a dedicated Eloquent model, per your confirmed preference —
     * mirrors how HireFlow itself queries MP_JOBSPEC_TABLE.
     */
    public function jobSpec()
    {
        return \Illuminate\Support\Facades\DB::connection('hrd2')
            ->table('tbl_manpower_jobspec')
            ->where('jspec_id', $this->jobspec_id)
            ->first();
    }
    /**
     * Resolves this position's short code (e.g. "CT") to its full title
     * (e.g. "Computer Technician") via HireFlow's tbl_jobdescription lookup.
     */
    public function positionTitle()
    {
        return \Illuminate\Support\Facades\DB::connection('hrd2')
            ->table('tbl_jobdescription')
            ->where('jd_code', $this->position)
            ->value('jd_title') ?? $this->position;
    }

    /**
     * Postings already created for this position (recruitment module's own table)
     */
    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'request_position_id', 'id');
    }

    /**
     * Composes a draft job posting description from this position's linked
     * Job Specification, plus this specific request's headcount. Excludes
     * internal assessment/personality fields (MPA-G, TAPT, Enneagram, Raven,
     * leadership, career, motivation) — those belong to the later
     * screening/assessment phase, not a public job ad.
     */
    public function draftPostingDescription(): string
    {
        $spec = $this->jobSpec();
        if (!$spec) {
            return '';
        }

        $sections = [];

        $requirements = [];
        if (!empty($spec->jspec_emplstat)) {
            $requirements[] = "Employment Status: " . $spec->jspec_emplstat;
        }
        if (!empty($spec->jspec_agerange)) {
            $requirements[] = "Age Range: " . $spec->jspec_agerange;
        }
        if (!empty($spec->jspec_sex)) {
            $requirements[] = "Sex: " . $spec->jspec_sex;
        }
        $requirements[] = "Headcount: " . $this->headcount;
        $sections[] = "REQUIREMENTS\n" . implode("\n", $requirements);

        if (!empty($spec->jspec_duties)) {
            $sections[] = "DUTIES & RESPONSIBILITIES\n" . trim($spec->jspec_duties);
        }
        if (!empty($spec->jspec_education)) {
            $sections[] = "EDUCATION\n" . trim($spec->jspec_education);
        }
        if (!empty($spec->jspec_workexp)) {
            $sections[] = "WORK EXPERIENCE\n" . trim($spec->jspec_workexp);
        }
        if (!empty($spec->jspec_techcompetencies)) {
            $sections[] = "TECHNICAL COMPETENCIES\n" . trim($spec->jspec_techcompetencies);
        }
        if (!empty($spec->jspec_competencies)) {
            $sections[] = "COMPETENCIES\n" . trim($spec->jspec_competencies);
        }
        if (!empty($spec->jspec_computerskill)) {
            $sections[] = "COMPUTER SKILLS\n" . trim($spec->jspec_computerskill);
        }
        if (!empty($spec->jspec_otherskill)) {
            $sections[] = "OTHER SKILLS\n" . trim($spec->jspec_otherskill);
        }

        return implode("\n\n", $sections);
    }

    /* ===================================================================
     * Public job ad composition
     *
     * draftPostingDescription() above is the INTERNAL jobspec dump.
     * draftPublicAd() below is the PUBLIC ad applicants read on the careers
     * page. Composition itself lives in App\Services\Recruitment\Ad —
     * this class keeps only the approved company copy, because that copy is
     * reviewed as policy rather than configuration.
     *
     * Never published: age range, sex, headcount, and every assessment field
     * (Meta Program, TAPT, Enneagram, learning style, career anchors,
     * motivation, personality, Raven, leadership). Age and sex must not appear
     * in a job advertisement (RA 10911); the rest are screening criteria.
     * =================================================================== */

    /** Shown under the job title. */
    public const AD_LOCATION = 'For our outlets within Mindanao areas';

    /**
     * Standing benefit lines, in the approved order. The ':growth' slot is
     * filled from AD_EMPLOYMENT_COPY according to the position's actual
     * jspec_emplstat — every other line is status-independent.
     */
    public const AD_PERKS = [
        'TRAINING PROVIDED',
        'May government benefits',
        ':growth',
        'Open for hardworking and willing matuto',
    ];

    /**
     * Employment-status wording.
     *
     *   label → the 💼 line under the job title
     *   perk  → fills the ':growth' slot in AD_PERKS
     *
     * Keys cover every option HireFlow's Job Spec form actually offers
     * (MP_JOBSPEC_OPTIONS['emplstat'] in manpower_jobspec_config.php), plus a
     * few legacy values. Regularization is promised for Probationary only,
     * because that is the one status where it is the defined next step.
     */
    public const AD_EMPLOYMENT_COPY = [
        'regular' => [
            'label' => 'Regular employment',
            'perk'  => 'May chance for growth and regular employment',
        ],
        'probationary' => [
            'label' => 'Probationary employment',
            'perk'  => 'May chance for growth at regularization',
        ],
        'contractual' => [
            'label' => 'Contractual employment',
            'perk'  => 'May chance for growth',
        ],
        'apprentice' => [
            'label' => 'Apprenticeship program',
            'perk'  => 'May chance for growth',
        ],
        'faculty_full' => [
            'label' => 'Full-time faculty position',
            'perk'  => 'May chance for growth',
        ],
        'faculty_part' => [
            'label' => 'Part-time faculty position',
            'perk'  => 'May chance for growth',
        ],
        'part_time' => [
            'label' => 'Part-time employment',
            'perk'  => 'May chance for growth',
        ],
        'part_time_fullload' => [
            'label' => 'Part-time employment (full load)',
            'perk'  => 'May chance for growth',
        ],
        'project' => [
            'label' => 'Project-based employment',
            'perk'  => 'May chance for growth',
        ],
        'casual' => [
            'label' => 'Casual employment',
            'perk'  => 'May chance for growth',
        ],
        'trainee' => [
            'label' => 'Trainee / OJT',
            'perk'  => 'May chance for growth',
        ],
    ];

    /**
     * Used when jspec_emplstat is empty or not one of the approved values.
     * No status line is shown and no employment terms are claimed.
     */
    public const AD_EMPLOYMENT_NEUTRAL = [
        'label' => null,
        'perk'  => 'May chance for growth',
    ];

    /**
     * Exact matches for HireFlow's nine Job Spec options, checked before the
     * substring fallback so "Part-Time Faculty" cannot be read as "Part-Time".
     */
    private const AD_EMPLOYMENT_EXACT = [
        'regular'               => 'regular',
        'probationary'          => 'probationary',
        'probationary fullload' => 'probationary',
        'contractual'           => 'contractual',
        'apprentice - sales'    => 'apprentice',
        'full-time faculty'     => 'faculty_full',
        'part-time faculty'     => 'faculty_part',
        'part-time'             => 'part_time',
        'parttime-fullload'     => 'part_time_fullload',
    ];

    /**
     * Composes the public-facing ad from this position's Job Specification.
     * Editable draft copy — HR reviews it in the Job Postings panel before it
     * goes live, and an edited ad is protected by JobPosting::$ad_is_custom.
     */
    public function draftPublicAd(): string
    {
        return (new \App\Services\Recruitment\Ad\JobAdComposer())->composeFor($this);
    }

    /**
     * Resolves a raw jspec_emplstat value to approved employment wording.
     * Exact match first, then a conservative substring pass; anything
     * unrecognised returns AD_EMPLOYMENT_NEUTRAL rather than guessing, so an
     * unexpected status can never produce a false employment promise.
     */
    public static function employmentCopyFor(?string $raw): array
    {
        $status = mb_strtolower(trim((string) $raw));

        if ($status === '') {
            return self::AD_EMPLOYMENT_NEUTRAL;
        }

        if (isset(self::AD_EMPLOYMENT_EXACT[$status])) {
            return self::AD_EMPLOYMENT_COPY[self::AD_EMPLOYMENT_EXACT[$status]];
        }

        // Order matters — most specific first.
        $tokens = [
            'faculty'    => null,          // resolved below by full/part
            'probation'  => 'probationary',
            'apprentice' => 'apprentice',
            'trainee'    => 'trainee',
            'ojt'        => 'trainee',
            'fullload'   => 'part_time_fullload',
            'part'       => 'part_time',
            'casual'     => 'casual',
            'project'    => 'project',
            'contract'   => 'contractual',
            'regular'    => 'regular',
        ];

        if (str_contains($status, 'faculty')) {
            return self::AD_EMPLOYMENT_COPY[
                str_contains($status, 'part') ? 'faculty_part' : 'faculty_full'
            ];
        }

        foreach ($tokens as $needle => $key) {
            if ($key !== null && str_contains($status, $needle)) {
                return self::AD_EMPLOYMENT_COPY[$key] ?? self::AD_EMPLOYMENT_NEUTRAL;
            }
        }

        return self::AD_EMPLOYMENT_NEUTRAL;
    }
}