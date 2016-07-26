<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

class Leaves extends Moloquent
{
    protected $connection = 'mongodb';
	protected $collection = 'myleave';
	protected $dates = ['from_date', 'to_date'];
	protected $fillable = [
        'user_name', 'nid', 'leave_from', 'total_leavedays', 'reporting_manager', 'state', 'reason', 'leave_type', 'from_session', 'from_session_ampm', 'to_session', 'to_session_ampm', 'leave_user_id', 'reporting_employee',
    ];
}
