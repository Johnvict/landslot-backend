<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    // return $router->app->version();
    return $environment = app()->environment();
});
// landslot api calls
$router->group(['prefix' => 'api/v1'], function ($router) {

    // all live calls
    $router->post('auth/token', 'TokenController@requestToken');
    $router->put('auth/refresh', 'UserController@refreshToken');
    $router->get('category', 'GeneralController@getCategory');

    // create account routes
    $router->post('auth/create', 'UserController@createAccount');
    // change password
    $router->post('forgot_password', 'UserController@forgotPassword');
    
    // added authentication middleware
    $router->group(['middleware' => 'auth'], function() use ($router) {
        // notification routes
        $router->group(['prefix' => 'notification'], function ($router) {
            $router->get('/', 'UserController@getNotification');
            $router->post('/', 'UserController@createNotification');
            $router->delete('/{id}', 'UserController@deleteNotification');
        });
        // workorder routes
        $router->group(['prefix' => 'workorder'], function ($router) {
            $router->post('/', 'UserController@createWorkorder');
            $router->get('/', 'UserController@retrieveWorkorder');
            $router->get('{id}', 'UserController@getWorkorder');
            $router->delete('{id}', 'UserController@deleteWorkorder');
            $router->put('{id}', 'UserController@updateWorkorder');
        });
        // specific message
        $router->group(['prefix' => 'messages'], function ($router) {
            $router->get('/{id}', 'UserController@messagesList');
            $router->post('/{id}', 'UserController@createMessages');
        });
        // specific message
        $router->group(['prefix' => 'recent_messages'], function ($router) {
            $router->get('/', 'UserController@recentMessages');
            $router->put('/update', 'UserController@updateReadMessages');
        });
        // verify payment for services
        $router->post('payment/{id}', 'UserController@paymentInvoice');
        // get the list of users
        $router->get('users', 'UserController@userList');
        // user account routes
        $router->put('update', 'UserController@updateAccount');
        // change user pasword
        $router->post('change_password', 'UserController@changePassword');
    });
});