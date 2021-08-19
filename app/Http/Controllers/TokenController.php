<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\UtilityController;
use App\User;

class TokenController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt, UtilityController $utility) {
        $this->jwt = $jwt;
        $this->utility = $utility;
    }

    public function respondWithToken($token) {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL()
        ];
    }

    public function requestToken (Request $request) {
        $response = [];
        $status = '';
        $data = array(
            'email'            =>  $request->email,
            'password'         =>  $request->password
        );
        $validator = \Validator::make($data, [
            'email'          => 'required|string',
            'password'       => 'required|string'
        ]);
        if($validator->fails()) {
            $status = '05';
        } else {
            $user = Auth::attempt($data);
            if(!$user) {
                $status = '03';
            } else {
                $response = $this->respondWithToken($user);
                $user = User::where('email', $data['email'])->first(); // get the user profile
                $response['user_profile'] = array(
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'phone'          => $user->phone,
                    'flat_number'    => $user->flat_number,
                    'is_admin'       => $user->is_admin,
                    'is_active'      => $user->is_active,
                    'username'       => $user->username,
                    'street'        => $user->street,
                    'city'           => $user->city,
                    'state'          => $user->state
                );
                $status = '00';
            }
        }
        return $this->utility->response($status, 'token', $response);
    }

}
