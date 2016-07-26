<?php

namespace App\Http\Controllers\worktime;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Worktime;
use App\User;
use Illuminate\Pagination\Paginator;

class WorktimeController extends Controller
{
    public function index()
    {
      	$worktime=array();
    	$loggedin_empcode = \Auth::user()->employee_code;
    	$_SESSION["id"] =$loggedin_empcode;

        $accessRole="HR"; 
        $loggedin_empcode = \Auth::user()->employee_code;

        // If user is HR than display dropdown Start 
        $users_role = User::where('employee_code', '=', $loggedin_empcode)->get(['role']);

        if(in_array('HR',explode(",",$users_role[0]['role'])) || in_array('Director',explode(",",$users_role[0]['role'])) || in_array('Delivery Head',explode(",",$users_role[0]['role']))) {
            $users_data=User::orderBy('name', 'asc')->get(['name','employee_code','role']);
            
           // Check whether loggedon user has HR role than display Manually In-Out Menu Start
            $data=User::checkManuallyInpoutStatus();
           // Check whether loggedon user has HR role than display Manually In-Out Menu End

            \View::share('users_data',$users_data);
        }
        // If user is HR than display dropdown End
        
        // Get Worktime data start
        $data=Worktime::orderBy('name','asc')->get();
        // Get Worktime data end

        if(count($data) != "0"){
	        foreach ($data as $key => $value) {
	        	$worktime[$value['employee_code']]["name"]=$value['name'];
	        	$worktime[$value['employee_code']]["email"]=$value['email'];
	        	$worktime[$value['employee_code']]["fromdate"]=date('d-m-Y',strtotime($value['fromdate']));
	        	$worktime[$value['employee_code']]["todate"]=date('d-m-Y',strtotime($value['todate']));
	        	$worktime[$value['employee_code']]["settime"]=$value['settime'];
	        	$worktime[$value['employee_code']]["state"]=$value['state'];
	        }
	    }

    	return view('worktime')->with('worktime',$worktime);
    }
}
