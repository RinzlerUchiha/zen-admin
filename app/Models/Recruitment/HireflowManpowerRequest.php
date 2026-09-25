<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only pointer to HireFlow's tbl_manpower_request (portal_db, the
 * default connection). The recruitment module never writes to this table — HireFlow (manpower/)
 * is the system of record for requests/approvals.
 */
class HireflowManpowerRequest extends Model
{
    use HasFactory;

    protected $table = 'tbl_manpower_request';
    protected $guarded = [];
    public $timestamps = false; // created_at handled by HireFlow's own schema

    public function positions()
    {
        return $this->hasMany(HireflowManpowerPosition::class, 'request_id', 'id');
    }

    /**
     * Every edit/cancel ask ever made against this request, newest first.
     */
    public function changeRequests()
    {
        return $this->hasMany(HireflowManpowerChangeRequest::class, 'request_id', 'id')
            ->orderByDesc('id');
    }

    /**
     * The one open ask, if there is one. HireFlow allows only a single
     * Pending change request per manpower request, so this is at most one row.
     */
    public function pendingChange()
    {
        return $this->hasOne(HireflowManpowerChangeRequest::class, 'request_id', 'id')
            ->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }
}