<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use MongoDate;

class Worktime extends Moloquent
{
    protected $connection = 'mongodb';
	protected $collection = 'worktime_details';
	protected $fillable = ['nid', 'employee_code', 'username', 'settime', 'fromdate', 'todate', 'state',
    ];
    public $timestamps = "false";
}
