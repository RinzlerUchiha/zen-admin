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
}