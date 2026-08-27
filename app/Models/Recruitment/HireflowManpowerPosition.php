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
}