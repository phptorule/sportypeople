<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Mail\AuthMailables;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('guest');
    }

    public function sendResetPasswordEmail($hash)
	{
		$user = DB::select("SELECT * FROM `users` WHERE MD5(`id`) = '" . $hash . "' LIMIT 1");

		if ( ! count($user))
		{
			return FALSE;
		}
	
		if ($user)
		{
			$user = $user[0];
			
			$password = $this->generate_password();
			DB::table('users')->where('id', $user->id)->update(array('password' => Hash::make($password)));
			$sender = new AuthMailables("Reset Password (step 2) - SportyPeople", "reset", array('new_pass' => $password));			
			Mail::to($user->email)->send($sender);
			if ( ! count(Mail::failures()))
			{
				return redirect("/login_show/reset");
			}
		}
		
		return FALSE;
    }

    public function send_hash_accept(Request $request)
    {
    	$this->validate($request, [
            'email' => 'required|email',
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();
		
		if ( ! $user)
		{
			return FALSE;
		}

		
    	$sender = new AuthMailables("Reset password (step 1) - SportyPeople", "pre_reset", array('hash' => md5($user->id), 'first_name' => $user->first_name));
		Mail::to($user->email)->send($sender);
		if ( ! count(Mail::failures()))
		{
			return array("success" => TRUE);
		}
		return array("success" => FALSE);
    }
	
	public function generate_password($length = 8) 
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);

		for ($i = 0, $result = ''; $i < $length; $i++) {
			$index = rand(0, $count - 1);
			$result .= mb_substr($chars, $index, 1);
		}

		return $result;
	}
}
