<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\UtilityController;
use App\Http\Controllers\TokenController;

// include all necessary modls
use App\User;
use App\Message;
use App\Category;
use App\Workorder;
use App\Notification;

class UserController extends Controller
{
    protected $jwt;

    public function __construct(JWTAuth $jwt, UtilityController $utility, TokenController $token) 
    {
        $this->jwt = $jwt;
        $this->utility = $utility;
        $this->token = $token;
    }

    public function createAccount (Request $request) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'name'      =>  $request->name,
            'email'     =>  $request->email,
            'password'  =>  Hash::make($request->password),
            'phone'     =>  $request->phone,
            'is_admin'  =>  $request->role == null ? '0' : '1'
        );
        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'name'      =>  'required|string|max:191',
            'email'     =>  'required|string|email|max:191|unique:users',
            'phone'     =>  'required|numeric|digits:11',
            'password'  =>  'required|string|min:6'
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            User::create($data);
            $status = '02';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function updateAccount (Request $request) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'name'          =>  $request->name,
            'flat_number'   =>  $request->flat_number,
            'username'      =>  $request->username,
            'phone'         =>  $request->phone,
            'street'        =>  $request->street,
            'city'          =>  $request->city,
            'state'         =>  $request->state
        );

        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'name'          =>  'string|max:191',
            'flat_number'   =>  'string|max:191',
            'phone'         =>  'numeric|digits:11',
            'username'      =>  'string|max:191',
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            if ($request->user()->is_admin == 1) {
                $status = '04';
            } else {
                $update_profile = User::find($request->user()->id);
                $update_profile->update($data);
                $status = '00';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function createWorkorder (Request $request) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'title'         =>  $request->title,
            'categories_id' =>  $request->categories_id,
            'priority'      =>  $request->priority,
            'description'   =>  $request->description,
        );
        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'title'         =>  'required|string|max:191',
            'categories_id'  =>  'required|string',
            'priority'      =>  'required|string',
            'description'   =>  'required|string',
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            if ($request->user()->is_admin == 1) {
                $status = '04';
            } else {
                $data['user_id'] = $request->user()->id;
                Workorder::create($data);
                $status = '02';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function userList (Request $request)
    {
        $response = [];
        $status = '';
        $field_name = '';
        if($request->user()->is_admin) {
            $result = User::where('is_admin', 0)->orderBy('created_at', 'desc')->get();
            if (count($result) <= 0) {
                $status = '06';
            } else {
                foreach ($result as $key => $value) {
                    $data = (object) [
                        'id'            => $value->id,
                        'name'          => $value->name,
                        'email'         => $value->email,
                        'is_active'     => $value->is_active,
                        'username'      => $value->username,
                        'flat_number'   => $value->flat_number,
                        'phone'         => $value->phone,
                        'street'        => $value->street,
                        'city'          => $value->city,
                        'state'         => $value->state,
                        'date_joined'   => $value->created_at->diffForHumans()
                    ];
                    array_push($response, $data);
                }
                $status = '00';
                $field_name = 'items';
            }
        } else {
            $status = '04';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function messagesList (Request $request, $id)
    {
        $response = [];
        $status = '';
        $field_name = '';
        $result = Message::where('user_id', $id)->orderBy('created_at', 'desc')->get();
        if (count($result) <= 0) {
            $status = '06';
        } else {
            foreach ($result as $key => $value) {
                $data = (object) [
                    'id'            => $value->id,
                    'message'       => $value->message,
                    'from'          => $value->from,
                    'created_at'    => $value->created_at->diffForHumans()
                ];
                array_push($response, $data);
            }
            $status = '00';
            $field_name = 'items';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function createMessages (Request $request, $id) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'from'          =>  $request->from,
            'message'       =>  $request->message
        );
        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'from'          =>  'required|string|max:191',
            'message'       =>  'required|string'
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            $data['user_id'] = $id;
            $result = Message::create($data);
            $field_name = 'items';
            $response = [
                'id'            => $result->id,
                'from'          => $result->from,
                'message'       => $result->message,
                'created_at'    => $result->created_at->diffForHumans()
            ];
            $status = '02';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function retrieveWorkorder (Request $request)
    {
        $response = [];
        $status = '';
        $field_name = '';
        if($request->user()->is_admin) {
            $result = Workorder::orderBy('created_at', 'desc')->get();
            if (count($result) <= 0) {
                $status = '06';
            } else {
                foreach ($result as $key => $value) {
                    $data = (object) [
                        'id'            => $value->id,
                        'name'          => User::findorfail($value->user_id)->name,
                        'category'      => Category::findorfail($value->categories_id)->title,
                        'description'   => $value->description,
                        'priority'      => $value->priority,
                        'progress'      => $value->progress,
                        'amount'        => $value->amount,
                        'payment_status'=> $value->payment_status,
                        'status'        => $value->status,
                        'created_at'    => $value->created_at->diffForHumans()
                    ];
                    array_push($response, $data);
                }
                $status = '00';
                $field_name = 'items';
            }
        } else {
            $user = $request->user()->id;
            $result = Workorder::where('user_id', $user)->orderBy('created_at', 'desc')->get();
            if (count($result) <= 0) {
                $status = '06';
            } else {
                foreach ($result as $key => $value) {
                    $data = (object) [
                        'id'            => $value->id,
                        'name'          => User::findorfail($value->user_id)->name,
                        'category'      => Category::findorfail($value->categories_id)->title,
                        'priority'      => $value->priority,
                        'description'   => $value->description,
                        'progress'      => $value->progress,
                        'amount'        => $value->amount,
                        'payment_status'=> $value->payment_status,
                        'status'        => $value->status,
                        'created_at'    => $value->created_at->diffForHumans()
                    ];
                    array_push($response, $data);
                }
                $status = '00';
                $field_name = 'items';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function getWorkorder (Request $request, $id)
    {
        $response = [];
        $status = '';
        $field_name = '';
        if($request->user()->is_admin) {
            $result = Workorder::find($id);
            if (count((array)$result) <= 0) {
                $status = '06';
            } else {
                $response = [
                    'id'            => $result->id,
                    'name'          => User::findorfail($result->user_id)->name,
                    'phone'         => User::findorfail($result->user_id)->phone,
                    'title'         => $result->title,
                    'category'      => Category::findorfail($result->categories_id)->title,
                    'description'   => $result->description,
                    'feedback'      => $result->feedback,
                    'priority'      => $result->priority,
                    'progress'      => $result->progress,
                    'amount'        => $result->amount,
                    'payment_status'=> $result->payment_status,
                    'status'        => $result->status,
                    'created_at'    => $result->created_at->diffForHumans()
                ];
                $status = '00';
                $field_name = 'items';
            }
        } else {
            $user = $request->user()->id;
            $result = Workorder::where(['user_id' => $user, 'id' => $id])->first();
            if (count((array)$result) <= 0) {
                $status = '06';
            } else {
                $response = [
                    'id'            => $result->id,
                    'name'          => User::findorfail($result->user_id)->name,
                    'phone'         => User::findorfail($result->user_id)->phone,
                    'title'         => $result->title,
                    'category'      => Category::findorfail($result->categories_id)->title,
                    'description'   => $result->description,
                    'feedback'      => $result->feedback,
                    'priority'      => $result->priority,
                    'progress'      => $result->progress,
                    'amount'        => $result->amount,
                    'payment_status'=> $result->payment_status,
                    'status'        => $result->status,
                    'created_at'    => $result->created_at->diffForHumans()
                ];
                $status = '00';
                $field_name = 'items';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function updateWorkorder (Request $request, $id) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $result = Workorder::find($id);
        if (count((array)$result) <= 0) {
            $status = '06';
        } else {
            $data = array(
                'priority'      =>  $request->priority,
                'title'         =>  $request->title,
                'description'   =>  $request->description,
                'feedback'      =>  $request->feedback,
                'categories_id' =>  $request->categories_id
            );
            // hash the user vendor_id as security pass
            $validator = \Validator::make($data, [
                'priority'      =>  'string|max:191',
                'title'         =>  'string|max:191',
                'description'   =>  'string',
                'categories_id' =>  'string|max:191'
            ]);
            if($validator->fails()) {
                $status = '05';
                $field_name = 'errors';
                $response = $validator->errors();
            } else {
                if ($request->user()->is_active == "1" && $request->user()->is_admin == "1") {
                    $data['progress'] = $request->progress !== null ? $request->progress : $result->progress;
                    $data['amount'] = $request->amount !== null ? $request->amount : $result->amount;
                    $data['transaction_ref'] = $request->transaction_ref !== null ? $request->transaction_ref : $result->transaction_ref;
                    $data['payment_status'] = $request->payment_status !== null ? $request->payment_status : $result->payment_status;
                    $data['status'] = $request->status !== null ? $request->status : $result->status;
                }
                // $update_profile = User::find($request->user()->id);
                $result->update($data);
                $status = '00';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function deleteWorkorder (Request $request, $id) // delete notifications
    { 
        $response = [];
        $status = '';
        $field_name = '';
        if ($request->user()->is_admin !== 1 || $request->user()->is_active !== 1) {
            $status = '04';
        } else {
            $result = Workorder::find($id);
            if (count((array)$result) <= 0) {
                $status = '06';
            } else {
                $result->delete();
                $status = '07';
                $field_name = 'items';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function getNotification (Request $request) 
    { // 
        $response = [];
        $status = '';
        $field_name = '';
        $result = Notification::orderBy('created_at', 'desc')->get();
        if (count($result) <= 0) {
            $status = '06';
        } else {
            foreach ($result as $key => $value) {
                $data = (object) [
                    'id'            => $value->id,
                    'title'         => $value->title,
                    'description'   => $value->description,
                    'author'        => User::findorfail($value->user_id)->name,
                    'created_at'    => $value->created_at->diffForHumans()
                ];
                array_push($response, $data);
            }
            $status = '00';
            $field_name = 'items';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function createNotification (Request $request) 
    { // generate unique bank account
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'title'         =>  $request->title,
            'description'   =>  $request->description
        );
        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'title'         =>  'required|string|max:191',
            'description'   =>  'required|string',
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            if ($request->user()->is_admin == 1 && $request->user()->is_active == 1) {
                $data['user_id'] = $request->user()->id;
                Notification::create($data);
                $status = '02';
            } else {
                $status = '04';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function deleteNotification (Request $request, $id) 
    { // delete notifications
        $response = [];
        $status = '';
        $field_name = '';
        if ($request->user()->is_admin !== 1 || $request->user()->is_active !== 1) {
            $status = '04';
        } else {
            $result = Notification::find($id);
            if (count((array)$result) <= 0) {
                $status = '06';
            } else {
                $result->delete();
                $status = '07';
                $field_name = 'items';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    // change password for user and admin
    public function changePassword (Request $request) // delete notifications
    {
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'cur_password'          =>  $request->cur_password,
            'new_password'          =>  $request->new_password,
            'password_confirmation' =>  $request->password_confirmation
        );
        // fetch current user details
        $getUser = User::find($request->user()->id);
        // confirm if old password is correct
        $confirm_old_password = Hash::check($request->cur_password, $getUser->password);
        if($confirm_old_password) { // hashed password confirm the plain text (current password) 
            if($data['new_password'] !== $data['password_confirmation']) {
                $status = '05';
                $field_name = 'errors';
                $response = array(
                    'password' => 'Confirm password doesn\'t match'
                );            
            } else {
                // hash new password
                $hashPassword = $request->user()->hashPassword($request->new_password);
                $getUser->update(['password' => $hashPassword]);
                $response = $this->token->refreshToken($getUser->email, $request->new_password);
                $field_name = 'token';
                $status = '00';
            }
        } else {
            $status = '05';
            $field_name = 'errors';
            $response = array(
                'password' => 'Current password doesn\'t match'
            ); 
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function forgotPassword (Request $request)
    {
        $response = [];
        $status = '';
        $field_name = '';
        $access_token = 0;
        for ($i = 0; $i < 5; $i++) 
        {
            $access_token .= mt_rand(0,9);
        }
        $user = User::where('email', $request->email)->first();
        if ($request->user()->email !== $request->email) {
            $status = '04';
        } else {
            if (count((array)$user) <= 0) {
                $status = '06';
            } else {
                $data['user'] = $user;
                $data['access_token'] = $access_token;
                try {
                    Mail::to($user->email)->send(new \App\Mail\ForgotPassword($data));
                    $user->update(['access_token' => $data['access_token']]);
                    $status = '00';
                    $field_name = 'items';
                } catch (\Throwable $th) {
                    \Log::info($th);
                    $status = '10';
                }
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function paymentInvoice (Request $request, $id) // delete notifications
    { 
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'transaction_ref'   =>  $request->transaction_ref
        );
        // hash the user vendor_id as security pass
        $validator = \Validator::make($data, [
            'transaction_ref'   =>  'required|string|max:191'
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            if ($request->user()->is_admin !== 1 || $request->user()->is_active !== 1) {
                $result = Workorder::find($id);
                if (count((array)$result) <= 0) {
                    $status = '06';
                } else {
                    $result->update($data);
                    $verified = $this->utility->verifyPayment($data['transaction_ref'], $result->amount);
                    if ($verified == 100) {
                        // update the transaction to paid
                        $result->update(['payment_status' => 'paid']);
                        $response = [
                            'id'            => $result->id,
                            'name'          => $request->user()->name,
                            'phone'         => $request->user()->phone,
                            'title'         => $result->title,
                            'category'      => Category::findorfail($result->categories_id)->title,
                            'description'   => $result->description,
                            'feedback'      => $result->feedback,
                            'priority'      => $result->priority,
                            'progress'      => $result->progress,
                            'amount'        => $result->amount,
                            'payment_status'=> $result->payment_status,
                            'status'        => $result->status,
                            'created_at'    => $result->created_at->diffForHumans()
                        ];
                        $field_name = 'items';
                        $status = '00';
                    } elseif ($verified == 419) {
                        // amount paid doesnot match the amount to pay
                        $status = '08';
                    } elseif ($verified == 404) {
                        // transaction not found
                        $status = '09';
                    } else {
                        $status = '10';
                    }
                }
            } else {
                $status = '04';
            }
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function recentMessages (Request $request)
    {
        $response = [];
        $status = '';
        $field_name = '';
        if ($request->user()->is_admin !== 1 || $request->user()->is_active !== 1) {
            $status = '04';
        } else {
            $users = User::all();
            if (count($users) <= 0) {
                return response()->json([
                    'status'    =>  200,
                    'message'   =>  'No available user',
                    'items'     =>  []
                ]);
            } else {
                foreach ($users as $key => $value) {
                    if (count($value['getUnreadMessages']) > 0) {
                        array_push($response, $value);
                    }
                }
                $status = '00';
                $field_name = 'items';
            }
        }
        if (count($response) === 0) {
            return response()->json([
                'status'    =>  200,
                'message'   =>  'successful',
                'items'     =>  []
            ]);
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function updateReadMessages (Request $request) {
        $response = [];
        $status = '';
        $field_name = '';
        $data = array(
            'payload'   =>  $request->payload
        );
        $validator = \Validator::make($data, [
            'payload'   =>  'required|array'
        ]);
        if($validator->fails()) {
            $status = '05';
            $field_name = 'errors';
            $response = $validator->errors();
        } else {
            foreach ($data['payload'] as $key => $value) {
                $result = Message::find($value);
                if ($result) {
                    $result->update(['unread' => 0]);
                }
            }
            $status = '00';
            $field_name = 'items';
        }
        return $this->utility->response($status, $field_name, $response);
    }

    public function refreshToken (Request $request) {
        $token = JWTAuth::getToken();
        if(!$token){
            return response()->json([
                'status'    =>  401,
                'message'   =>  'Token not provided'
            ]);
        }
        if ($token = JWTAuth::refresh($token)) {   
            return response()->json([
                'status'    =>  200,
                'token'     =>  $token
            ]);
        } else {
            return response()->json([
                'status'    => 401,
                'message'   => "Token has been blacklisted, kindly login"
            ]);
        }
    }

}
