<?php

namespace App;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class cron extends Eloquent 
{
	protected $collection = 'cron';

	protected $fillable = [
        'cron_time', 'cron_type',
    ];

}