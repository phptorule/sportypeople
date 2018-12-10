<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use App\UsersDevices;

class UserController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}
	
	public function change_password(Request $request)
	{
		DB::table("users")->where("id", Auth::user()->id)->update(array("password" => Hash::make($request->new_pass)));
	}

	public function save_profile(Request $request)
	{	
		$request->gender = isset(trim($request->gender)[0]) ? trim($request->gender)[0] : '';
		$request->availability = trim($request->availability) == 'Not available' ? 1 : (trim($request->availability) == 'Public' ? 0 : 0);
		$request->invite_sent = trim($request->invite_sent) == 'Yes' ? 1 : 0;

		Input::merge(array_map('trim', Input::all()));
		
		$this->validate($request, [
            'first_name'       => 'required|string|max:255',
	        'last_name'      => 'required|string|max:255',
	       // 'birth_year'   => 'required|string|max:255',
	       // 'birth_day'  => 'required|string|max:255',
	       // 'full_address'    => 'required|string|max:255'
        ]);
		
		$update = array(
				'first_name' => $request->first_name,
				'last_name' => $request->last_name,
				'birth_year' => $request->birth_year,
				'birth_month' => date("m", strtotime($request->birth_month)),
				'birth_day' => $request->birth_day,
				'full_address' => $request->full_address,
				'latitude' => $request->latitude,
				'longitude' => $request->longitude,
				'gender' => $request->gender,
				'able_min' => $request->able_min,
				'able_max' => $request->able_length,
				'availability' => $request->availability,
				'about_me' => $request->about_me,
				'name_addr' => $request->name_addr,
				'invite_sent' => $request->invite_sent
			);
			
		
		
			
		$file = "";
		if ($request->hasFile('avatar')) 
		{
			if ( ! file_exists(public_path("images/users")))
			{
				mkdir(public_path("images/users"), 0755, TRUE);
			}
			
			$name_file = time() .".". $request->avatar->getClientOriginalExtension();
			
			list($fn_width, $fn_height) = getimagesize($request->file('avatar'));
			list($width, $height) = $this->get_resize($fn_width, $fn_height);

			if (move_uploaded_file($request->file('avatar'), $file = "images/users/" . $name_file))
			{
                Image::make($file)->resize($width, $height)->save($min_file = "images/users/min_" . $name_file);
                $update['file'] = $min_file;
               
				if ($request->rotate_img)
				{
					$img = Image::make($min_file);
					$big_file = Image::make($file);
					for($i = 0; $i < $request->rotate_img ; $i++)
               		{	
						$img->rotate( -90 );
						$big_file->rotate( -90 );

               		}
					$img->save();
					$big_file->save();
				}
			}
		}
		else
		{
			$user = DB::table("users")->where("id", $request->id)->first();

			if ($request->rotate_img)
			{
               	$img = Image::make($user->file);
               	$img_big = Image::make(str_replace("min_", "", $user->file));
               	for($i = 0; $i < $request->rotate_img ; $i++)
               	{
					$img->rotate( -90 );
					$img_big->rotate( -90 );

               	}
				$img->save();
				$img_big->save();

			//	DB::table("users")->where("id", $request->id)->update(array("rotate" => $request->rotate_img));
			}
		}
			
		DB::table('users')->where('id', $request->id)->update($update);
		return redirect('/meeting/create');
	}
	

	public function get_resize($w, $h)
	{
		$ratio = $w / $h;
		$width = 0;
		$height = 0;
		if( $ratio > 1) 
		{
			$width = 320;
			$height = 320 / $ratio;
		}
		else 
		{
			$width = 320 * $ratio;
			$height = 320;
		}

		return array($width, $height);
	}

	public function get_profile(Request $request)
	{		
        $data = DB::table('users')->where('id', Auth::user()->id)->first();
		return view('profile', array('user' => $data));
	}
	
	public function subscriptionOneSignal(Request $request)
	{
		$device = new UsersDevices;
		$device->users_id = Auth::user()->id;
		$device->one_signal = $request->input("onesignal");
		$device->save();
	}

	public function unsubscriptionOneSignal(Request $request)
	{
		UsersDevices::where("one_signal", "=", $request->input("onesignal"))->delete();
	}

	public function unsubscriptionOneSignalFull() 
	{
		UsersDevices::where("users_id", "=", Auth::user()->id)->delete();
	}

	public function logout()
	{
		$this->unsubscriptionOneSignalFull();
		Auth::logout();
		return redirect('/');
	}
}
