<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use MongoDate;

class Chart extends Moloquent
{   
    protected $collection = 'report';

    protected $fillable = [
        'name', 'emp_code', 'att_date', 'checkin', 'checkout', 'worked', 'hrms_request_id',
    ];

    protected function _inputhoursCalculation($hours_calculations)
    {
        $seconds=0; 

        foreach ($hours_calculations as $key => $value) { 
            list($hour,$minute,$second) = explode(':', $value);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            $seconds += $second;
        }
         $temp = floor($seconds/3600);
         // If Single digit hours convert it into two digits
         $hours = sprintf("%02d", $temp);
         $seconds -= $hours*3600;
         $minutes  = floor($seconds/60);
         $seconds -= $minutes*60;
         $total_hours=$hours.":".sprintf("%02d", $minutes);

         return $total_hours;
    }

    protected function _workHours($employee_code,$first_day_currmonthyear,$last_day_currmonthyear)
    {
        try{
            $hours_calculations=array();
            $data = Chart::where('emp_code', '=', $employee_code)->whereBetween('att_date',[$first_day_currmonthyear,$last_day_currmonthyear])->get();
            
            if(count($data)!= 0){
                for($i=0;$i<count($data);$i++) {
                    $records = $data[$i]->getAttributes();
                    $dateRecord[$records['att_date']][] = $records;
                }

                foreach ($dateRecord as $key => $value) {
                    //If same date record is found than execute
                    if(count($value) > 1 )  
                    {   
                        for($i=0;$i<count($value);$i++){
                            $day=date("j",strtotime($key));
                            $day_word=date("D",strtotime($key));
                            $hours_calculations[$key][]=date("H:i:s",strtotime($value[$i]['worked']));
                        }  
                    }else{      
                        $day=date("j",strtotime($key));
                        $day_word=date("D",strtotime($key));
                        $temp=date("H:i:s",strtotime($value[0]['worked'])); 

                        // Check wheter comment parameter set or not, comment parameter is
                        // set in database through manyally in-out report, if comment parameter   // is set than manage it differently than present.
                        if(isset($value[0]['comment'])){
                            $present[$day]['time'] = 8;
                            $present[$day]['type'] = 'comment';
                            $present[$day]['dayname'] = $day_word;
                        }else{
                            //Execute if date is not same
                            $hours=date("G",strtotime($value[0]['worked']));    
                            $minutes=date("i",strtotime($value[0]['worked']));  
                            $time=$hours. '.' . $minutes;                       

                            $present[$day]['time'] = $time;
                            $present[$day]['type'] = 'working';
                            $present[$day]['dayname'] = $day_word;
                        }                        
                    }
                }  

                foreach ($hours_calculations as $key => $value) {
                      $day=date("j",strtotime($key));
                      $day_word=date("D",strtotime($key));
                      $total_hours=0;
                       for($i=0;$i<count($value);$i++)
                       {
                            $hour = $value[$i]; 
                            $total_hours =Chart::_inputhoursCalculation($value);

                       }  
                        $present[$day]['time'] = str_replace(":",".",$total_hours);
                        $present[$day]['type'] = 'working';
                        $present[$day]['dayname'] = $day_word; 

                }              
                return $present;
            }else{
                $present=array();
                return $present;
            }   
        }catch(Exception $e){
            echo "Exception in Working Hours".$e->getMessage();
        }
    }

    protected function _holiday($start_date,$end_date)
    {
        try{
            $holiday=array();
            $hoidays_data=holiday::whereBetween('Date',[$start_date,$end_date])->get();

            if(count($hoidays_data)!= 0){
                for($i=0;$i<count($hoidays_data);$i++) {
                    $day=date("j",strtotime($hoidays_data[$i]['Date']));
                    $day_word=date("D",strtotime($hoidays_data[$i]['Date']));

                    $holiday[$day]['time'] = 8;
                    $holiday[$day]['type'] = 'holiday';
                    $holiday[$day]['dayname'] = $day_word;
                }
            }
            
            return $holiday;
        }catch(Exception $e) {
            echo "Exception in Holiday".$e->getMessage();
        }
    }

    protected function _satsun($workingArray,$nmonth,$year)
    {
        try{
            $satsun=array();
            $begin  = new \DateTime(date($year.'-'.$nmonth.'-01'));
            $end    = new \DateTime(date($year.'-'.$nmonth.'-t'));

            while ($begin <= $end) 
            {
                if($begin->format("D") == "Sat" || $begin->format("D") == "Sun") 
                {
                    $day=$begin->format("j");
                    if(count($workingArray) != "0"){
                        foreach ($workingArray as $key => $value){
                            // Check is user is available on Staurday or Sunday if yes than that day not consider 
                            // as saturday or sunday
                            if($day != $key){
                                $day_word=$begin->format("D");
                                $satsun[$day]['time'] = 0;
                                $satsun[$day]['type'] = 'satsun';
                                $satsun[$day]['dayname'] = $day_word;
                                $satsun[$day]['type'] = 'satsun';        
                            }
                            break;
                        }
                    }else if(count($workingArray) == "0"){
                        $day_word=$begin->format("D");
                        $satsun[$day]['time'] = 0;
                        $satsun[$day]['type'] = 'satsun';
                        $satsun[$day]['dayname'] = $day_word;
                        $satsun[$day]['type'] = 'satsun';        
                    }
                    
                } 

                $begin->modify('+1 day');
            }
            return $satsun;
        }catch(Exception $e) {
            echo "Exception in Saturday Sunday".$e->getMessage();
        }
    }

