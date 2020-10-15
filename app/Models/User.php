<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\ModelIterface;

class User extends Authenticatable implements ModelIterface
{
    protected $fillable = [
        'id',
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'phone_office',
        'role',
        'mail',
        'login',
        'password',
        'is_work',
        'out_calls',
        'ip',
        'speaker_status',
        'last_online',
        'organization_id',
        'company_id',
        'pseudo_session',
        'is_show_work',
        'mainlink',
        'telegram',
        'plates'
    ];

    const ROLE_USER     = 'user';

    const ROLE_ADMIN    = 'admin';

    protected $permission;

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function company()
    {
        return $this->belongsTo(Organization::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function atsUsers()
    {
        return $this->hasMany(AtsUser::class)->with('atsGroup', 'sipCallerIds')->orderBy('id', 'ASC');
    }

    public function calls()
    {
        return $this->hasMany(Call::class);
    }

    public function history(){
        return $this->morphMany(History::class, 'users', 'reference_table', 'reference_id', 'id')->with('users')->orderBy('id', 'DESC');
    }

    public function images(){
        return $this->hasMany(UserImage::class, 'user_id');
    }

}
