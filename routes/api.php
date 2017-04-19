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
Route::put('/registerUserKey', 'users@register_key')->middleware('api');
Route::post('/searchUser', 'users@searchUser')->middleware('api');
Route::post('/get_single_report', 'report_post@getSingleReport')->middleware('api');
Route::post('/getComment', 'report_post@getComment')->middleware('api');
Route::post('/addComment', 'report_post@addComment')->middleware('api');
Route::get('/tips_categories', 'safety_tip@getAllCategory')->middleware('api');
Route::post('/get_details_tip', 'safety_tip@getDetailsTips')->middleware('api');
Route::post('/add_avatar', 'users@addAvatar')->middleware('api');
Route::post('/add_chat_user', 'users@addUser')->middleware('api');
Route::post('/search_user', 'users@search_user')->middleware('api');

Route::get('/getMultiUser', 'users@getMultiUser')->middleware('api');
Route::post('/addMessage', 'users@addMessage')->middleware('api');
Route::post('/fetchChatRoom', 'users@fetchChatRoom')->middleware('api');
Route::post('/fetchSingleChatRoom', 'users@fetchSingleChatRoom')->middleware('api');

Route::get('/testMessage', 'users@testMessage')->middleware('api');
