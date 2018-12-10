<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use \App\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteMailables;
use OneSignal;

class MeetingController extends Controller
{	
	public function __construct()
	{
		$this->middleware('auth');
	}
	
	private $secret = 3;
	public function create()
	{
	    return view('meetings.addedit');
	}
	
	public function show($hash)
	{
		$meeting = DB::select("SELECT * FROM `meetings` WHERE MD5(`id` + " . $this->secret . ") = '" . $hash . "' LIMIT 1");
		$meeting = isset($meeting[0]) ? $meeting[0] : FALSE;
		if ( ! $meeting)
		{
			return redirect('/404');
		}
		
		$meeting->meeting_date = date('d-m-Y', strtotime(str_replace("00:00:00", "", $meeting->meeting_date)));
		$array = array('list' => array(), 'meeting_id' => $meeting->id, 'meeting' => $meeting);
		
		$invites = array();
		foreach(DB::table('invites')->get() as $row)
		{
			$invites[$row->meeting_id]['users'][] = $row->user_id;
		}

		$sql = sprintf("SELECT  `name_addr`, `gender`, `about_me`, `birth_year`, `file`, `id`, `able_max`, `first_name`, `last_name`, `full_address`, ( 6371 * acos( cos( radians('%s') ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( `latitude` ) ) ) ) AS `distance` FROM `users` WHERE `id` != '" . $meeting->user_id . "' HAVING `distance` < `able_max` ORDER BY `distance` LIMIT 0 , 50",
						  $meeting->latitude, 
						  $meeting->longitude,
						  $meeting->latitude); 
		
		foreach(DB::select( $sql ) as $row)
		{
			$row->invite = FALSE;
			if (isset($invites[$meeting->id]['users']) && in_array($row->id, $invites[$meeting->id]['users']))
			{
				$row->invite  = TRUE;
			}
			
			$row->age = date('Y') - $row->birth_year;
			
			if ( ($meeting->age_min <= $row->age) && ($row->age <= $meeting->age_max))
			{
				$array['list'][$row->id] = $row;								
			}
		} 
		
		
		$array['range_age'] = array();
		$ages = array(array(18, 24), array(25, 34), array(35, 44), array(45, 54), array(55, 64), array(65, 74), array(75, 84));
		foreach($ages as $row)
		{
			if ($row[0] >= $meeting->age_min && ($row[0] <= $meeting->age_min || $row[0] <= $meeting->age_max))
			{
				$array['range_age'][] = $row;
			}
		}
		
		
		return view("meetings.result", $array);
	}
	
	private function is_post($array)
	{
		if (empty($array))
		{
			abort(404);
		}
	}
	
