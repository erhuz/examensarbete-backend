<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['json.response']], function () {


    // public routes
    Route::post('/login', 'Api\AuthController@login')->name('login.api');
    Route::post('/register', 'Api\AuthController@register')->name('register.api');
    Route::get('/test', function (Request $request) {
        return [
            'status' => 200,
            'test' => 'succsessful'
        ];
    });

    // private routes
    Route::middleware('auth:api')->group(function () {
        Route::get('/logout', 'Api\AuthController@logout')->name('logout');
        Route::get('/user', 'Api\UserController@user');
        Route::get('/auth/test', function (Request $request)
        {
            return ['Auth' => 'Successful', 'Description' => 'If this message reached your client, it  means the authorisation worked!'];
        });
    });

});

