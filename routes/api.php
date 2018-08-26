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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user/email', 'UserController@checkEmail');
Route::post('/user/password', 'UserController@checkPassword');
Route::post('/user/login', 'UserController@login');
Route::post('/user', 'UserController@register');
Route::put('/user', 'UserController@update')->middleware('auth:api');
Route::post('/user/image', 'UserController@storeImage')->middleware('auth:api');
