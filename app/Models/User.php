<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // protected $connection = 'hrd2';
    protected $table = 'tbl_user2';
    protected $primaryKey = 'U_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'name',
        // 'email',
        // 'password',

        'U_Name',
        'U_Password',
        'U_Password_hashed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // 'password',
        'U_Password',
        'remember_token',
        'U_Password_hashed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
        // 'password' => 'hashed',
        'U_Password_hashed' => 'hashed',
    ];

    /**
     * Override the username field for authentication.
     */
    // public function username()
    // {
    //     return 'U_Name';
    // }

    public function getAuthPassword()
    {
        // return $this->U_Password;
        return $this->U_Password_hashed;
    }

    /**
     * Mutator to hash passwords when setting them.
     */
    // public function setUPasswordAttribute($value)
    // {
    //     $this->attributes['U_Password'] = Hash::make($value);
    // }

    public function setUPasswordHashedAttribute($value)
    {
        // Avoid double-hashing already-hashed strings
        $this->attributes['U_Password_hashed'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }


    // public function getAuthIdentifierName()
    // {
    //     return 'U_ID';
    // }

    public function userAccess($mod, $indv, $system = 'HRIS')
    {
        // return true;
        $query = DB::connection('hrd2')->table('tbl_sysassign')
        ->where([
            ['assign_empno', '=', $this->Emp_No],
            ['system_id', '=', $system],
            ['assign_mod', '=', $mod]
        ]);
        if($indv){
            $query->where('assign_indv', $indv);
        }
        return $query->exists();
    }

    public function getLastFirstNameAttribute()
    {
        return DB::table('tbl201_persinfo')
        ->where('pers_empno', $this->Emp_No)
        ->selectRaw("TRIM(CONCAT(pers_lastname, ', ', pers_firstname)) AS lfname")
        ->first()->lfname ?? '';
    }

    public function getFirstLastNameAttribute()
    {
        return DB::table('tbl201_persinfo')
        ->where('pers_empno', $this->Emp_No)
        ->selectRaw("TRIM(CONCAT(pers_firstname, ' ', pers_lastname)) AS flname")
        ->first()->flname ?? '';
    }

    public function getJobPositionAttribute()
    {
        return DB::table('tbl201_jobrec as a')
        ->leftJoin('tbl_jobdescription as b', 'jd_code', '=', 'jrec_position')
        ->where('jrec_empno', $this->Emp_No)
        ->where('jrec_status', 'Primary')
        // ->select('jrec_position', 'jd_title')
        ->orderByDesc('jrec_effectdate')
        ->first();
    }

    // public function getRememberTokenName()
    // {
    //     return 'custom_remember_token'; // Your custom column name
    // }

    // public function getRememberToken()
    // {
    //     return $this->custom_remember_token;
    // }

    // public function setRememberToken($value)
    // {
    //     $this->custom_remember_token = $value;
    // }

}
