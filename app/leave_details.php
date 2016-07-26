<?php

namespace App;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class leave_details  extends Eloquent 
{
	protected $collection = 'leave_details';
	protected $dates = ['from_date', 'to_date'];
	protected $fillable = [
         'employee_code','nid','type','user_name','from_date','to_date','total_days','from_session','to_session','updated_date',
    ];

}