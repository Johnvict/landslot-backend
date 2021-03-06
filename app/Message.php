<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    //
    protected $fillable = [
        'user_id',
        'message',
        'from',
        'unread'
    ];

    protected $hidden = [
        'updated_at',
    ];

}