    protected function _wfh($employee_code,$start_date,$end_date,$nmonth,$year)
    {
        try{
            $wfh=array();
            $workFromHome=emp_details::where('employee_code' , '=' , $employee_code)
                                    ->where('type' , '=' , "work_from_home")
                                    ->whereBetween('from_date',[$start_date,$end_date])
                                    ->whereBetween('to_date',[$start_date,$end_date])
                                    ->get();
            
            if(count($workFromHome)!="0"){
                foreach ($workFromHome as $key => $value) {
                    if($value->state == "Approved") {
                        if($value->from_date == $value->to_date) {
                            // Convert ISO Format date into date
                            $day=date("j",strtotime($value->from_date));
                            $day_word=date("D",strtotime($value->from_date));

                            $wfh[$day]['time'] = 8;
                            $wfh[$day]['type'] = 'wfh_leave_approved';
                            $wfh[$day]['dayname'] = $day_word;
                        }else if($value->from_date != $value->to_date) {
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));

                            for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                $day_word=date("D",strtotime($temp));
                                $wfh[$day]['time'] = 8;
                                $wfh[$day]['type'] = 'wfh_leave_approved';
                                $wfh[$day]['dayname'] = $day_word;
                            }
                        }
                    }
                }
            }
            return $wfh;
        }catch(Exception $e) {
            echo "Exception in Work From Home".$e->getMessage();
        }
    }

    protected function _wfc($employee_code,$start_date,$end_date,$nmonth,$year)
    {  
        try{
            $wfc=array();
            $workFromClient=emp_details::where('employee_code' , '=' , $employee_code)
                                    ->where('type' , '=' , "work_from_client_location")
                                    ->where('from_date','<=',$end_date)
                                    ->where('to_date','>=',$start_date)
                                    ->get();
            
            if(count($workFromClient) != "0")
            {
                foreach ($workFromClient as $key => $value) {
                    if($value->state == "Approved") {
                         if($value->from_date == $value->to_date){
                            // Convert ISO Format date into date
                                $day=date("j",strtotime($value->from_date));
                                $day_word=date("D",strtotime($value->from_date)); 
                                $wfc[$day]['time'] = 8;
                                $wfc[$day]['type'] = 'wfc_leave_approved';
                                $wfc[$day]['dayname'] = $day_word; 
                                                         
                        }else if($value->from_date != $value->to_date) {
                            // When From Date and To Date is not Match
                            $previousMonth=$nmonth;
                            $currentMonth=date('m');
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));
                            $from_date_month=date("m",strtotime($value->from_date));
                            $to_date_month=date("m",strtotime($value->to_date));
                            $currentDay=date('d',strtotime("-1 days"));
                            $from_date_numberof_days=date("t",strtotime($value->from_date));

                            // From Date Month is not same with to date month
                            if($from_date_month != $to_date_month){
                                // If passing month is same with current month
                                if($nmonth == $currentMonth){
                                    $from_date_day="1";
                                    for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                        $temp=date($year.'-'.$nmonth.'-'.$day);    
                                        $day_word=date("D",strtotime($temp));
                                        if($day_word != "Sat" && $day_word != "Sun") {
                                            $wfc[$day]['time'] = 8;
                                            $wfc[$day]['type'] = 'wfc_leave_approved';
                                            $wfc[$day]['dayname'] = $day_word;
                                        }
                                    }
                                }elseif($nmonth != $currentMonth){
                                    for($day=$from_date_day;$day<=$from_date_numberof_days;$day++){
                                        $temp=date($year.'-'.$previousMonth.'-'.$day);
                                        $day_word=date("D",strtotime($temp));
                                            if($day_word != "Sat" && $day_word != "Sun"){
                                                $wfc[$day]['time'] = 8;
                                                $wfc[$day]['type'] = 'wfc_leave_approved';
                                                $wfc[$day]['dayname'] = $day_word;
                                            }
                                        }
                                }    
                            }else if($from_date_month == $to_date_month){
                                if($nmonth == $currentMonth){
                                    for($day=$from_date_day;$day<=$to_date_day;$day++){
                                        $temp=date($year.'-'.$nmonth.'-'.$day);
                                        $day_word=date("D",strtotime($temp));
                                        if($day_word != "Sat" && $day_word != "Sun"){
                                            $wfc[$day]['time'] = 8;
                                            $wfc[$day]['type'] = 'wfc_leave_approved';
                                            $wfc[$day]['dayname'] = $day_word;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $wfc;
        }catch(Exception $e) {
            echo "Exception in Work From Client Location".$e->getMessage();
        }
    }

    protected function _leave($employee_code,$start_date,$end_date,$nmonth,$year)
    {
        try{
            $leave=array();
            $emp_leave_detail=emp_details::where('employee_code' , '=' , $employee_code)
                                    ->where('type','=','leave')
                                    ->where('state','=',"Approved")
                                    ->whereBetween('from_date',[$start_date,$end_date])
                                    ->whereBetween('to_date',[$start_date,$end_date])
                                    ->where('from_session', '=', 'Full Day')
                                    ->get();

            if(count($emp_leave_detail) != "0"){
                foreach ($emp_leave_detail as $key => $value) {
                    if($value->state == "Approved") {
                        if($value->from_date == $value->to_date) {
                            $day=date("j",strtotime($value->from_date));
                            $day_word=date("D",strtotime($value->from_date));

                            $leave[$day]['time'] = 8;
                            $leave[$day]['type'] = 'leave';
                            $leave[$day]['dayname'] = $day_word;
                        }else if($value->from_date != $value->to_date) {
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));

                            for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                $day_word=date("D",strtotime($temp));
                                if($day_word != "Sat" && $day_word != "Sun"){
                                $leave[$day]['time'] = 8;
                                $leave[$day]['type'] = 'leave';
                                $leave[$day]['dayname'] = $day_word;
                                }
                            }
                             
                        }
                    }
                }
            }
            return $leave;
        }catch(Exception $e) {
            echo "Exception in Employee Leave".$e->getMessage();
        }
    }

    protected function _hlfday($employee_code,$start_date,$end_date,$nmonth,$year)
    {
        try{
            $hlfDay=array();
            $leaveDays=array();
            $halfdayfinalData=array();
            $dates=array();
            
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
                            $day=date("j",strtotime($value->from_date));
                            $day_word=date("D",strtotime($value->from_date));

                            $hlfDay[$day]['time'] = 4;
                            $hlfDay[$day]['type'] = 'half_day';
                            $hlfDay[$day]['dayname'] = $day_word;
                        }else if($value->from_date != $value->to_date) {
                            // If From date and to date is not same
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));

                            if($value->from_session != "" && $value->to_session != ""){
                                $fromDate=date("Y-m-d",strtotime($value->from_date));
                                $toDate=date("Y-m-d",strtotime($value->to_date));

                                $dates[]=$fromDate;
                                $dates[]=$toDate;
                                $state="1";

                                $leaveDays=array();
                                for($day=$from_date_day+1;$day<$to_date_day;$day++) {
                                    $leaveDays[]=$day;
                                }
                            }else if($value->from_session != ""){
                                $day=date("j",strtotime($value->from_date));
                                $day_word=date("D",strtotime($value->from_date));

                                $hlfDay[$day]['time'] = 4;
                                $hlfDay[$day]['type'] = 'half_day';
                                $hlfDay[$day]['dayname'] = $day_word;
                                $fromSession="plus";
                                $state="0";
                                $leaveDays[]="1";
                            }else if($value->to_session != ""){
                                $day=date("j",strtotime($value->to_date));
                                $day_word=date("D",strtotime($value->to_date));

                                $hlfDay[$day]['time'] = 4;
                                $hlfDay[$day]['type'] = 'half_day';
                                $hlfDay[$day]['dayname'] = $day_word;
                                $toSession="minus";
                                $state="0";
                                $leaveDays[]="1";
                            }

                            if($state == "0"){
                                if(isset($fromSession)){
                                    for($day=$from_date_day+1;$day<=$to_date_day;$day++) {
                                        $day=date("j",strtotime($year.'-'.$nmonth.'-'.$day));
                                    $day_word=date("D",strtotime($year.'-'.$nmonth.'-'.$day));

                                        $hlfDayLeave[$day]['time'] = 8;
                                        $hlfDayLeave[$day]['type'] = 'leave';
                                        $hlfDayLeave[$day]['dayname'] = $day_word;
                                    }
                                }else if(isset($toSession)){
                                    for($day=$from_date_day;$day<$to_date_day;$day++) {
                                        $day=date("j",strtotime($year.'-'.$nmonth.'-'.$day));
                                    $day_word=date("D",strtotime($year.'-'.$nmonth.'-'.$day));

                                        $hlfDayLeave[$day]['time'] = 8;
                                        $hlfDayLeave[$day]['type'] = 'leave';
                                        $hlfDayLeave[$day]['dayname'] = $day_word;
                                    }
                                }
                            }else if($state == "1"){
                                foreach ($dates as $key => $value) {
                                    $day=date("j",strtotime($value));
                                    $day_word=date("D",strtotime($value));

                                    $hlfDay[$day]['time'] = 4;
                                    $hlfDay[$day]['type'] = 'half_day';
                                    $hlfDay[$day]['dayname'] = $day_word;
                                }

                                if(count($leaveDays) != "0"){
                                       foreach ($leaveDays as $key => $value) {
                                        $day=date("j",strtotime($year.'-'.$nmonth.'-'.$value));
                                        $day_word=date("D",strtotime($year.'-'.$nmonth.'-'.$value));

                                            $hlfDayLeave[$day]['time'] = 8;
                                            $hlfDayLeave[$day]['type'] = 'leave';
                                            $hlfDayLeave[$day]['dayname'] = $day_word;
                                        }
                                }
                            }
                        }
                    }
                }

                foreach ($hlfDay as $key => $value) {
                $halfdayfinalData[] = array(
                        'x' => $key,
                        'y' => (float)$value['time'],
                        'label' => $value['dayname'].', ' .$key,
                        'color' => "#c908D3"
                                    );
                }
                \View::share('hlfdata',json_encode($halfdayfinalData));
            }else{
                $halfdayfinalData=array();
                \View::share('hlfdata',json_encode($halfdayfinalData));
            }

            if(count($leaveDays) != "0"){
                session(['hlfDayLeave' => $hlfDayLeave]);
            }else{
                $hlfDayLeave=array();
                session(['hlfDayLeave' => $hlfDayLeave]);
            }
            
            return $hlfDay;
        }catch(Exception $e){
            echo "Exception in Half Day".$e->getMessage();
        }
    }

    protected function _absentDays($workingData,$nmonth,$year)
    {    
        try{
            $restDayArray=array();
            $currentMonthNumber=date('m');
            $currentYear=date('Y');

            $lastDay=cal_days_in_month(CAL_GREGORIAN,$nmonth,$year);
            for($i=1;$i<=$lastDay;$i++){
                $allDays[]=$i;
            }

            foreach ($workingData as $key => $value) {
                foreach ($allDays as $key1 => $value1) {
                    if($value1 = $key)
                    {
                        $available_days[]=$value1;
                        break;
                    }
                }
            }

            $result=array_diff($allDays,$available_days);

            if($nmonth < 6 && $year <= 2016){
                foreach ($result as $key => $day) {
                    $temp=date($year.'-'.$nmonth.'-'.$day);
                    $dayWithoutZero=date("j",strtotime($temp));
                    $day_word=date("D",strtotime($temp));

                    $workingData[$dayWithoutZero]['time'] = 0;
                    $workingData[$dayWithoutZero]['type'] = 'future_day';
                    $workingData[$dayWithoutZero]['dayname'] = $day_word;
                }
            }else{
                // Check if passing year is greater than the current year than consider all days of that month as future days.
                if($year > $currentYear){
                    foreach ($result as $key => $day) {
                        $temp=date($year.'-'.$nmonth.'-'.$day);
                        $dayWithoutZero=date("j",strtotime($temp));
                        $day_word=date("D",strtotime($temp));

                        $workingData[$dayWithoutZero]['time'] = 0;
                        $workingData[$dayWithoutZero]['type'] = 'future_day';
                        $workingData[$dayWithoutZero]['dayname'] = $day_word;
                    }
                }else if($nmonth > $currentMonthNumber){
                    // Check if passing month is greater than the current month than consider all days of that month as future days.
                    foreach ($result as $key => $day) {
                        $temp=date($year.'-'.$nmonth.'-'.$day);
                        $dayWithoutZero=date("j",strtotime($temp));
                        $day_word=date("D",strtotime($temp));

                        $workingData[$dayWithoutZero]['time'] = 0;
                        $workingData[$dayWithoutZero]['type'] = 'future_day';
                        $workingData[$dayWithoutZero]['dayname'] = $day_word;
                    }
                }else if($nmonth < $currentMonthNumber){
                    // Check if passing month is less than the current month than display absent days as absent days and remining days as per appropriate record.
                    foreach ($result as $key => $day) {
                        $temp=date($year.'-'.$nmonth.'-'.$day);
                        $dayWithoutZero=date("j",strtotime($temp));
                        $day_word=date("D",strtotime($temp));

                        // 
                        $day=date("d",strtotime($temp));
                        $yrmon= strtotime($temp);
                        $yrmonformat=date('M Y', $yrmon);
                        $day_word=date("D",strtotime($temp));
                        $restDayArray[$day]["fulldate"]=$day_word.', '.$day.' '.$yrmonformat;
                        // 

                        $workingData[$dayWithoutZero]['time'] = 8;
                        $workingData[$dayWithoutZero]['type'] = 'absent_days';
                        $workingData[$dayWithoutZero]['dayname'] = $day_word;
                    }
                }else{
                    // Get Current Date day
                    $currentDateDay=date('d');

                    foreach ($result as $key => $day) {
                        $temp=date($year.'-'.$nmonth.'-'.$day);
                        $dayWithoutZero=date("j",strtotime($temp));
                        $day_word=date("D",strtotime($temp));

                        // consider all the days future days which is greter than the current date day and no bar generated for that days.
                        // and the previous days than the current date day whoes entry is not available anywhere in table consider that days as absent
                        if($day < $currentDateDay){
                            // 
                            $day=date("d",strtotime($temp));
                            $yrmon= strtotime($temp);
                            $yrmonformat=date('M Y', $yrmon);
                            $day_word=date("D",strtotime($temp));
                            $restDayArray[$day]["fulldate"]=$day_word.', '.$day.' '.$yrmonformat;
                            // 

                            $workingData[$dayWithoutZero]['time'] = 8;
                            $workingData[$dayWithoutZero]['type'] = 'absent_days';
                            $workingData[$dayWithoutZero]['dayname'] = $day_word;
                        }else{
                            $workingData[$dayWithoutZero]['time'] = 0;
                            $workingData[$dayWithoutZero]['type'] = 'future_day';
                            $workingData[$dayWithoutZero]['dayname'] = $day_word;
                        }
                    }
                }
            }
        }catch(Exception $e) {
            echo "Exception in Rest of the day".$e->getMessage();
        }
        session(['restDayArray' => $restDayArray]);
        return $workingData;
    }

    protected function checkWorkingHours($inTime,$setTime)
    {
        if(strpos($setTime, 'PM') !== false) {
            $time=chop($setTime,"PM");
        }else if(strpos($setTime, 'AM') !== false) {
            $time=chop($setTime,"AM");
        }

        // If Intime is greter than set time
        if($inTime > $time."00"){
            $difference=(strtotime($inTime)-strtotime($time.":00"))/60;
        }else{
            $difference="0";
        }

        return $difference;
    }

    protected function _inoutReportData($employee_code,$first_day_currmonthyear,$last_day_currmonthyear,$nmonth,$year,$start_date,$end_date)
    {
        $boolManuallyInoutStatus=User::checkManuallyInpoutStatus();
        $inout_rpt_array=array();
        
        // Present Start
        try{
            $hours_calculations_inout = array();
            $inout_rpt_data = Chart::where('emp_code', '=', $employee_code)->whereBetween('att_date',[$first_day_currmonthyear,$last_day_currmonthyear])->get();

            if(count($inout_rpt_data) != "0"){
                for($i=0;$i<count($inout_rpt_data);$i++) {
                    $records = $inout_rpt_data[$i]->getAttributes();
                    $inputRecord[$records['att_date']][] = $records;
                }

                foreach ($inputRecord as $key => $value) {
                    if(count($value) > 1 ) {
                        for($i=0;$i<count($value);$i++){
                            $day=date("d",strtotime($key));
                            $yrmon= strtotime($key);
                            $yrmonformat=date('M Y', $yrmon);
                            $day_word=date("D",strtotime($key));
                            $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                            $hours_calculations_inout[$key][]=date("H:i:s",strtotime($value[$i]['worked']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'][] = date("H:i:s",strtotime($value[$i]['checkin']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'][] = date("H:i:s",strtotime($value[$i]['checkout']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'][] = date("H:i:s",strtotime($value[$i]['worked'])).":00";
                        }
                    }else{
                        $day=date("d",strtotime($key));
                        $yrmon= strtotime($key);
                        $yrmonformat=date('M Y', $yrmon);
                        $day_word=date("D",strtotime($key));
                        $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                         
                        $date=$key;
                        $settime=Worktime::where('employee_code','=',$employee_code)
                                ->where('fromdate','<=',$date)
                                ->where('todate','>=',$date)
                                ->where('state','=',"Approved")
                                ->get();

                        if(count($settime) != "0"){
                            $inout_rpt_array[$day][$isodateToNormaldate]['setWorktime'] = $settime[0]['settime'];

                            // Check Set time and In time difference and if intime is
                            // more than 30 minutes of set timr than display it with
                            // red color start
                            $inTime=date("H:i:s",strtotime($value[0]['checkin']));
                            $setTime=$settime[0]['settime'];                            

                            $difference=$this->checkWorkingHours($inTime,$setTime);

                            if($difference > 30){
                            $inout_rpt_array[$day][$isodateToNormaldate]['late'] = $difference;
                            }
                            // Check Set time and In time difference and if intime is
                            // more than 30 minutes of set timr than display it with
                            // red color end
                        }elseif(count($settime) == "0"){
                            $inout_rpt_array[$day][$isodateToNormaldate]['setWorktime'] = "09:30 AM";

                            // Check Set time and In time difference and if intime is
                            // more than 30 minutes of set timr than display it with
                            // red color start
                            $inTime=date("H:i:s",strtotime($value[0]['checkin']));
                            $setTime="09:30AM";                            

                            $difference=$this->checkWorkingHours($inTime,$setTime);

                            if($difference > 30){
                            $inout_rpt_array[$day][$isodateToNormaldate]['late'] = $difference;
                            }
                            // Check Set time and In time difference and if intime is
                            // more than 30 minutes of set timr than display it with
                            // red color end
                        }
                        $temp=date("H:i:s",strtotime($value[0]['worked'])); 

                        $hours=date("H",strtotime($value[0]['worked']));    
                        $minutes=date("i",strtotime($value[0]['worked']));  
                        $time=$hours. ':' . $minutes.":00";

                        // Check wheter comment parameter set or not, comment parameter is
                        // set in database through manyally in-out report, if comment parameter   // is set than manage it differently than present.
                        if(isset($value[0]['comment'])){
                            $comment=explode(",",$value[0]['comment']);

                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "00:00:00";
                        $inout_rpt_array[$day][$isodateToNormaldate]['type'] = $comment[0];
                        }else{
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = date("H:i:s",strtotime($value[0]['worked']));
                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = $time;
                            $inout_rpt_array[$day][$isodateToNormaldate]['type'] = "Present";
                        }
                    }
                }

                foreach ($hours_calculations_inout as $key => $value) {
                   $day=date("d",strtotime($key));
                   $yrmon= strtotime($key);
                   $yrmonformat=date('M Y', $yrmon);
                   $day_word=date("D",strtotime($key));
                   $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;
                    for($i=0;$i<count($value);$i++)
                    {
                        $total_hours=Chart::_inputhoursCalculation($value); 
                    }
                    $inout_rpt_array[$day][$isodateToNormaldate]['time'] = $total_hours.':00';
                    $inout_rpt_array[$day][$isodateToNormaldate]['type'] = "Present";
                }
            } 
        }catch(Exception $e) {
            echo "Exception in In Out Report".$e->getMessage();
        }
        // Present End

        // Holiday Start
        try{
            $hoidays_data=holiday::whereBetween('Date',[$start_date,$end_date])->get();

            if(count($hoidays_data) != "0"){
                for($i=0;$i<count($hoidays_data);$i++) {
                    $day=date("d",strtotime($hoidays_data[$i]['Date']));
                    $yrmon= strtotime($hoidays_data[$i]['Date']);
                    $yrmonformat=date('M Y', $yrmon);
                    $day_word=date("D",strtotime($hoidays_data[$i]['Date']));
                    $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                    $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                    $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                    $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "00:00:00";
                    $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "00:00:00";
                    $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Holiday';
                }
            }
        }catch(Exception $e) {
            echo "Exception in Holiday".$e->getMessage();
        }
        // Holiday End

        // Work From Home Start
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
                            $day=date("d",strtotime($value->from_date));
                            $yrmon= strtotime($value->from_date);
                            $yrmonformat=date('M Y', $yrmon);
                            $day_word=date("D",strtotime($value->from_date));
                            $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Work From Home';
                        }else if($value->from_date != $value->to_date) {
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));
                            for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                $day=date("d",strtotime($temp));
                                $yrmon= strtotime($temp);
                                $yrmonformat=date('M Y', $yrmon);

                                $day_word=date("D",strtotime($temp));
                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Work From Home';
                            }
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
                            $day=date("d",strtotime($value->from_date));
                            $yrmon= strtotime($value->from_date);
                            $yrmonformat=date('M Y', $yrmon);
                            $day_word=date("D",strtotime($value->from_date));
                            $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Work From Client';
                        }else if($value->from_date != $value->to_date) {
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));

                            for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                $day=date("d",strtotime($temp));
                                $yrmon= strtotime($temp);
                                $yrmonformat=date('M Y', $yrmon);
                                $day_word=date("D",strtotime($temp));
                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Work From Client';
                            }
                        }
                    }
                }
            }
        }catch(Exception $e) {
            echo "Exception in Work From Client Location".$e->getMessage();
        }
        // Work From Client End

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
                                    $day=date("d",strtotime($value->from_date));
                                    $yrmon= strtotime($value->from_date);
                                    $yrmonformat=date('M Y', $yrmon);
                                    $day_word=date("D",strtotime($value->from_date));
                                    $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;
                                    
                                    // Fetach working hours details form the report table start
                                    $empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $date.' 00:00:00')->get();

                                    if(count($empData) == "0"){
                                        $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                        $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                        $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                        $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                        $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                    }else if(count($empData) != "0"){
                                        $records = $empData[0]->getAttributes();
                                        $empDateRecord[$records['att_date']][] = $records;

                                        foreach ($empDateRecord as $key => $value) {
                                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
                                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
                                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = date("H:i:s",strtotime($value[0]['worked']));
                                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = date("H:i:s",strtotime($value[0]['worked']));
                                            $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Half Day';
                                        }    
                                    }
                                    // Fetach working hours details form the report table end
                                }else if($value->from_date != $value->to_date) {
                                    // If From date and to date is not same

                                    $from_date_day=date("j",strtotime($value->from_date));
                                    $to_date_day=date("j",strtotime($value->to_date));

                                    // Check in from session and to session boath value is set or either in from session and in to session value is set start
                                    if($value->from_session != "" && $value->to_session != ""){
                                        $fromDate=date("Y-m-d",strtotime($value->from_date));
                                        $toDate=date("Y-m-d",strtotime($value->to_date));

                                        $dates[]=$fromDate;
                                        $dates[]=$toDate;
                                        $state="1";

                                        $leaveDays=array();
                                        for($day=$from_date_day+1;$day<$to_date_day;$day++) {
                                            $leaveDays[]=$day;
                                        }

                                    }else if($value->from_session != ""){
                                        $date=date("Y-m-d",strtotime($value->from_date));
                                        $day=date("d",strtotime($value->from_date));
                                        $yrmon= strtotime($value->from_date);
                                        $yrmonformat=date('M Y', $yrmon);
                                        $day_word=date("D",strtotime($value->from_date));
                                        $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;
                                        $state="0";
                                        $fromSession="plus";
                                    }else if($value->to_session != ""){
                                        $date=date("Y-m-d",strtotime($value->to_date));
                                        $day=date("d",strtotime($value->to_date));
                                        $yrmon= strtotime($value->to_date);
                                        $yrmonformat=date('M Y', $yrmon);
                                        $day_word=date("D",strtotime($value->to_date));
                                        $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;
                                        $state="0";
                                        $toSession="minus";
                                    }
                                    // Check in from session and to session boath value is set or either in from session and in to session value is set end

                                    // If in boath from session and in to seesion value is set
                                    if($state == "1"){
                                        foreach ($dates as $key => $value) {
                                           $date=date("Y-m-d",strtotime($value));
                                           $day=date("d",strtotime($value));
                                           $yrmon= strtotime($value);
                                           $yrmonformat=date('M Y', $yrmon);
                                           $day_word=date("D",strtotime($value));
                                           $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                            $empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $value.' 00:00:00')->get();

                                            if(count($empData) == "0"){
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                            }else if(count($empData) != "0"){
                                                $records = $empData[0]->getAttributes();
                                                $empDateRecord[$records['att_date']][] = $records;   

                                                foreach ($empDateRecord as $key => $value) {
                                                    $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
                                                    $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
                                                    $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = date("H:i:s",strtotime($value[0]['worked']));
                                                    $inout_rpt_array[$day][$isodateToNormaldate]['time'] = date("H:i:s",strtotime($value[0]['worked']));
                                                    $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Half Day';
                                                }
                                            }
                                        }

                                        if(count($leaveDays) != "0"){
                                           foreach ($leaveDays as $key => $value) {
                                                $temp=date($year.'-'.$nmonth.'-'.$value);
                                                $day=date("d",strtotime($temp));

                                                $yrmon= strtotime($temp);
                                                $yrmonformat=date('M Y', $yrmon);
                                                $day_word=date("D",strtotime($temp));
                                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                           }
                                          
                                        }
                                    }else{
                                        // If in either from session or in to session value is set

                                        // Fetach working hours details form the report table start
                                        $empData = Chart::where('emp_code', '=', $employee_code)->where('att_date', '=', $date.' 00:00:00')->get();

                                        if(count($empData) == "0"){
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                        }else if(count($empData) != "0"){
                                            $records = $empData[0]->getAttributes();
                                            $empDateRecord[$records['att_date']][] = $records;

                                            foreach ($empDateRecord as $key => $value) {
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = date("H:i:s",strtotime($value[0]['checkin']));
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = date("H:i:s",strtotime($value[0]['checkout']));
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = date("H:i:s",strtotime($value[0]['worked']));
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = date("H:i:s",strtotime($value[0]['worked']));
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Half Day';
                                            }
                                            // Fetach working hours details form the report table end
                                        }

                                        // If in from session value is set than set from session date as half day and remaining day as leave
                                        if(isset($fromSession)){
                                            for($day=$from_date_day+1;$day<=$to_date_day;$day++) {
                                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                                $day=date("d",strtotime($temp));
                                                $yrmon= strtotime($temp);
                                                $yrmonformat=date('M Y', $yrmon);
                                                $day_word=date("D",strtotime($temp));
                                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                            }
                                        }else if(isset($toSession)){
                                            // If in to session value is set than set to session date as half day and remaining day as leave

                                            for($day=$from_date_day;$day<$to_date_day;$day++) {
                                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                                $day=date("d",strtotime($temp));
                                                $yrmon= strtotime($temp);
                                                $yrmonformat=date('M Y', $yrmon);
                                                $day_word=date("D",strtotime($temp));
                                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                                            }
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

        // Leave Start
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
                            $day=date("d",strtotime($value->from_date));
                            $yrmon= strtotime($value->from_date);
                            $yrmonformat=date('M Y', $yrmon);
                            $day_word=date("D",strtotime($value->from_date));
                            $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;

                            $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                            $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';
                        }else if($value->from_date != $value->to_date) {
                            $from_date_day=date("j",strtotime($value->from_date));
                            $to_date_day=date("j",strtotime($value->to_date));

                            for($day=$from_date_day;$day<=$to_date_day;$day++) {
                                $temp=date($year.'-'.$nmonth.'-'.$day);
                                $day=date("d",strtotime($temp));
                              
                                $yrmon= strtotime($temp);
                                $yrmonformat=date('M Y', $yrmon);
                                $day_word=date("D",strtotime($temp));
                                
                                $isodateToNormaldate=$day_word.', '.$day.' '.$yrmonformat;
                                 
                                $inout_rpt_array[$day][$isodateToNormaldate]['checkin'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['checkout'] = "00:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['worked'] = "08:00:00";
                                $inout_rpt_array[$day][$isodateToNormaldate]['time'] = "08:00:00";
                                if($day_word == "Sat")
                                {
                                $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Saturday';
                                }
                                elseif ($day_word == "Sun") {
                                     $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Sunday';
                                }
                                else{
                                     $inout_rpt_array[$day][$isodateToNormaldate]['type'] = 'Leave';   
                                }
                            }
                             
                        }
                    }
                }
            }
        }catch(Exception $e) {
            echo "Exception in Employee Leave".$e->getMessage();
        }        
        // Leave End

        // Saturday Sunday Start
        $begin  = new \DateTime(date($year.'-'.$nmonth.'-01'));
        $end    = new \DateTime(date($year.'-'.$nmonth.'-t'));
        
        while ($begin <= $end) 
        {
            $day=$begin->format("d");
            if(!array_key_exists($day,$inout_rpt_array)){
                if($begin->format("D") == "Sat" || $begin->format("D") == "Sun") 
                {
                    $day=$begin->format("d");
                    $day_word=$begin->format("D");
                    $isodateToNormaldate=date($day.'/'.$nmonth.'/'.$year);
                    $currentDate=date($year.'-'.$nmonth.'-'.$day);

                    if(date('w', strtotime($currentDate)) == 6){
                        $day1=date("d",strtotime($currentDate));
                        $yrmon= strtotime($currentDate);
                        $yrmonformat=date('M Y', $yrmon);
                        $day_word1=date("D",strtotime($currentDate));
                        $isodateToNormaldate=$day_word1.', '.$day1.' '.$yrmonformat;

                        // Check whether employee is available on saturday start
                        $status=Chart::where('emp_code', '=', $employee_code)
                               ->where('att_date','=',$currentDate." 00:00:00")
                               ->get();

                        if(count($status) == "0"){
                            $inout_rpt_array[$day1][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['worked'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['time'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['type'] = 'Saturday';
                        }
                        // Check whether employee is available on saturday end

                    }else if(date('w', strtotime($currentDate)) == 0){
                        $day1=date("d",strtotime($currentDate));
                        $yrmon= strtotime($currentDate);
                        $yrmonformat=date('M Y', $yrmon);
                        $day_word1=date("D",strtotime($currentDate));
                        $isodateToNormaldate=$day_word1.', '.$day1.' '.$yrmonformat;

                        // Check whether employee is available on sunday start
                        $status=Chart::where('emp_code', '=', $employee_code)
                               ->where('att_date','=',$currentDate." 00:00:00")
                               ->get();

                        if(count($status) == "0"){
                            $inout_rpt_array[$day1][$isodateToNormaldate]['checkin'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['checkout'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['worked'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['time'] = "00:00:00";
                            $inout_rpt_array[$day1][$isodateToNormaldate]['type'] = 'Sunday';
                        }
                        // Check whether employee is available on sunday end
                    }
                }
            }
            $begin->modify('+1 day');
        }
        // Saturday Sunday End

        // Rest Day Start
        foreach (session()->get('restDayArray') as $key => $value) {
            $fdate=$value['fulldate'];
            $inout_rpt_array[$key][$fdate]['checkin'] = "00:00:00";
            $inout_rpt_array[$key][$fdate]['checkout'] = "00:00:00";
            $inout_rpt_array[$key][$fdate]['worked'] = "00:00:00";
            $inout_rpt_array[$key][$fdate]['time'] = "00:00:00";
            $inout_rpt_array[$key][$fdate]['type'] = "Absent Day";
        }
        // Rest Day End

        if(count($inout_rpt_array) != "0"){
            ksort($inout_rpt_array); 
            return $inout_rpt_array;
        }else{
            $inout_rpt_array=array();
            return $inout_rpt_array;
        }
    }

    protected function _formatData($workingData)
    {
        ksort($workingData);

        foreach ($workingData as $key => $value) {
            if($value['type'] == "working"){
                $color="#1DA275";
            }else if($value['type'] == "holiday"){
                $color="#50A8D6";
            }else if($value['type'] == "satsun"){
                $color="#F3B718";
            }else if($value['type'] == "wfh_leave_approved"){
                $color="#F0B416";
            }else if($value['type'] == "wfc_leave_approved"){
                $color="#BBDD40";
            }else if($value['type'] == "absent_days"){
                $color="#333300";
            }else if($value['type'] == "leave"){
                $color="#FF6600";
            }else if($value['type'] == "half_day"){
                $color="#c908D3";
            }else  if($value['type'] == "future_day"){
                $color="#ffffff";
            }else if($value['type'] == "comment"){
                $color="#BDBDBD";
            }

            $finalData[] = array(
                                    'x' => $key,
                                    'y' =>(float)$value['time'],
                                    'label' =>  $value['dayname'].', '.$key,
                                    'color' => $color
                                  );
        } 
       
        return $finalData;
    }

}