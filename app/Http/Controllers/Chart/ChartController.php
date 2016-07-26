<?php
namespace App\Http\Controllers\Chart;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use MongoDate;
use App\Chart;
use App\holiday;
use App\wfh_details;
use App\wfc_details;
use App\Leaves;
use App\leave_details;
use App\User;
use App\Worktime;
use App\sendmail_detail;
use App\emp_details;
use Illuminate\Support\Facades\Input;
use Config;
use App\cron;

class ChartController extends Controller
{
	protected $month=array();

	function __construct()
    {
    	$this->month=array("1" => "Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
    }

    public function getRoleEmpCode()
    {
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

    	// If user is HR than allow to change id in the URL Start
	    if(in_array($accessRole,explode(",",$users_role[0]['role']))) {
	    	if(Input::get('id') != "") {
	    		// If in URL Id Parameter is set.
		    	$employee_code=Input::get('id');
	    	}else if(Input::get('id') == "") {
		    	$employee_code=$loggedin_empcode; 
		    	$_SESSION["id"] =$employee_code;
		    }
		}else if(!in_array($accessRole,explode(",",$users_role[0]['role']))) {	
	    	$employee_code=$loggedin_empcode; 
	    	$_SESSION["id"] =$employee_code;

		}  
		return $employee_code;
		// If user is HR than allow to change id in the URL End
    }

    public function chart($employee_code,$first_day_currmonthyear,$last_day_currmonthyear,$start_date,$end_date,$nmonth,$year)
    {
    	//.............................. Generating Chart Start........................
		$present=Chart::_workHours($employee_code,$first_day_currmonthyear,$last_day_currmonthyear);
	    $holiday=Chart::_holiday($start_date,$end_date);
		$wfh=Chart::_wfh($employee_code,$start_date,$end_date,$nmonth,$year);
		$wfc=Chart::_wfc($employee_code,$start_date,$end_date,$nmonth,$year);
		$leave=Chart::_leave($employee_code,$start_date,$end_date,$nmonth,$year);
		$hlfday=Chart::_hlfday($employee_code,$start_date,$end_date,$nmonth,$year);
		$hlfDayLeave=session()->get('hlfDayLeave');
	    $workingArray=($present + $holiday + $wfh + $wfc + $leave + $hlfday + $hlfDayLeave);
		$satsun=Chart::_satsun($workingArray,$nmonth,$year);
	    $combinedArray=($present + $holiday + $wfh + $wfc + $leave + $hlfday + $hlfDayLeave + $satsun);
		$workingData=Chart::_absentDays($combinedArray,$nmonth,$year);	
		ksort($workingData); 

		return $workingData;
		//.............................. Generating Chart End........................
    }

    public function renderChart(Request $request)
    { 
    	$monthNum = $request->input('month');
		$year = $request->input('year');
    	if(isset($monthNum) && isset($year)){ 
	    	foreach ($this->month as $key => $value) {
				if($key == $monthNum){
					$nextPrevMonthNumber=($key);
					$nextPrevMonthName=($this->month[$key]);
				}
			}

			$nmonth = sprintf("%02d", $nextPrevMonthNumber);

			// Get the Current date and convet it into ISO format start
	    	$first_day_currmonthyear = date($year.'-'.$nmonth.'-01');   
	        $last_day_currmonthyear  = date($year.'-'.$nmonth.'-t');

	        $start_date = new MongoDate(strtotime($first_day_currmonthyear));
	        $end_date = new MongoDate(strtotime($last_day_currmonthyear));
	         
	    	// Get the Current date and convet it into ISO format end

		}else if(!isset($monthNum) && !isset($year)){
			// Get the Current date and convet it into ISO format start
	    	$first_day_currmonthyear = date('Y-m-01');   
	        $last_day_currmonthyear  = date('Y-m-t');

	        $nmonth = date("m", strtotime($first_day_currmonthyear));
	        $year = date("Y", strtotime($first_day_currmonthyear));
	       
	        $start_date = new MongoDate(strtotime($first_day_currmonthyear));
	        $end_date = new MongoDate(strtotime($last_day_currmonthyear));
	    	// Get the Current date and convet it into ISO format end
		}

		// Check when click on chart button id is passed or not start
		if($request->input('id') != \Auth::user()->employee_code){
			$employee_code=$this->getRoleEmpCode();
			// $employee_code=$request->input('id');
		}else{
			$employee_code=$this->getRoleEmpCode();
		}
		// Check when click on chart button id is passed or not start  

    	//........................ Generating Chart Start........................
    	$workingData=$this->chart($employee_code,$first_day_currmonthyear,$last_day_currmonthyear,$start_date,$end_date,$nmonth,$year);
    	//.............................. Generating Chart End........................
	    
		//...............................In Out Report Start..............................
		$inout_rpt_array=Chart::_inoutReportData($employee_code,$first_day_currmonthyear,$last_day_currmonthyear,$nmonth,$year,$start_date,$end_date);
		//...............................In Out Report End..............................

		$data=Chart::_formatData($workingData);

 		// Routing Start
 		// This will be excuted when user click on next or previous
 		if(isset($monthNum) && isset($year)){
 			// Check URL and in URL if next is there than if condition will be executed
 			$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
 			
 			if(strpos($url,'next') !== false){
			    if(!empty($inout_rpt_array)){
				return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',$inout_rpt_array)->with('nextMonthName',$nextPrevMonthName)->with('nextYear',$year);
				}else{
					return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',array())->with('nextMonthName',$nextPrevMonthName)->with('nextYear',$year);
				}
			} 
			// Check URL and in URL if previous is there than else condition will be executed
			else{
			    if(!empty($inout_rpt_array)){
				return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',$inout_rpt_array)->with('previousMonthName',$nextPrevMonthName)->with('previousYear',$year);
				}else{
					return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',array())->with('previousMonthName',$nextPrevMonthName)->with('previousYear',$year);
				}	
			}
			// Check URL End
 		}
 		// This will be initial time
 		else{
			if(!empty($inout_rpt_array)){
				return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',$inout_rpt_array);
			}else{
				return view('chart/chart')->with('data',json_encode($data))->with('inout_rpt_data',array());
			}
		}
		// Routing End
	} //Fucntion End
} //Class End
