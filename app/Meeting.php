<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{

	protected $fillable = ['meeting_date', 'latitude', 'longitude', 'age_min', 'age_max', 'gender'];

}
