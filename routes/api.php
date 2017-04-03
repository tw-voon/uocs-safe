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

Route::get('/emergency_contact', 'emergency_contact@index')->middleware('api');
Route::post('/login', 'users@login')->middleware('api');
Route::get('/report_type', 'report_type@getReportType')->middleware('api');
Route::post('/report_post', 'report_post@index')->middleware('api');
Route::post('/get_report', 'report_post@getReport')->middleware('api');
Route::post('/register', 'users@register_user')->middleware('api');
Route::post('/registerUserKey', 'users@register_key')->middleware('api');
Route::post('/searchUser', 'users@searchUser')->middleware('api');

Route::get('/getMultiUser', 'users@getMultiUser')->middleware('api');
Route::post('/addMessage', 'users@addMessage')->middleware('api');
Route::get('/fetchChatRoom', 'users@fetchChatRoom')->middleware('api');
Route::post('/fetchSingleChatRoom', 'users@fetchSingleChatRoom')->middleware('api');

Route::get('/testMessage', 'users@testMessage')->middleware('api');
