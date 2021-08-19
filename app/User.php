<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

// Password Hasher
use Illuminate\Support\Facades\Hash;

class User extends Model implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_admin',
        'is_active',
        'flat_number',
        'username',
        'street',
        'city',
        'state',
        'access_token'
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'email_verified_at',
        'remember_token',
        'access_token'
    ];

    protected $with = [
        'workorders',
        'getUnreadMessages'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function workorders () {
        return $this->hasMany('App\Workorder', 'user_id');
    }

    public function notifications () {
        return $this->hasMany('App\Notification', 'user_id');
    }

    public function hashPassword ($password) {
        return Hash::make($password);
    }

    public function getMessages () {
        return $this->hasMany('App\Message');
    }

    public function getUnreadMessages (){
        return $this->hasMany('App\Message')->where('unread', 1);
    }

}