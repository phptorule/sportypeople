<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    if(isset(Auth::user()->id))
	{
		return redirect('/home');
	}
    return view('welcome');
});

Route::any('/logout', 'UserController@logout');

Route::get('/home', 'HomeController@index');

Route::get('/login_show', function(){
	return view('welcome', ['show_login' => TRUE]);
});

Route::get('/login_show/reset', function(){
	return view('welcome', ['show_reset' => TRUE]);
});

//profile
Route::get("profile", "UserController@get_profile");

//invite
Route::any("/save_invite", 'MeetingController@invite');

//invite reject
Route::any("/invite_reject", 'MeetingController@reject');

//serach
Route::any("/search", 'MeetingController@search');

//invite accept
Route::any("/invite_accept", 'MeetingController@accept');

//get messages 
Route::any('/get_msg', 'MeetingController@get_msg');

Route::any('/get_msg_view', 'MeetingController@get_msg_view');

//msg invite
Route::any("/send_msg_invite", "MeetingController@msgs_send");

//watch invite
Route::any("/watch_invite", 'MeetingController@watch_invite');

//view
Route::get("meeting/view/{invite}/more/{more}", 'MeetingController@view_invite');

Route::any("save_profile", "UserController@save_profile");

//search
Route::get('meeting/create', 'MeetingController@create')->middleware("web");

//upload
Route::any("upload_avatar", "UserController@upload_avatar");

//search result
Route::get('meeting/result/{hesh}', 'MeetingController@show')->middleware("web");

//404
Route::get('404', 'HomeController@page_404');

//history
Route::get("meeting/history/", 'MeetingController@history');

// reset password step 2

Route::post("/subscriptionOneSignal", "UserController@subscriptionOneSignal");
Route::post("/unsubscriptionOneSignal", "UserController@unsubscriptionOneSignal");

Route::get("accept/{hash}", "\App\Http\Controllers\Auth\ForgotPasswordController@sendResetPasswordEmail");

Route::group(['prefix' => 'api'], function () {
	Route::post("user/change_password", '\App\Http\Controllers\UserController@change_password');
	Route::post('user/ajax_register', '\App\Http\Controllers\Auth\RegisterController@ajax_register');
	Route::post('user/ajax_login', '\App\Http\Controllers\Auth\LoginController@ajax_login');
	Route::post("user/ajax_forgot_password", '\App\Http\Controllers\Auth\ForgotPasswordController@send_hash_accept');
	Route::post("meeting/save", '\App\Http\Controllers\MeetingController@save');
});

Route::any('/locale/{locale}', function ($locale){
        session(['locale'=>$locale]);
        return  redirect()->back();
});