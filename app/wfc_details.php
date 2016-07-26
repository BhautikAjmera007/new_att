<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class wfc_details extends Moloquent
{
    protected $collection = 'wfc_details';
    	protected $dates = ['from_date', 'to_date'];
	protected $fillable = [
        'employee_code','nid','type','user_name','from_date','to_date','total_days','from_session','to_session','updated_date',
    ];
}