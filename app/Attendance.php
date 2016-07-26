<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use MongoDate;
use App\User;
use Illuminate\Support\Facades\Auth;

class Attendance extends Model
{
	protected function _totalDays($from_date,$to_date)
	{
		$totalDays=array();
		$firstDate   = new \DateTime(date($from_date));
		$lastDate    = new \DateTime(date($to_date));

		while ($firstDate <= $lastDate) 
		{
			$totalDays[]=$firstDate->format("j");
		    $firstDate->modify('+1 day');
		}
	
		return $totalDays; 
	}

    protected function _calSatsun($from_date,$to_date)
    {
    	$satsun=array();
    	$first_date  = new \DateTime(date($from_date));
		$last_date    = new \DateTime(date($to_date));

		while ($first_date <= $last_date) 
		{
		    if($first_date->format("D") == "Sat" || $first_date->format("D") == "Sun") 
		    {
		        $day=$first_date->format("j");
		        $satsun[]=$day;
		    }
		    $first_date->modify('+1 day');
		}

		return $satsun;
    }

    protected function _holidays($from_date,$to_date)
    {
    	$holiday=array();
    	$first_day_currmonthyear=date('Y-m-d', strtotime($from_date));
    	$last_day_currmonthyear=date('Y-m-d', strtotime($to_date));
    
	    $start_date = new \MongoDate(strtotime($first_day_currmonthyear));
	    $end_date = new \MongoDate(strtotime($last_day_currmonthyear));
	    
		$hoidays_data=holiday::whereBetween('Date',[$start_date,$end_date])->get();

		if(count($hoidays_data) != "0"){
			for($i=0;$i<count($hoidays_data);$i++) {
				$day=date("j",strtotime($hoidays_data[$i]['Date']));
				$holiday[]=$day;
			}
		}

		return $holiday;
    }

    protected function _workingHours($from_date,$to_date,$loggedinEmployeeCode)
    {
    	$first_day_currmonthyear=date('Y-m-d', strtotime($from_date));
	    $last_day_currmonthyear=date('Y-m-d', strtotime($to_date));

	    $loggedin_empcode=$loggedinEmployeeCode;	    
	    $a=0;

    	$data = Chart::where('emp_code', '=', $loggedin_empcode)->whereBetween('att_date',[$first_day_currmonthyear,$last_day_currmonthyear])->get();

    	if(count($data) != 0){
	    	$dateRecord = array();

	    	for($i=0;$i<count($data);$i++) {
		    	$records = $data[$i]->getAttributes();
		    	$dateRecord[$records['att_date']][] = $records;
			}
			
			foreach ($dateRecord as $key => $value) {
				//If same date record is found than execute
				if(count($value) > 1 )  
				{
					for($i=0;$i<count($value);$i++){
		    			$hours_calculations[]=date("H:i:s",strtotime($value[$i]['worked']));
					}
					$total_hours=Chart::_inputhoursCalculation($hours_calculations);
					
					$a+=str_replace(":",".",$total_hours);
				}else{  												
					//Execute if date is not same
					$hours=date("G",strtotime($value[0]['worked']));    
					$minutes=date("i",strtotime($value[0]['worked']));  
					$time=$hours. ':' . $minutes;                       

					$a+=str_replace(":",".",$time);
				}
			}
			$workinghours=$a;
			return $workinghours;
		}else{
			$workinghours="";
			return $workinghours;
		}
    }

