<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use MongoDate;

class emp_details extends Moloquent
{
    protected $connection = 'mongodb';
    protected $collection = 'emp_details';
    protected $dates = ['from_date', 'to_date'];
	protected $fillable = [
        'employee_code','nid','type','user_name','from_date','to_date','total_days','from_session','to_session','updated_date',
    ];
}
