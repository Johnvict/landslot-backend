<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\UtilityController;

use App\Category;


class GeneralController extends Controller
{
    public function __construct(UtilityController $utility) {
        $this->utility = $utility;
    }

    public function getCategory () {
        $status = '00';
        $response = Category::orderBy('title', 'asc')->get(); 
        return $this->utility->response($status, 'workorder', $response);
    }
    
}