	public function search(Request $request)
	{
		$this->is_post($request->all());
		
		$meet = DB::select("SELECT * FROM `meetings` WHERE MD5(`id` + " . $this->secret . ") = '" . $request->meeting_id . "' LIMIT 1");
		$meet = isset($meet[0]) ? $meet[0] : FALSE;
		
		$array = array('list' => array());
		$meeting = DB::table('meetings')->where('id', $meet->id)->first();
		
		$invites = array();
		foreach(DB::table('invites')->get() as $row)
		{
			$invites[$row->meeting_id]['users'][] = $row->user_id;
		}
		
		$sql = sprintf("SELECT  `name_addr`, `gender`, `about_me`, `birth_year`, `file`, `id`, `able_max`, `first_name`, `last_name`, `full_address`, ( 6371 * acos( cos( radians('%s') ) * cos( radians( `latitude` ) ) * cos( radians( `longitude` ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( `latitude` ) ) ) ) AS `distance` FROM `users` WHERE `id` != '" . $meeting->user_id . "' HAVING `distance` < `able_max` ORDER BY `distance` LIMIT 0 , 50",
						  $meeting->latitude, 
						  $meeting->longitude,
						  $meeting->latitude); 
		
		$request->gender = strtoupper($request->gender[0]);
		
		if (trim($request->gender) != 'F' && trim($request->gender) != 'M')
		{
			$request->gender = 'all';
		}
		
		if ( ! strpos('-', $request->age))
		{
			$request->age = strtolower($request->age);
		}
		
		
		$age_ids = array();
		foreach(DB::select($sql) as $row)
		{
			// filter age
			$row->age =  date('Y') - $row->birth_year;
			
			if (isset($request->age) && trim($request->age) != 'all')
			{
				list($min_age, $max_age) = explode('-', trim($request->age));
				
				$min_age = trim($min_age) * 1;
				$max_age = trim($max_age) * 1;
				
				if (($min_age * 1 <= $row->age * 1) && ($max_age * 1 >= $row->age * 1))
				{
					$age_ids[] = $row->id;			
				}
			}
			elseif(trim($request->age) == 'all')
			{
				if ( ($meeting->age_min * 1 <= $row->age * 1) && ($row->age * 1 <= $meeting->age_max * 1))
				{
					$age_ids[] = $row->id;								
				}
			}
		}
		
		$users_ids = array();
		foreach(DB::table('users')->whereIn('id', $age_ids)->get() as $row)
		{
			// filter gender
			if (isset($request->gender) && $request->gender != 'all')
			{
				if ($row->gender == $request->gender)
				{
					$users_ids[] = $row->id;			
				}
			}
			else
			{
				$users_ids[] = $row->id;
			}
		}
		
		$array['list'] = array();
		foreach(DB::table('users')->whereIn('id', $users_ids)->get() as $row)
		{
			$row->invite = FALSE;
			if (isset($invites[$meeting->id]['users']) && in_array($row->id, $invites[$meeting->id]['users']))
			{
				$row->invite  = TRUE;
			}
			
			$row->age =  date('Y') - $row->birth_year;
			
			$array['list'][] = $row;
		}
		
		return $array;
	}
	
	public function watch_invite(Request $request)
	{
		$this->is_post($request->all());
		$not_read_messages = array();
		foreach(DB::table('invite_messages')->where('status', 'new')->get() as $row)
		{
			$not_read_messages[$row->invite_id] = $row->user_id;
		}
		
		$invites = array();
		foreach(DB::table('invites')->where('user_id', Auth::user()->id)->where('status', 'pending')->get() as $row)
		{
			$invites[] = $row;
		}
		
		$message = 0;
		
		foreach(DB::table('invites')->get() as $row)
		{
			if (isset($not_read_messages[$row->id]) && $not_read_messages[$row->id] != Auth::user()->id)
			{
				$message ++;
			}
		}
		
		$_invites = array();
		foreach(DB::select("select * from `invites` where `status` = 'accepted' or `status` = 'rejected'") as $row)
		{
			$_invites[$row->meeting_id] = $row;
		}
		
		$statuses = array();
		foreach(DB::table('meetings')->where('user_id', Auth::user()->id)->get() as $row)
		{
			if(isset($_invites[$row->id]) && ! $_invites[$row->id]->watch)
			{
				$statuses[] = $_invites[$row->id];
			}
		}
		
		return array("statuses" => $statuses, "invites" => count($invites), "messages" => $message);
	}
	
	public function invite(Request $request)
	{
		$this->is_post($request->all());
		if (
			$id = DB::table('invites')->insertGetId(
			 array(
				'user_id' => $request->user_id,
				'meeting_id' => $request->meeting_id,
				'created_at' => new \DateTime()
			 )
		 ))
		{
			$user = DB::table("users")->where("id", $request->user_id)->first();
			
			$data = array(
				"meeting" => DB::table("meetings")->where("id", $request->meeting_id)->first(),
				"user" => $user
			);


			$sender = new InviteMailables("Invite - SportyPeople", "invite", $data);//mjs
			
			if ($user->invite_sent)
			{
				Mail::to($user->email)->send($sender);
			}

			$devices = DB::table("users_devices")->where("users_id", "=", $user->id)->get();
			foreach($devices as $d)
			{
				OneSignal::sendNotificationToUser("Invite - SportyPeople", $d->one_signal, $url = url("/meeting/history"), $data = null, $buttons = null, $schedule = null);
			}

			DB::table('invites')->where('id', $id)->update(array('base_url' => md5($id + $this->secret)));
			
			if ( ! empty($request->message))
			{
				DB::table('invite_messages')->insert(array(
					'user_id' => Auth::user()->id, 
					'status' => 'old', 
					'invite_id' => $id, 
					'message_body' => $request->message,
					'created_at' => \Carbon\Carbon::now()
				));
			}
			
			return array("status" => "ok", "messages" => "Ok");
		}
		return array("status" => "error", "messages" => "error");
	}

