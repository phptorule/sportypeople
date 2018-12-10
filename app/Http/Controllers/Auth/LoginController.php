<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /*
    * Called as API via AJAX. 
    * @see trait AuthenticatesUsers
    * 
    */
    public function ajax_login(Request $request)
	{
		
        $creds = [
            'email'    => $request->input('username'),
            'password' => $request->input('password')
        ];
        
        $remember_me = $request->remember_me;
	
    	$full = FALSE;
		
        $user = Auth::user();
		
	
        if( ! isset($user->id))
		{
            $success = Auth::attempt($creds, $remember_me);
			$users = DB::table('users')->where('email', $request->input('username'))->first();
			
			if ($users)
			{
				if ( ! empty($users->first_name) &&
					 ! empty($users->birth_year) &&
					 ! empty($users->latitude) &&
					 ! empty($users->longitude) &&
					 ! empty($users->full_address) &&
					 ! empty($users->gender) )
					 {
						 $full = TRUE;
					 }
			}
			
        } else {
            $success = true;
        }

        return [
            'success' => $success,
			'full' => $full
        ];
    }

    public function ajax_me(){
        var_dump(Auth::user());
    }
}
