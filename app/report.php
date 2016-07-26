<?php

namespace App;
use Jenssegers\Mongodb\Model as Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
class report extends Model
{
       protected $collection = 'report';
       protected $fillable = [
        'name', 'emp_code', 'att_date','checkin','checkout','worked','hrms_request_id',
     ];

    protected function temp()
    {
    	$curl_post_data = array(
          'name' => 'priyanka',
          'emp_code' => '10101027',
          'att_date' =>  'January 4, 2016 11:18 PM',
          'checkin' =>  'March 1, 2016 9:18 AM',
          'checkout' => 'March 1,2016 6:35 PM',
          'worked' => 'March 1,2016 6:35 PM',
          'hrms_request_id' => ''
  			);
    		$a=json_encode($curl_post_data);
    		return $a;
    }

      public static function validate($post) {
                $rules = array(
                       'name' => 'Required',
                       'checkin' => 'Required',
                       'checkout' => 'Required',
                ); 
                return Validator::make($post, $rules);
            }

}