	public function save(Request $request) 
	{
		$this->validate($request, [
	        'latitude'       => 'required|numeric',
	        'longitude'      => 'required|numeric',
	        'full_address'   => 'required',
	        'meeting_month'  => 'required|not_in:Month',
	        'meeting_day'    => 'required|not_in:Day',
	        'gender'         => 'required|in:All,M,F',
	        'age_min'        => 'required|numeric',
	        'age_max'        => 'required|numeric'
	    ]);
		
		
		$data = $request->all();
		$MeetingDate = Carbon::parse(date("Y") . '-' . trim($data['meeting_month']) . '-' . trim($data['meeting_day']) . " 23:00:00");
		$Now = new Carbon();

		// If meeting date is in the past, increase it for 1 year.
		$diff = $Now->gte($MeetingDate);
		if($diff === true)
		{
			$MeetingDate->addYear();
		}

		$meeting = new Meeting;

		$meeting->user_id       = Auth::user()->id;
		$meeting->full_address  = $data['full_address'];
		$meeting->meeting_date  = (string)$MeetingDate;
		$meeting->latitude      = $data['latitude'];
		$meeting->longitude     = $data['longitude'];
		$meeting->age_min       = $data['age_min'];
		$meeting->age_max       = $data['age_max'];
		$meeting->gender        = $data['gender'];
		$meeting->flexible_days = ! empty($data['flexible']) ? 3 : 0; 

		$meeting->save();
		
		$hash = md5($meeting->id * 1 + $this->secret);
		DB::table('meetings')->where('id', $meeting->id)->update(array('base_url' => $hash));
		$meeting->base_url = $hash;
		return $meeting;
	}
	
	public function history()
	{
		$data = array('upcoming' => array(), 
					  'upconfirmed' => array(), 
					  'past' => array());
		
		$invites = array();
		foreach(DB::table('invites')->get() as $row)
		{
			$invites[$row->meeting_id] = $row;
		}
		
		$users = array();
		foreach(DB::table('users')->get() as $row)
		{
			$users[$row->id] = $row;
		}
		
		$meetings = array();
		foreach(DB::table('meetings')->get() as $row)
		{
			$meetings[$row->id] = $row;
		}
		
		$new_messages = array();
		foreach(DB::table('invite_messages')->where('status', 'new')->get() as $row)
		{
			$new_messages[$row->invite_id][] = $row->user_id;
		}
		
		
		foreach(DB::table('invites')->where('user_id', Auth::user()->id)->get() as $row)
		{
			$row->meeting = isset($meetings[$row->meeting_id]) ? $meetings[$row->meeting_id] : FALSE;
			$row->user = isset($users[$row->meeting->user_id]) ? $users[$row->meeting->user_id] : FALSE;
			$row->msg_new = isset($new_messages[$row->id]) && in_array($row->meeting->user_id, $new_messages[$row->id]) ? TRUE : FALSE;
			$row->hash = $row->base_url;
			
			$row->created_at = $row->meeting->meeting_date;
		
			if(strtotime($row->meeting->meeting_date) < time())
			{
				$data['past'][] = $row;
			}
			elseif($row->status == 'accepted')
			{
				$data['upcoming'][] = $row;		
			}
			elseif($row->status != 'accepted' && $row->status != 'rejected')
			{
				$data['upconfirmed'][] = $row;		
			}
		}
		
		
		if (DB::table('invites')->where('user_id', Auth::user()->id)->first() && DB::table('invites')->where('user_id', Auth::user()->id)->first()->status  == 'pending')
		{
			DB::table('invites')->where('user_id', Auth::user()->id)->update(array("status" => "old"));			
		}
		
		$_invites = array();
		foreach(DB::select("select * from `invites` where `status` = 'accepted' or `status` = 'rejected'") as $row)
		{
			$_invites[$row->meeting_id] = $row;
		}
		
		$statuses = array();
		foreach(DB::table('meetings')->where('user_id', Auth::user()->id)->get() as $row)
		{
			if(isset($_invites[$row->id]))
			{
				$statuses[$row->id] = $_invites[$row->id];
			}
		}

        foreach(DB::table('meetings')->where('user_id', Auth::user()->id)->get() as $row)
		{
			if( isset($invites[$row->id]) && 
				isset($invites[$row->id]->user_id) && 
				isset($users[$invites[$row->id]->user_id]) )
			{
				$row->status = isset($statuses[$row->id]) ? $invites[$row->id]->watch ? FALSE : TRUE : FALSE;
				$row->invite_id = $invites[$row->id]->id;
				$row->hash = $invites[$row->id]->base_url;
				$row->msg_new = isset($new_messages[$invites[$row->id]->id]) && in_array($invites[$row->id]->user_id, $new_messages[$invites[$row->id]->id]) ? TRUE : FALSE;
				$row->user = isset($users[$invites[$row->id]->user_id]) ? $users[$invites[$row->id]->user_id] : FALSE;
				
				$row->created_at = $row->meeting_date;
				
				$row->sent = true;

				if(strtotime($row->meeting_date) < time())
				{

					$data['past'][] = $row;
				}
				else
				{
                    if ( in_array($invites[$row->id]->status, ['old', 'pending']))
                    {
                        $data['upconfirmed'][] = $row;
                    }
                    else
                    {
                        $data['upcoming'][] = $row;					
                    }
				}
			}
		}


		return view("meetings.history", $data);
	}
	
