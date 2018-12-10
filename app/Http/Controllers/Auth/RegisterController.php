<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Mail\AuthMailables;
use Illuminate\Support\Facades\Mail;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/profile';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:100',
            'middle_name' => 'max:100',
            'last_name' => 'max:100',

            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $ok = User::create([
            'first_name'  => $data['first_name'],
//            'middle_name' => $data['middle_name'],
            'last_name'   => $data['last_name'],
            'email'       => $data['email'],
            'username'    => $data['email'],
            'password'    => bcrypt($data['password']),
            'country_id' => 0,
            'birth' => 0
        ]);

        $creds = [
            'email'    => $data['email'],
            'password' => $data['password']
        ];
        $rememberMe = true;
        Auth::attempt($creds, $rememberMe);

        return $ok;
    }

    public function ajax_register(Request $request)
	{
		$this->validate($request, array(
			'first_name' => 'required|max:100',
            'middle_name' => 'max:100',
            'last_name' => 'max:100',
			'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed'
		));
		
		$data = array(
			'first_name' => $request->first_name,
			'last_name' => $request->last_name,
			'email' => $request->email,
			'password' => Hash::make($request->password)
		);
		
		if (DB::table('users')->insert($data))
		{
			$rememberMe  = TRUE;
			$creds = array(
				'password' => $request->input('password'),
				'email' => $request->input('email')
			);
            
            try {
                $sender = new AuthMailables("Signup - SportyPeople", "signup", array('first_name' => $request->first_name));
                Mail::to($request->email)->send($sender);
            } catch(\Exception $e) {
                // do some
            }
            
            if (Auth::attempt($creds, $rememberMe))
            {
                return response()->json(array('success' => 'ok'));
            }
            
            return response()->json(array('success' => 'error'));
		}		
    }
	
	public function ajax_save_profile(Request $request)
	{
       return array();
    }
}
