<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Category;

class Workorder extends Model
{
    //
    protected $fillable = [
        'title', 'description', 'user_id', 'categories_id', 'priority', 'progress', 'status', 'feedback', 'transaction_ref', 'amount', 'payment_status'
    ];

    protected $hidden = [
        'updated_at', 'id', 'user_id', 'categories_id'
    ];

    public function getCategory ($cat) {
        return Category::findorfail($cat)->title;
    }
}