	public function get_msg_view(Request $request)
	{
		$this->is_post($request->all());
		
		$messages = array();
		
		$users = array();
		foreach(DB::table('users')->get() as $row)
		{
			$users[$row->id] = $row;
		}
		
		$invite = DB::table('invites')->where('base_url', $request->hash)->first();
		foreach(DB::table('invite_messages')->where('invite_id', $invite->id)->orderBy('created_at', 'desc')->take(2)->get()->reverse() as $row)
		{
			$row->user = isset($users[$row->user_id]) ? $users[$row->user_id] : FALSE;
			$messages[] = $row;
		}
		
		$tmp_msg = [];
		for($i = 0, $length = 1;  $i < count($messages); $i ++)
		{
			$item = $messages[$i];
			if (isset($tmp_msg[$i - $length]) && isset($messages[$i - $length]) && $item->user_id == $messages[$i - $length]->user_id)
			{
				$tmp_msg[$i - $length]['message_body'] .= "</br>" . $item->message_body;
				$length ++;
				continue;
			}
			
			$tmp_msg[$i]['user_id'] = $item->user_id;
			$tmp_msg[$i]['message_body'] = $item->message_body;
			$length = 1;
		}
		
		foreach($tmp_msg as $key => $msg)
		{
			$tmp_msg[$key]['user'] = isset($users[$msg['user_id']]) ? $users[$msg['user_id']] : FALSE;
		}

		
		$messages = array();
		foreach($tmp_msg as $msg)
		{
			$messages[] = $msg;
		}
		
		return json_encode($messages);
	} 