    protected function _empData($from_date,$to_date,$loggedinEmployeeCode)
    {
    	$workingData=array();
    	// Convert date into ISO Format
    	$first_day_currmonthyear=date('Y-m-d', strtotime($from_date));
    	$last_day_currmonthyear=date('Y-m-d', strtotime($to_date));
	    
	    $start_date = new MongoDate(strtotime($first_day_currmonthyear));
	    $end_date = new MongoDate(strtotime($last_day_currmonthyear));

	    $employee_code=$loggedinEmployeeCode;

		// Working Hours Start
	    try{
	    	$data = Chart::where('emp_code', '=', $employee_code)->whereBetween('att_date',[$first_day_currmonthyear,$last_day_currmonthyear])->get();
	    	
	    	if(count($data) != "0"){
		    	$dateRecord = array();

		    	for($i=0;$i<count($data);$i++) {
			    	$records = $data[$i]->getAttributes();
			    	$dateRecord[$records['att_date']][] = $records;
				}
				
				foreach ($dateRecord as $key => $value) {
					//If same date record is found than execute
					if(count($value) > 1 ){
						$date=date("Y-m-d",strtotime($key));
						$day=date("j",strtotime($key));

						for($i=0;$i<count($value);$i++){
			    			$hours_calculations[]=date("H:i:s",strtotime($value[$i]['worked']));
			    			$minCheckIn=date("H:i:s",strtotime($value[0]['checkin']));
			    			$maxCheckOut=date("H:i:s",strtotime($value[$i]['checkout']));
						}

						$workingData[$date]['date'] =$date;
						$workingData[$date]['checkin'] = $minCheckIn;
						$workingData[$date]['checkout'] = $maxCheckOut;

						$total_hours=Chart::_inputhoursCalculation($hours_calculations);
						$workingData[$date]['totalHours'] = $total_hours;
						$workingData[$date]['type'] = 'present';
					}else{  												
						//Execute if date is not same
						$date=date("Y-m-d",strtotime($key));
						$day=date("j",strtotime($key));
						$temp=date("H:i:s",strtotime($value[0]['worked'])); 

						$hours=date("G",strtotime($value[0]['worked']));    
						$minutes=date("i",strtotime($value[0]['worked']));  
						$time=$hours. '.' . $minutes;                       

						$workingData[$date]['date'] = $date;
						$workingData[$date]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
						$workingData[$date]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
						$workingData[$date]['totalHours'] = $time;
						$workingData[$date]['type'] = 'present';
					}
				}
			}
		}catch(Exception $e){
			echo "Exception in Working Hours".$e->getMessage();
		}
		// Working Hours Complete

	    // Get Holiday Data from the database and store days of holiday in holiday_days array
	    try{
		    $hoidays_data=holiday::whereBetween('Date',[$start_date,$end_date])->get();

		    if(count($hoidays_data) != "0"){
				for($i=0;$i<count($hoidays_data);$i++) {
					$date=date("Y-m-d",strtotime($hoidays_data[$i]['Date']));
					$day=date("j",strtotime($hoidays_data[$i]['Date']));

					$workingData[$date]['date'] = $date;
					$workingData[$date]['checkin'] = "";
					$workingData[$date]['checkout'] = "";
					$workingData[$date]['totalHours'] = "8";
					$workingData[$date]['type'] = 'holiday';
					$workingData[$date]['holidayname'] = $hoidays_data[$i]['Title'];
				}
			}
		}catch(Exception $e) {
			echo "Exception in Holiday".$e->getMessage();
		}
		// Holiday End

		// Work From Home
		try{
		    $workFromHome=emp_details::where('employee_code' , '=' , $employee_code)
		    						   ->where('type','=','work_from_home')
		    						   ->whereBetween('from_date',[$start_date,$end_date])
		    						   ->whereBetween('to_date',[$start_date,$end_date])
	                                  ->get();

	        if(count($workFromHome) != "0"){
		        foreach ($workFromHome as $key => $value) {
					if($value->state == "Approved") {
						if($value->from_date == $value->to_date) {
							// Convert ISO Format date into date
							$date=date("Y-m-d",strtotime($value->from_date));
							$day=date("j",strtotime($value->from_date));

							$workingData[$date]['date'] = $date;
							$workingData[$date]['checkin'] = "";
							$workingData[$date]['checkout'] = "";
							$workingData[$date]['totalHours'] = "8";
							$workingData[$date]['type'] = 'wfh';
						}else if($value->from_date != $value->to_date) {
							// Check if from date month and to date month is same start
							$from_date_month=date("m",strtotime($value->from_date));
							$to_date_month=date("m",strtotime($value->to_date));

							if($from_date_month == $to_date_month){
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));
								for($day=$from_date_day;$day<=$to_date_day;$day++) {
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfh';
								}
							}else if($from_date_month != $to_date_month){
								$year=date('Y',strtotime($value->from_date));

								$daysFromMonth=cal_days_in_month(CAL_GREGORIAN,$from_date_month,$year);
								$daysToMonth=cal_days_in_month(CAL_GREGORIAN,$to_date_month,$year);
								
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));

								for($day=$from_date_day;$day<=$daysFromMonth;$day++){
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfh';
								}

								for($day=1;$day<=$to_date_day;$day++){
									$year=date('Y',strtotime($value->to_date));
									$month=date('m',strtotime($value->to_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfh';
								}
							}
							// Check if from date month and to date month is same end
						}
					}
				}
			}
		}catch(Exception $e) {
			echo "Exception in Work From Home".$e->getMessage();
		}
		// Work From Home End

		// Work From Client Start
		try{
		    $workFromClient=emp_details::where('employee_code' , '=' , $employee_code)
		    						   ->where('type','=','work_from_client_location')
		    						   ->whereBetween('from_date',[$start_date,$end_date])
		    						   ->whereBetween('to_date',[$start_date,$end_date])
	                                   ->get();

	        if(count($workFromClient) != "0"){
		        foreach ($workFromClient as $key => $value) {
					if($value->state == "Approved") {
						if($value->from_date == $value->to_date) {
							// Convert ISO Format date into date
							$date=date("Y-m-d",strtotime($value->from_date));
							$day=date("j",strtotime($value->from_date));

							$workingData[$date]['date'] = $date;
							$workingData[$date]['checkin'] = "";
							$workingData[$date]['checkout'] = "";
							$workingData[$date]['totalHours'] = "8";
							$workingData[$date]['type'] = 'wfc';
						}else if($value->from_date != $value->to_date) {
							// Check if from date month and to date month is same start
							$from_date_month=date("m",strtotime($value->from_date));
							$to_date_month=date("m",strtotime($value->to_date));

							if($from_date_month == $to_date_month){
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));

								for($day=$from_date_day;$day<=$to_date_day;$day++) {
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfc';
								}
							}else if($from_date_month != $to_date_month){
								$year=date('Y',strtotime($value->from_date));

								$daysFromMonth=cal_days_in_month(CAL_GREGORIAN,$from_date_month,$year);
								$daysToMonth=cal_days_in_month(CAL_GREGORIAN,$to_date_month,$year);
								
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));

								for($day=$from_date_day;$day<=$daysFromMonth;$day++){
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfc';
								}

								for($day=1;$day<=$to_date_day;$day++){
									$year=date('Y',strtotime($value->to_date));
									$month=date('m',strtotime($value->to_date));
									$temp=date($year.'-'.$month.'-'.$day);

									$workingData[$temp]['date'] = $temp;
									$workingData[$temp]['checkin'] = "";
									$workingData[$temp]['checkout'] = "";
									$workingData[$temp]['totalHours'] = "8";
									$workingData[$temp]['type'] = 'wfc';
								}
							}
							// Check if from date month and to date month is same end
						}
					}
				}
			}
		}catch(Exception $e) {
			echo "Exception in Work From Client Location".$e->getMessage();
		}
		// Work From Client End

		// Employee Leave
		try{
			$emp_leave_detail=emp_details::where('employee_code' , '=' , $employee_code)
									   ->where('type','=','leave')
		    						   ->whereBetween('from_date',[$start_date,$end_date])
		    						   ->whereBetween('to_date',[$start_date,$end_date])
		    						   ->where('from_session', '=', 'Full Day')
	                                   ->get();

	        if(count($emp_leave_detail) != "0"){
		        foreach ($emp_leave_detail as $key => $value) {
					if($value->state == "Approved") {
						if($value->from_date == $value->to_date) {
							$date=date("Y-m-d",strtotime($value->from_date));
							$day=date("j",strtotime($value->from_date));

							$workingData[$date]['date'] = $date;
							$workingData[$date]['checkin'] = "";
							$workingData[$date]['checkout'] = "";
							$workingData[$date]['totalHours'] = "8";
							$workingData[$date]['type'] = 'leave';
						}else if($value->from_date != $value->to_date) {
							// Check if from date month and to date month is same start
							$from_date_month=date("m",strtotime($value->from_date));
							$to_date_month=date("m",strtotime($value->to_date));

							if($from_date_month == $to_date_month){
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));

								for($day=$from_date_day;$day<=$to_date_day;$day++) {
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);
									$satsun=date('w', strtotime($temp));

									// Saturday and Sunday not consider in leave
									if($satsun != 6 && $satsun != 0) {
										$workingData[$temp]['date'] = $temp;
										$workingData[$temp]['checkin'] = "";
										$workingData[$temp]['checkout'] = "";
										$workingData[$temp]['totalHours'] = "8";
										$workingData[$temp]['type'] = 'leave';
									}
								}
							}else if($from_date_month != $to_date_month){
								$year=date('Y',strtotime($value->from_date));

								$daysFromMonth=cal_days_in_month(CAL_GREGORIAN,$from_date_month,$year);
								$daysToMonth=cal_days_in_month(CAL_GREGORIAN,$to_date_month,$year);
								
								$from_date_day=date("j",strtotime($value->from_date));
								$to_date_day=date("j",strtotime($value->to_date));

								for($day=$from_date_day;$day<=$daysFromMonth;$day++){
									$year=date('Y',strtotime($value->from_date));
									$month=date('m',strtotime($value->from_date));
									$temp=date($year.'-'.$month.'-'.$day);
									$satsun=date('w', strtotime($temp));

									// Saturday and Sunday not consider in leave
									if($satsun != 6 && $satsun != 0) {
										$workingData[$temp]['date'] = $temp;
										$workingData[$temp]['checkin'] = "";
										$workingData[$temp]['checkout'] = "";
										$workingData[$temp]['totalHours'] = "8";
										$workingData[$temp]['type'] = 'leave';
									}
								}

								for($day=1;$day<=$to_date_day;$day++){
									$year=date('Y',strtotime($value->to_date));
									$month=date('m',strtotime($value->to_date));
									$temp=date($year.'-'.$month.'-'.$day);
									$satsun=date('w', strtotime($temp));

									// Saturday and Sunday not consider in leave
									if($satsun != 6 && $satsun != 0) {
										$workingData[$temp]['date'] = $temp;
										$workingData[$temp]['checkin'] = "";
										$workingData[$temp]['checkout'] = "";
										$workingData[$temp]['totalHours'] = "8";
										$workingData[$temp]['type'] = 'leave';
									}
								}
							}
							// Check if from date month and to date month is same end
						}
					}
				}
			}
		}catch(Exception $e) {
			echo "Exception in Employee Leave".$e->getMessage();
		} 
		// Employee Leave End

		// Half Day Start
		try{
			$emp_halfday_leave_detail=emp_details::where('employee_code' , '=' , $employee_code)
										   ->where('type','=','leave')
			    						   ->whereBetween('from_date',[$start_date,$end_date])
			    						   ->whereBetween('to_date',[$start_date,$end_date])
		                                   ->get();

		    if(count($emp_halfday_leave_detail) != "0"){

			    foreach ($emp_halfday_leave_detail as $key => $value) {
						if($value->state == "Approved" && ($value->from_session == "Half Day" || $value->to_session == "Half Day")) {
								// If From date and to date is same
								if($value->from_date == $value->to_date) {
									$date=date("Y-m-d",strtotime($value->from_date));
									$day=date("j",strtotime($value->from_date));

									if($value->from_session != ""){	
										$firstSecondHalf=$value->from_session_ampm;
									}else if($value->to_session != ""){
										$firstSecondHalf=$value->to_session_ampm;
									}

								$workingData[$date]['date'] = $date;

								// Fetach working hours details form the report table start
								$empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $date)->get();

								// If Employee is on half day leave and not coming whole day than consider that day as leave start
								if(count($empData) == "0"){
									$workingData[$date]['date'] = $date;
									$workingData[$date]['checkin'] = "";
									$workingData[$date]['checkout'] = "";
									$workingData[$date]['totalHours'] = "8";
									$workingData[$date]['type'] = 'leave';
								}else if(count($empData) != "0"){
									$records = $empData[0]->getAttributes();
								    $empDateRecord[$records['att_date']][] = $records;

									foreach ($empDateRecord as $key => $value) {
										$workingData[$date]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
										$workingData[$date]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
										$workingData[$date]['totalHours'] = date("H:i:s",strtotime($value[0]['worked']));
									}
									// Fetach working hours details form the report table end

									$workingData[$date]['daystatus'] = $firstSecondHalf;
									$workingData[$date]['type'] = 'half_day';
								}
								// If Employee is on half day leave and not coming whole day than consider that day as leave End
							}else if($value->from_date != $value->to_date) {
								// If From date and to date is not same
								if($value->from_session != "" && $value->to_session != ""){
									$fromDate=date("Y-m-d",strtotime($value->from_date));
									$toDate=date("Y-m-d",strtotime($value->to_date));
									
									$from_date_day=date("j",strtotime($value->from_date));
									$to_date_day=date("j",strtotime($value->to_date));

									$frmoFirstSecondHalf=$value->from_session_ampm;
									$toFirstSecondHalf=$value->from_session_ampm;
								}else if($value->from_session != ""){
									$date=date("Y-m-d",strtotime($value->from_date));
									$day=date("j",strtotime($value->from_date));
									$firstSecondHalf=$value->from_session_ampm;
								}else if($value->to_session != ""){
									$date=date("Y-m-d",strtotime($value->to_date));
									$day=date("j",strtotime($value->to_date));
									$firstSecondHalf=$value->to_session_ampm;
								}

								// If in boath from session and to session value is set
								if(!empty($from_date_day) && !empty($to_date_day)){
									$dates[]=$fromDate;
									$dates[]=$toDate;

									$stat[]=$value->from_session_ampm;
									$stat[]=$value->to_session_ampm;

									foreach ($dates as $key => $value) {

										$empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $value)->get();

										// If Employee is on half day leave and not coming whole day than consider that day as leave start
										if(count($empData) == "0"){
											$workingData[$value]['date'] = $value;
											$workingData[$value]['checkin'] = "";
											$workingData[$value]['checkout'] = "";
											$workingData[$value]['totalHours'] = "8";
											$workingData[$value]['type'] = 'leave';
										}else if(count($empData) != "0"){
											$day=date("j",strtotime($value));

											$workingData[$value]['date'] = $value;
											$workingData[$value]['checkin'] = date("H:i:s",strtotime($empData[0]['checkin']));
											$workingData[$value]['checkout'] = date("H:i:s",strtotime($empData[0]['checkout']));
											$workingData[$value]['totalHours'] = date("H:i:s",strtotime($empData[0]['worked']));	
											$workingData[$value]['daystatus'] = $stat[$key];
											$workingData[$value]['type'] = 'half_day';
										}
										// If Employee is on half day leave and not coming whole day than consider that day as leave end
									}
								}else{
									// If in either from session or to session value is set
									$temp=date('Y-m-'.$day);

									$workingData[$date]['date_check'] = $date;

									// Fetach working hours details form the report table start
									$empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $date)->get();

									// If Employee is on half day leave and not coming whole day than consider that day as leave start
									if(count($empData) == "0"){
										$workingData[$date]['date'] = $date;
										$workingData[$date]['checkin'] = "";
										$workingData[$date]['checkout'] = "";
										$workingData[$date]['totalHours'] = "8";
										$workingData[$date]['type'] = 'leave';
									}else if(count($empData) != "0"){
										$records = $empData[0]->getAttributes();
								    	$empDateRecord[$records['att_date']][] = $records;

								    	foreach ($empDateRecord as $key => $value) {
											$workingData[$date]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
											$workingData[$date]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
											$workingData[$date]['totalHours'] = date("H:i:s",strtotime($value[0]['worked']));
										}
										// Fetach working hours details form the report table end 

										$workingData[$date]['daystatus'] = $firstSecondHalf;
										$workingData[$date]['type'] = 'half_day';
									}
								}
							}
						}
				}
			}
		}catch(Exception $e){
			echo "Exception in Half Day".$e->getMessage();
		}
		// Half Day End
		

		if(count($workingData) == "0"){
			$workingData=array();
			return $workingData;
		}else{
			ksort($workingData);
			return $workingData;	
		}
    }
}
