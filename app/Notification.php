<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $fillable = [
        'title', 'description', 'user_id' 
    ];

    protected $hidden = [
        'user_id'. 'created_at', 'updated_at' 
    ];

}