	public function get_msg(Request $request)
	{
		$this->is_post($request->all());
		
		$messages = array();
		
		$users = array();
		foreach(DB::table('users')->get() as $row)
		{
			$users[$row->id] = $row;
		}
		
		$invite = DB::table('invites')->where('base_url', $request->hash)->first();
		foreach(DB::table('invite_messages')->where('invite_id', $invite->id)->orderBy('created_at', 'desc')->get()->reverse() as $row)
		{
			$row->user = isset($users[$row->user_id]) ? $users[$row->user_id] : FALSE;
			$messages[] = $row;
		}

		DB::table('invite_messages')->where('user_id', '!=', Auth::user()->id)->update(array("read_flag" => '1'));

		$tmp_msg = [];
		$first = TRUE;
		for($i = 0, $length = 1;  $i < count($messages); $i ++)
		{
			$item = $messages[$i];
			if (isset($tmp_msg[$i - $length]) && isset($messages[$i - $length]) && $item->user_id == $messages[$i - $length]->user_id)
			{
				$length ++;
				$first = FALSE;
			}
			
			$tmp_msg[$i]['user_id'] = $item->user_id;
			$tmp_msg[$i]['message_body'] = $item->message_body;
			$tmp_msg[$i]['time'] =  $this->time_since(time() - strtotime($item->created_at)) . " ago";
			$tmp_msg[$i]['first'] = $length == 1 ? TRUE : FALSE;
			$tmp_msg[$i]['new'] =  ! $item->read_flag && $item->user_id == $invite->id ? TRUE : FALSE;

			$length = 1;
		}
		
		foreach($tmp_msg as $key => $msg)
		{
			$tmp_msg[$key]['user'] = isset($users[$msg['user_id']]) ? $users[$msg['user_id']] : FALSE;
		}

		
		$messages = array();
		foreach($tmp_msg as $msg)
		{
			$messages[] = $msg;
		}
		
		return json_encode($messages);
	}

	public function time_since($since)
	{
	    $chunks = array(
	        array(60 * 60 * 24 * 365 , 'year'),
	        array(60 * 60 * 24 * 30 , 'month'),
	        array(60 * 60 * 24 * 7, 'week'),
	        array(60 * 60 * 24 , 'day'),
	        array(60 * 60 , 'hour'),
	        array(60 , 'minute'),
	        array(1 , 'second')
	    );

	    for ($i = 0, $j = count($chunks); $i < $j; $i++) {
	        $seconds = $chunks[$i][0];
	        $name = $chunks[$i][1];
	        if (($count = floor($since / $seconds)) != 0) {
	            break;
	        }
	    }

	    $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
	    return $print;
	}
	
	public function view_invite($hash, $more)
	{
		$invt = DB::select("SELECT * FROM `invites` WHERE `base_url` = '" . $hash . "' LIMIT 1");
		$invt = isset($invt[0]) ? $invt[0] : FALSE;
		
		$data = array();
		$invite = DB::table('invites')->where('id', $invt->id)->first();
		$data['info']['invite'] = $invite;
		$data['info']['meeting'] = DB::table('meetings')->where('id', $invite->meeting_id)->first();
		
		$data['info']['meeting']->meeting_date = date("d-m-Y", strtotime($data['info']['meeting']->meeting_date));
		
		
		if (Auth::user()->id != $data['info']['invite']->user_id)
		{
			$data['info']['user'] = DB::table('users')->where('id', $data['info']['invite']->user_id)->first();			
		}
		else
		{
			$data['info']['user'] = DB::table('users')->where('id', $data['info']['meeting']->user_id)->first();			
		}
		
		$data['info']['user']->age =  date('Y') - $data['info']['user']->birth_year * 1;
		
		$data['info']['messages'] = array();
		
		$users = array();
		foreach(DB::table('users')->get() as $row)
		{
			$row->age = date('Y') - $row->birth_year * 1;
			$users[$row->id] = $row;
		}
		
		DB::table('invite_messages', $invt->id)->where('user_id', '!=', Auth::user()->id)->update(array('status' => 'old'));
		
		if ($data['info']['meeting']->user_id == Auth::user()->id && ($data['info']['invite']->status == 'accepted' || $data['info']['invite']->status == 'rejected'))
		{
			DB::table('invites')->where('id', $invt->id)->update(array('watch' => TRUE));
		}
		
		/*
		$user_from = DB::table('invite_messages', $invt->id)->first()->user_id;
		
		DB::table('invite_messages')
			->where('invite_id', $invt->id)
			->where('user_id', $user_from)
			->update(array('status' => 'old'));*/
		
		foreach(DB::table('invite_messages')->where('invite_id', $invt->id)->orderBy('created_at', 'desc')->take($more)->get()->reverse() as $row)
		{
			$row->user = isset($users[$row->user_id]) ? $users[$row->user_id] : FALSE;
			$data['info']['messages'][] = $row;
		}
		
		//dd($data['info']['messages']);
		
		$tmp_msg = [];
		for($i = 0, $length = 1;  $i < count($data['info']['messages']); $i ++)
		{
			$item = $data['info']['messages'][$i];
			if (isset($tmp_msg[$i - $length]) && isset($data['info']['messages'][$i - $length]) && $item->user_id == $data['info']['messages'][$i - $length]->user_id)
			{
				$tmp_msg[$i - $length]['message_body'] .= '</br>' . $item->message_body;
				$length ++;
				continue;
			}
			
			$tmp_msg[$i]['user_id'] = $item->user_id;
			$tmp_msg[$i]['message_body'] = $item->message_body;
			$length = 1;
		}
		
		foreach($tmp_msg as $key => $msg)
		{
			$tmp_msg[$key]['user'] = isset($users[$msg['user_id']]) ? $users[$msg['user_id']] : FALSE;
		}

		$data['info']['messages'] = array();
		foreach($tmp_msg as $msg)
		{
			$data['info']['messages'][] = $msg;
		}
	
		return view("meetings.view", $data);
	}

