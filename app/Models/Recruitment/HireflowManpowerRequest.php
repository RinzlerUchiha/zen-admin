<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only pointer to HireFlow's tbl_manpower_request (hrd2 connection).
 * The recruitment module never writes to this table — HireFlow (manpower/)
 * is the system of record for requests/approvals.
 */
class HireflowManpowerRequest extends Model
{
    use HasFactory;

    protected $connection = 'hrd2';
    protected $table = 'tbl_manpower_request';
    protected $guarded = [];
    public $timestamps = false; // created_at handled by HireFlow's own schema

    public function positions()
    {
        return $this->hasMany(HireflowManpowerPosition::class, 'request_id', 'id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }
}