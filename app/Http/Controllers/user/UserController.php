<?php
namespace App\Http\Controllers\user;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\report;
use App\Chart;
use App\SearchEmployee;
use DB;
use App\leave_details;
use App\wfh_details;
use App\wfc_details;
use App\sendmail_detail;
use Table;
use Session;
use MongoDate;
use Input;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\user\Flash;
use App\emp_details;

class UserController extends Controller
{  
   protected $all_record = array(); 
public function manuallyReport()
 {
  // Display Manually In Out Report Only HR Start
    $data=User::checkManuallyInpoutStatus();
  // Display Manually In Out Report Only HR End
    
        $this->hrMenu(); 
        $user=User::All();
        $user_name=array();
        foreach ($user as $key => $value) {
                $user_name[$value['employee_code']] = $value['name']; 
    } 
    return view('user/manuallyReport')->with('user_name',$user_name);
 }
public function dateFormat($date)
 {
    if(strlen($date) == "1" || strlen($date) == "2"){
        $hours = sprintf("%02d", $date);
        $date_time = $hours;
     }else{
         
        $str_arr = explode(':',$date);
        $pre=$str_arr[0]; 
        $post=$str_arr[1]; 
        $post1=$str_arr[2];
        $convertPre = sprintf("%02d", $pre);
        $convertPost = sprintf("%02d", $post);
        $convertPost1 = sprintf("%02d", $post1);
        $date_time = $convertPre.':'.$convertPost.':'.$convertPost1; 
     }
    return $date_time;
  }
public function manuallyReportInsert(Request $request)
{ 
  $post = $request->all();

  if(strlen($post['comment']) == 0)
  {
        $v = report::validate($post); 
         if( $v->fails() ) 
        {
          return redirect()->back()->withErrors($v->errors()); 
        }
        else
        {  
         $emp_code=$request->name; 
         $checkin=$request->checkin;
         $checkout=$request->checkout;  
          
         $user=User::where('employee_code','=',$emp_code)->get(); 
         $user_name = $user[0]['name'];  
         
        /*$checkin_time = $this->dateFormat($checkin); 
         $checkout_time=$this->dateFormat($checkout);
        echo $checkout_time;*/
           

          $final_date = str_replace("/", "-", $request->date);
         $att_date = date('Y-m-d', strtotime($final_date));
         $att_date_final=date('Y-m-d', strtotime($att_date)).' 00'.':00'.':00'; 

         $checin_date=$att_date.' '.$checkin;
         $checkout_date=$att_date.' '.$checkout; 

         $replaceCheckin = str_replace(":", ".",$checkin);
         $replaceCheckout = str_replace(":", ".",$checkout);  
         $worked= $replaceCheckout - $replaceCheckin;  

         if(strlen($worked) == "1" || strlen($worked) == "2"){
             
            $hours = sprintf("%02d", $worked);
            $worked_time = $hours.":00".":00"; 
         }else{ 

            $str_arr = explode('.',$worked);
            $pre=$str_arr[0]; 
            $post=$str_arr[1]; 

            $convertPre = sprintf("%02d", $pre);
            $convertPost = sprintf("%02d", $post);
            
            $worked_time = $convertPre.':'.$convertPost.':00';  
         } 

         $worked_date=$att_date.' '.$worked_time; 
         $data=array(
                'name' => $user_name,
                'emp_code' => $emp_code, 
                'att_date' => $att_date,
                'checkin' => $checin_date,
                'checkout' => $checkout_date,
                'worked' => $worked_date,
                'hrms_request_id' => " ",
            );
          
            $res = DB::table('report')->insert($data);   
             Session::flash('message', 'Inserted Successfully');
             return \Redirect::to('manually'); 
       
        }
    }
 else{
         $emp_code=$request->name; 
         $replaceDate = str_replace("/", "-", $request->date); 
         $att_date_final=date('Y-m-d', strtotime($replaceDate)).' 00'.':00'.':00';
         $comment=$request->comment;

         $user=User::where('employee_code','=',$emp_code)->get(); 
         $user_name = $user[0]['name'];

         $loggedin_empcode= \Auth::user()->employee_code; 
         $users_role=User::where('employee_code', '=', $loggedin_empcode)->get(['role']);

        if(in_array('Director',explode(",",$users_role[0]['role'])))
        {
            $role  = "Director";
        }
        elseif(in_array('HR',explode(",",$users_role[0]['role'])))
        {
            $role  = "HR";  
        }
        elseif (in_array('Delivery Head',explode(",",$users_role[0]['role']))) {
               $role  = "Delivery Head";  
        }else{
            $role  = "HR";
        } 

           $data=array(
                'name' => $user_name,
                'emp_code' => $emp_code, 
                'att_date' => date('Y-m-d', strtotime($att_date_final)),
                'checkin' =>  $att_date_final,
                'checkout' => $att_date_final,
                'worked' => $att_date_final,
                'comment'=>$comment.",".$role,
                'hrms_request_id' => " ",
            );

        $user_report_verify=DB::collection('report')
                            ->where('att_date','=',$att_date_final)
                            ->where('emp_code','=',$emp_code)
                            ->get();
        
             $res = DB::table('report')->insert($data);   
             Session::flash('message', 'Inserted Successfully');
             return \Redirect::to('manually');
         
}
 
 
}

public function hrMenu()
    {
     $loggedin_empcode= \Auth::user()->employee_code; 
     $_SESSION["id"] =$loggedin_empcode; 
     $users_role=User::where('employee_code', '=', $loggedin_empcode)->get(['role']);
     if(in_array('Director',explode(",",$users_role[0]['role'])) || in_array('HR',explode(",",$users_role[0]['role'])) || in_array('Delivery Head',explode(",",$users_role[0]['role']))) {
     $users_data=User::get(['name','employee_code','role']); 
     \View::share('users_data',$users_data);
    }

    else{
         echo "you are not HR...";
        } 
    }      
public function showStatus(Request $request)
 {
    // Display Manually In Out Report Only HR Start
    $data=User::checkManuallyInpoutStatus();
    // Display Manually In Out Report Only HR End

    $this->hrMenu();  
    $checkState=array(); 
    
    $year  = $request->fromDate;
    $month =  $request->toDate;
    // $name = $request->searchEmpName

    //$filterByDate variable is used when user search record based on calender
    $filterByDate  = $request->date;  

    if(isset($filterByDate)){
        $convertFilterByDate=date('Y-m-d',strtotime(str_replace("/","-",$filterByDate)));
    }else if(isset($year) && isset($month)){
        $start = date($year.'/'.$month.'/01');   
        $end = date($year.'/'.$month.'/t'); 

    }else if(!isset($year) && !isset($month)){
        $start = date('Y/m/01');   
        $end  = date('Y/m/t');
        
         $now = new \DateTime('now');
         $month = $now->format('m');
         $year = $now->format('Y');      
    }

    if(!isset($convertFilterByDate)){
        $replaceFrmDate = str_replace("/", "-", $start);
        $replaceToDate = str_replace("/", "-", $end);

        $fromDate=date('Y-m-d', strtotime($replaceFrmDate));
        $toDate=date('Y-m-d', strtotime($replaceToDate));
        $status=$request->status;

        if(isset($fromDate) && isset($toDate) && isset($status)){
            // Display records based on From Date and To Date
            if($status == "All"){
                $rec=sendmail_detail::whereBetween('reminder_date', [$fromDate, $toDate])
                                      ->where('action','=',"Absent")
                                      ->get();
            }else{
                $rec=sendmail_detail::whereBetween('reminder_date', [$fromDate, $toDate])
                                      ->where('status','=',$status)
                                      ->where('action','=',"Absent")
                                      ->get();
            }
            // This variable is used to determine in From date and To date which date is displayed
            $dateStatus="1";
        }else{
            // By default display current month records
            $currYear=date('Y');
            $currMonth=date('m');
            $combineYearMonth=$currYear."-".$currMonth;

            $rec=sendmail_detail::select('emp_code','name','reminder_date','status')
                                  ->where('reminder_date','LIKE',$combineYearMonth.'-%')
                                  ->where('status','=',"Pending")
                                  ->where('action','=',"Absent")
                                  ->get();

            // This variable is used to determine in From date and To date which date is displayed
            $dateStatus="0";
        }
    }else if(isset($convertFilterByDate)){
        $rec=sendmail_detail::where('reminder_date','=',$convertFilterByDate)
                                          ->where('action','=',"Absent")
                                          ->get();
        $dateStatus="0";
    }

    // Fetch Sate of employees from WFH, WFC, and Leave table and display state in
    // table appropriate column
    foreach ($rec as $key => $value) {
        $empCode=$rec[$key]['emp_code'];
        $date=$rec[$key]['reminder_date'];
        $isoDate = new MongoDate(strtotime($date));

        $wfh=emp_details::where('employee_code','=',$empCode)
                                    ->where('type','=','work_from_home')
                                    ->where('from_date','<=',$isoDate)
                                    ->where('to_date','>=',$isoDate)
                                    ->get();

        $wfc=emp_details::where('employee_code','=',$empCode)
                                ->where('type','=','work_from_client_location')
                                ->where('from_date','<=',$isoDate)
                                ->where('to_date','>=',$isoDate)
                                ->get();

        $leave=emp_details::where('employee_code','=',$empCode)
                                ->where('type','=','leave')
                                ->where('from_date','<=',$isoDate)
                                ->where('to_date','>=',$isoDate)
                                ->get();

        if(count($wfh) != "0"){
            $checkState[$key]['name']=$rec[$key]['name'];
            $checkState[$key]['emp_code']=$rec[$key]['emp_code'];
            $checkState[$key]['date']=$rec[$key]['reminder_date'];
            $checkState[$key]['wfh']=$wfh[0]['state'];
            $checkState[$key]['status']=$rec[$key]['status'];
        }else if(count($wfc) != "0"){
            $checkState[$key]['name']=$rec[$key]['name'];
            $checkState[$key]['emp_code']=$rec[$key]['emp_code'];
            $checkState[$key]['date']=$rec[$key]['reminder_date'];
            $checkState[$key]['wfc']=$wfc[0]['state'];
            $checkState[$key]['status']=$rec[$key]['status'];
        }else if(count($leave) != "0"){
            $checkState[$key]['name']=$rec[$key]['name'];
            $checkState[$key]['emp_code']=$rec[$key]['emp_code'];
            $checkState[$key]['date']=$rec[$key]['reminder_date'];
            $checkState[$key]['leave']=$leave[0]['state'];
            $checkState[$key]['status']=$rec[$key]['status'];
        }else{
            $checkState[$key]['name']=$rec[$key]['name'];
            $checkState[$key]['emp_code']=$rec[$key]['emp_code'];
            $checkState[$key]['date']=$rec[$key]['reminder_date'];
            $checkState[$key]['status']=$rec[$key]['status'];
        }
    }
    // $page = (int) $request->get('page',1);
    // $perPage = 15;
    // $offSet = ($page * $perPage) - $perPage;
    // $itemsForCurrentPage = array_slice($checkState, $offSet, $perPage, true);
    // $temp=new LengthAwarePaginator($itemsForCurrentPage, count($checkState), $perPage, $page, ['path' =>'/status']);

    return view('user/userstatus')->with('rec',$checkState)->with('dateStatus',$dateStatus);
    }