	public function accept(Request $request)
	{
		$this->is_post($request->all());
		$invite = $this->get_invite_for_url($request->invite_id);
		DB::table('invites')->where('id', $invite->id)->update(array('status' => 'accepted'));

		$meeting = DB::table('meetings')->where("id", "=", $invite->meeting_id)->first();
		$user = DB::table('users')->where("id", "=", $meeting->user_id)->first();
		$devices = DB::table("users_devices")->where("users_id", "=", $user->id)->get();
		foreach($devices as $d)
		{
			OneSignal::sendNotificationToUser("Accept Invite - SportyPeople ! ", $d->one_signal, $url = url("/meeting/history"), $data = null, $buttons = null, $schedule = null);
		}

		return json_encode(array("status" => "ok"));
	}
	
	public function reject(Request $request)
	{
		$this->is_post($request->all());
		$invite = $this->get_invite_for_url($request->invite_id);
		DB::table('invites')->where('id', $invite->id)->update(array('status' => 'rejected'));

		$meeting = DB::table('meetings')->where("id", "=", $invite->meeting_id)->first();
		$user = DB::table('users')->where("id", "=", $meeting->user_id)->first();
		$devices = DB::table("users_devices")->where("users_id", "=", $user->id)->get();
		foreach($devices as $d)
		{
			OneSignal::sendNotificationToUser("Reject Invite - SportyPeople ! ", $d->one_signal, $url = url("/meeting/history"), $data = null, $buttons = null, $schedule = null);
		}

		return json_encode(array("status" => "ok"));
	}
	
	public function get_invite_for_url($hash)
	{
		return DB::table('invites')->where('base_url', $hash)->first();
	}
	
	public function get_meeting_for_url($hash)
	{
		return DB::table('meetings')->where('base_url', $hash)->first();
	}
	
	public function msgs_send(Request $request)
	{
		$invite = $this->get_invite_for_url($request->invite_id);
		$owner = DB::table('meetings')->where('id', DB::table('invites')->where('id', $invite->id)->first()->meeting_id)->first()->user_id;
		if ($owner == Auth::user()->id)
		{
			DB::table('invite_messages')->where('invite_id', $invite->id)->insert(array(
				'invite_id' => $invite->id,
				'user_id' => $owner,
				'message_body' => $request->msg_send,
				'created_at' => \Carbon\Carbon::now()
			));
		}
		else
		{
			DB::table('invite_messages')->where('invite_id', $invite->id)->insert(array(
				'invite_id' => $invite->id,
				'user_id' => DB::table('invites')->where('id', $invite->id)->first()->user_id,
				'message_body' => $request->msg_send,
				'created_at' => \Carbon\Carbon::now()
			));
		}
		return json_encode(array('status' => 'ok'));
	}
}