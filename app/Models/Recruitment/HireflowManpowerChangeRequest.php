<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only pointer to HireFlow's tbl_manpower_change_request (portal_db, the
 * default connection).
 *
 * A Requestor whose request is already Approved asks their Approver for
 * permission to edit or cancel it; that ask lives here. zen-admin only ever
 * reads these rows — approving or declining stays in HireFlow (manpower/),
 * which is the system of record for the decision and its side effects.
 */
class HireflowManpowerChangeRequest extends Model
{
    use HasFactory;

    protected $table = 'tbl_manpower_change_request';
    protected $guarded = [];
    public $timestamps = false; // created_at handled by HireFlow's own schema

    public function request()
    {
        return $this->belongsTo(HireflowManpowerRequest::class, 'request_id', 'id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * "Edit requested" / "Cancel requested" — what HR sees on the card.
     */
    public function shortLabel(): string
    {
        return $this->change_type === 'cancel' ? 'Cancel requested' : 'Edit requested';
    }
}