    public function updateStatus(Request $request)
    {
        $empcode=$request->empcode;
        $status=$request->leavestatus;
        $comment=$request->comment;

        // Convert Date into Y-m-d
        $var = $request->date1;
        $date = str_replace('/', '-', $var);
        $conditionDate=date('Y-m-d', strtotime($date)); 
        // Update in Send Mail Log
        $update_rec=sendmail_detail::where('emp_code','=',$empcode)
                                   ->where('cron_date','=',$conditionDate)
                                   ->update(['status' => $status,'comment' => $comment]); 
        // Redirec to /status route after updating the record
        return \Redirect::to('/status');
    } 
public function show(Request $request)
{
    $this->hrMenu(); 
    $name = $request->input('searchval');
    if (!empty($name)) {
        $data = [];            
        $data['^a-zA-Z0-9'] = $name;
        $searchEmployee = new SearchEmployee();         
        $users = $searchEmployee->search($data);         
    } else { 
        $users = User::sorted()->paginate(15); 
    } 
    $table = Table::create($users,['employee_code','name','email']); 
    $table->addColumn('','Action', function($result) {
                        return "<a class='btn btn-primary pull-left' href='calender?id=".$result->employee_code."'>Report</a>";
            }); 
    return view('home', ['table' => $table]); 
}


public function employee_report(Request $request)
{

    // Display Manually In Out Report Only HR Start
    $data=User::checkManuallyInpoutStatus();
    // Display Manually In Out Report Only HR End

     $year=$request->Input('year');
     $month=$request->Input('month'); 
  
    if(isset($year) && isset($month)){
        $start = date($year.'-'.$month.'-01');   
        $end = date($year.'-'.$month.'-t'); 

    }else if(!isset($year) && !isset($month)){
        $start = date('Y-m-01');   
        $end  = date('Y-m-t');
        
         $now = new \DateTime('now');
         $month = $now->format('m');
         $year = $now->format('Y');
          
    }  
    $all_record =  $this->get_all_detail_report_data($start,$end); 
    
    $page = (int) $request->get('page',1);
    $perPage = 15;
    $offSet = ($page * $perPage) - $perPage;
    $itemsForCurrentPage = array_slice($all_record, $offSet, $perPage, true);
    $temp=new LengthAwarePaginator($itemsForCurrentPage, count($all_record), $perPage, $page, ['path' =>'/monthBy?year='.$year.'&month='.$month]);
           
   return view('monthBy')->with('all_record',$temp)->with('year',$year)->with('month',$month); 
}
 protected function inputhoursCalculation($hours_calculations)
    {
        $seconds=0; 

        foreach ($hours_calculations as $key => $value) { 
            list($hour,$minute) = explode(':', $value);
            $seconds += $hour*3600;
            $seconds += $minute*60;
            
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

public function get_all_detail_report_data($start,$end)
{  
   $start_leave = new MongoDate(strtotime($start));
   $end_leave = new MongoDate(strtotime($end));

    $this->hrMenu(); 
    $user=User::All();
    $name_code=array();
    foreach ($user as $key => $value) {
          $name_code[]=[
                    $value['employee_code'],
                    $value['name']
                ];
    }
     
    $count_user=count($name_code);
    $time=array();
    $leave_array=array();
for($i=0;$i<$count_user;$i++) 
  {
    $total_hours= DB::collection('report')
                ->where('emp_code','=',$name_code[$i][0])
                ->whereBetween('att_date',[$start,$end])
                ->get();

     foreach ($total_hours as $key => $value) {
                       $hours=date("G",strtotime($value['worked']));  
                       $minutes=date("i",strtotime($value['worked'])); 
                       $time[$value['emp_code']][]=$hours. ":" . $minutes;
                }
   $leave=emp_details::where('employee_code','=',$name_code[$i][0])
            ->where('type','=','leave')
            ->where('state','=',"Approved")
            ->whereBetween('from_date',[$start_leave,$end_leave])
            ->whereBetween('to_date',[$start_leave,$end_leave])
            ->get(); 
         foreach ($leave as $key => $value) {
                    $leave_array[$value['employee_code']][]=$value['total_days'];
         }
}
 
    foreach ($user as $key => $value) {
     $user_array[$value['employee_code']]=$value['name'];
    } 
    $hours=array();
    $seconds=0;
    $h=0;
    $m=0; 
    $hhm1=array();
    $all_record=array(); 
         
    foreach ($user_array as $key => $value) {
            $all_record[$key]['name']=$value;
        }

    foreach ($time as $key => $value) { 
      $count=count($value);
      if($count>1){ 
        $hour = $this->inputhoursCalculation($value);
          $all_record[$key]['hrs'] =$hour; 
       }
      else{ 
       foreach ($value as $key1 => $value1) {
           $all_record[$key]['hrs'] = $value1;
          }
        } 
    }
 
 //leave//
    
    foreach ($leave_array as $key => $value) {
             $all_record[$key]['leave']=count($value);
    }

 //other//
   $leave_other=emp_details::whereBetween('from_date',[$start_leave,$end_leave])
                            ->where('type','=','leave')
                            ->get();
   $leave_other_array=array();
    foreach ($leave_other as $key => $value) {
        $leave_other_array[]=$value['employee_code'];
           
    }
     $wfh_other=emp_details::whereBetween('from_date',[$start_leave,$end_leave])
                            ->where('type','=','work_from_home')
                            ->get();

     $wfh_other_array=array();
     foreach ($wfh_other as $key => $value) {
        $wfh_other_array[]=$value['employee_code'];
        
     }
         $diff1=array_diff($leave_other_array,$wfh_other_array);
         $difference1 = array_merge(array_diff($leave_other_array,$wfh_other_array), array_diff($wfh_other_array, $leave_other_array));
         $difference1_unique=array_unique($difference1);
         
         $wfc_other=emp_details::whereBetween('from_date',[$start_leave,$end_leave])
                                ->where('type','=','work_from_client_location')
                                ->get();

         $wfc_other_array=array();
         foreach ($wfc_other as $key => $value) {
            $wfc_other_array[]=$value['employee_code'];
        }
        $report_other=DB::collection('report')
                     ->whereBetween('att_date',[$start,$end])   
                    ->get(); 
        $report_other_array=array();
        foreach ($report_other as $key => $value) {
                $report_other_array[]=$value['emp_code'];
        } 

        $difference2 = array_merge(array_diff($wfc_other_array,$report_other_array), array_diff($report_other_array, $wfc_other_array));
        $difference2_unique=array_unique($difference2);
        $difference3 = array_merge(array_diff($difference1_unique,$difference2_unique), array_diff($difference2_unique, $difference1_unique));
        $difference3_unique=array_unique($difference3);

       foreach ($name_code as $key => $value) {
             
             if(empty($difference3_unique))
             {

                 $all_record[$value[0]]['other']="Other";
             }
             else
             {
            foreach ($difference3_unique as $key1 => $value1) { 
                    if($value == $value1)
                    {
                         $all_record[$value[0]]['other']="Other";
                    } 
              }
            }
       } 
       
       return $all_record;
    }

public function searchName($id,$year,$month)
{  
    $data = array();
     if(isset($year) && isset($month)){

        $start = date($year.'-'.$month.'-01');   
        $end = date($year.'-'.$month.'-t'); 
    } 
      $all_record =  $this->get_all_detail_report_data($start,$end);  
      foreach ($all_record as $key => $value) {
           if($key == $id)
           { 

                $data['name']=$value['name'];
                 if(isset($value['hrs']))
                 {
                    $data['hrs'] = $value['hrs'];
                 } 
                 else
                 {
                    $data['hrs'] = "0";
                 }  
                
                if(isset($value['leave']))
                 {
                    $data['leave'] = $value['leave'];
                 } 
                 else
                 {
                    $data['leave'] = "None";
                 } 
                 if(isset($value['other']))
                 {
                    $data['other'] = "other";
                 } 
                 else
                 {
                    $data['other'] = "None";
                 }  
           }
      }
        return $data;
          
}

   

    public function print_report(Request $request)
    {   
           $year=$request['year'];
           $month=$request['month'];
           $start = date($year.'-'.$month.'-01');   
           $end = date($year.'-'.$month.'-t'); 
           $all_record=$this->get_all_detail_report_data($start,$end);  
           return view('print')->with('all_record',$all_record)->with('year',$year)->with('month',$month);
    }
    function date_range($first, $last, $step = '+1 day', $output_format = 'd/m/Y' ) {

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);
    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }
   return $dates;
}
function getDatesFromRange($start, $end) {
    $interval = new \DateInterval('P1D');
    $realEnd = new \DateTime($end);
    $realEnd->add($interval);

    $period = new \DatePeriod(
         new \DateTime($start),
         $interval,
         $realEnd
    );
 
    foreach($period as $date) { 
        $array[] = $date->format('Y-m-d'); 
    }

    return $array;
}
 
public function day_report_data()
{ 
    // Display Manually In Out Report Only HR Start
    $data=User::checkManuallyInpoutStatus();
    // Display Manually In Out Report Only HR End

      $this->hrMenu(); 
      $current_date=date('Y-m-d');
      $y  = date('Y',strtotime($current_date));
      $m = date('m',strtotime($current_date));  
      $year = isset($_REQUEST['year'])?$_REQUEST['year'] : $y;
      $month =  isset($_REQUEST['month'])?$_REQUEST['month'] : $m; 

        $start = date($year.'-'.$month.'-01');   
        $end = date($year.'-'.$month.'-t'); 

        $start = date($year.'-'.$month.'-01');   
        $end = date($year.'-'.$month.'-t'); 
        $start_leave = new MongoDate(strtotime($start));
        $end_leave = new MongoDate(strtotime($end));
         $user=User::All();
         $name_code=array();
            foreach ($user as $key => $value) {
                  $name_code[]=[
                            $value['employee_code'],
                            $value['name']
                        ];
            }

           $count_user=count($name_code); 
           $time=array();
           $leave=array();
           $w_f_c_array=array();
           $w_f_h_array=array();
         for($i=0;$i<$count_user;$i++) 
            {
                $total_work_hours = DB::collection('report')
                            ->where('emp_code','=',$name_code[$i][0])
                            ->whereBetween('att_date',[$start,$end]) 
                            ->get();

                foreach ($total_work_hours as $key => $value) {  
                               
                               $hours=date("G",strtotime($value['worked']));  
                               $minutes=date("i",strtotime($value['worked'])); 
                               $seconds=date("i",strtotime($value['worked'])); 
                               $time[$name_code[$i][1]][$value['att_date']][]=$hours. ":" . $minutes.":".$seconds;
                        } 
                $w_f_c = DB::collection('emp_details')
                            ->where('type','=','work_from_client_location')
                            ->where('employee_code','=',$name_code[$i][0])
                            ->where('state','=',"Approved")
                            ->whereBetween('from_date',[$start_leave,$end_leave]) 
                            ->whereBetween('to_date',[$start_leave,$end_leave]) 
                            ->get(); 
                  
                  foreach ($w_f_c as $key => $value) {

                                 $from_date = date('Y-m-d',$value['from_date']->sec);
                                 $to_date = date('Y-m-d',$value['to_date']->sec); 
                                 $dates =$this->getDatesFromRange($from_date,$to_date); 
                                 foreach ($dates as $key => $value) {
                                      $w_f_c_array[$name_code[$i][1]][$value][]='WFC';

                                 }  
                         }

                $w_f_h = DB::collection('emp_details')
                            ->where('type','=','work_from_home')
                            ->where('employee_code','=',$name_code[$i][0])
                            ->where('state','=',"Approved")
                            ->whereBetween('from_date',[$start_leave,$end_leave]) 
                            ->whereBetween('to_date',[$start_leave,$end_leave]) 
                            ->get();
                  foreach ($w_f_h as $key => $value) {
                                 $from_date = date('Y-m-d',$value['from_date']->sec);
                                 $to_date = date('Y-m-d',$value['to_date']->sec); 
                                 $dates =$this->getDatesFromRange($from_date,$to_date); 
                                 foreach ($dates as $key => $value) {
                                      $w_f_h_array[$name_code[$i][1]][$value][]='WFH'; 
                                 }  
                         }    
                $leave_array_day = emp_details::where('employee_code','=',$name_code[$i][0])
                                                 ->where('type','=','leave')
                                                 ->where('state','=',"Approved")
                                                 ->whereBetween('from_date',[$start_leave,$end_leave]) 
                                                 ->get();

                foreach ($leave_array_day as $key => $value) {
                   $from_date =  date('Y-m-d',strtotime($value['from_date']));
                   $to_date =  date('Y-m-d',strtotime($value['to_date']));
                   $dates =$this->getDatesFromRange($from_date,$to_date); 
                    foreach ($dates as $key => $value) {
                         $date = strtotime($value);
                         $date = date("l", $date);
                         $date = strtolower($date); 
                         if($date != "saturday" && $date != "sunday") { 
                                 $leave[$name_code[$i][1]][$value][]='Leave';
                            } 
                                       
                        }  
                   } 
            } 
      $total_hours_day=array();
        foreach ($time as $key => $value) {
             foreach ($value as $key1 => $value1) {
                    if(count($value1) == 1)
                    {
                        list($hour,$minute,$second) = explode(':', $value1[0]);
                        $hour_single = sprintf("%02d", $hour).':'.$minute; 
                        $total_hours_day[$key][$key1][]=$hour_single; 

                    }
                    else{
                    for($i=0;$i<count($value1);$i++)
                          { 
                               $hour = Chart::_inputhoursCalculation($value1); 
                          }
                            $total_hours_day[$key][$key1][]=$hour; 
                        }   
                }
                 
        } 

         foreach ($leave as $key => $value) { 
             foreach ($value as $key1 => $value1) { 
                   $total_hours_day[$key][$key1." 00:00:00"][]= 'Leave';
             }
        }
           foreach ($w_f_c_array as $key => $value) { 
             foreach ($value as $key1 => $value1) { 
                   $total_hours_day[$key][$key1." 00:00:00"][]='WFC';
             }
        }
        foreach ($w_f_h_array as $key => $value) { 
             foreach ($value as $key1 => $value1) { 
                   $total_hours_day[$key][$key1." 00:00:00"][]='WFH';
             }
        }


        $d=cal_days_in_month(CAL_GREGORIAN,$month,$year);
        $date=array();
        for($i=1;$i<=$d;$i++)
        {
             $num_padded = sprintf("%02d", $i);
             $date[$year.'-'.$month.'-'.$num_padded." 00:00:00"] = "-"; 
        }  
        $all_record_report_day=array();
        foreach ($date as $key => $value) {
            foreach ($total_hours_day as $key1 => $value1) { 
                      foreach ($value1 as $key2 => $value2) {
                           if($key2 == $key)
                           {
                             $all_record_report_day[$key1][$key2]= $value2 ;
                           }
                           else
                             {
                                $all_record_report_day[$key1][$key][]= $value ;
                             }
                      }
                } 
        }  
         
         return view('day_report')->with('all_record_report_day',$all_record_report_day)->with('month',$month)->with('year',$year)->with('name_code',$name_code);
         
    }
 

 }