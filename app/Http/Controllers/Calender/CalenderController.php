<?php
namespace App\Http\Controllers\Calender;
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
use App\user;
use App\emp_details;

class CalenderController extends Controller
{ 
  public function calender(Request $request)
  {  
    $accessRole="HR"; 
     // Check when click on calender button id is passed or not start
    if($request->input('id') != \Auth::user()->employee_code){
      $id=$request->input('id');
    }else{
      $id=\Auth::user()->employee_code;
    }
    // Check when click on calender button id is passed or not start  
     $loggedin_empcode = \Auth::user()->employee_code;
     $_SESSION["id"] =$loggedin_empcode; 
 
     $users_role=User::where('employee_code', '=', $loggedin_empcode)->get(['role']);
     if(in_array('HR',explode(",",$users_role[0]['role'])) || in_array('Director',explode(",",$users_role[0]['role'])) || in_array('Delivery Head',explode(",",$users_role[0]['role']))) {
       $users_data=User::orderBy('name', 'asc')->get(['name','employee_code','role']);
       
       // Check whether loggedon user has HR role than display Manually In-Out Menu Start
        $data=User::checkManuallyInpoutStatus();
       // Check whether loggedon user has HR role than display Manually In-Out Menu End
        \View::share('users_data',$users_data);
     } 
     else if(!in_array($accessRole,explode(",",$users_role[0]['role']))) {  
        $employee_code=$loggedin_empcode;
        $id=\Auth::user()->employee_code;
        $status = 1; 
        /*$_SESSION["id"] =$employee_code;*/
    }  

    if(!isset($users_data)){
    // If in the query string Id parameter value is set
    if(isset($_REQUEST['id'])){
      if($_REQUEST['id'] != \Auth::user()->employee_code){
        $url=str_replace($_REQUEST['id'],\Auth::user()->employee_code,$_SERVER['REQUEST_URI']);
      }else if($_REQUEST['id'] == \Auth::user()->employee_code){
        $url="1";
        \View::share('url',$url);
      }
    }
    else if(!isset($_REQUEST['id'])){
      $url="1";
      \View::share('url',$url);
    }
   }
      else if(isset($users_data)){
       $url="1";
       \View::share('url',$url);
    }
    return view('calender.calender')->with('id',$id);
 }
public function showCalender($alldate_array=array(),$alldate_array1=array(),$holiday1=array(),$leave_array=array(),$work_from_array=array(),$work_from_client_array=array(),$absent_day_array=array(),$comment_array=array())
  {  
     $event_array = array();
     foreach ($holiday1 as $key => $value) {
        $record[0]["title"]="Holiday";
        $record[0]["id"]="1";
        $record[0]["description"]=$value;
        $event_array[] = array(
          'id' => $record[0]['id'],
          'title' => $record[0]['title'],
          'start' => $key,
          'end'   => $key,
           'description'=>$record[0]["description"],
          'allDay'=> false,
          'backgroundColor'=>'#50A8D6'
          );
     }

     foreach ($comment_array as $key => $value) { 
      $comment = explode(',', $value);
      if(isset($commnet[1]))
      {
        $record[0]["title"]=$comment[0];
        $record[0]["id"]="1";
        $record[0]["description"]="Comment By ".$comment[1];
        $event_array[] = array(
          'id' => $record[0]['id'],
          'title' => $record[0]['title'],
          'start' => $key,
          'end'   => $key,
           'description'=>$record[0]["description"],
          'allDay'=> false,
          'backgroundColor'=>'#BDBDBD'
          );
        }
        else{
          $record[0]["title"]=$comment[0];
        $record[0]["id"]="1";
        $record[0]["description"]="Comment By HR";
        $event_array[] = array(
          'id' => $record[0]['id'],
          'title' => $record[0]['title'],
          'start' => $key,
          'end'   => $key,
           'description'=>$record[0]["description"],
          'allDay'=> false,
          'backgroundColor'=>'#BDBDBD'
          );
        }
     }
    
     foreach ($absent_day_array as $key => $value) {
        $record[0]["title"]="Absent";
        $record[0]["id"]="1";
        $record[0]["description"]=$value;
        $event_array[] = array(
          'id' => $record[0]['id'],
          'title' => $record[0]['title'],
          'start' => $key,
          'end'   => $key,
           'description'=>$record[0]["description"],
          'allDay'=> false,
          'backgroundColor'=>'#333300'
          );
     }

   foreach ($alldate_array1 as $key => $value) {
       $record[0]["title"]="Present";
       $record[0]["id"]="1";
       $record[0]["description"]=$value;
        $event_array[] = array(
        'id' => $record[0]['id'],
        'title' => $record[0]['title'],
        'start' => $key,
        'end'   => $key,
        'description'=>$record[0]["description"],
        'allDay'=> false,
        'backgroundColor'=>'#1DA275'
        );
    }
foreach ($alldate_array as $key => $value) {
       $record[0]["title"]="Present";
       $record[0]["id"]="1";
       $record[0]["description"]=$value;
       $event_array[] = array(
        'id' => $record[0]['id'],
        'title' => $record[0]['title'],
        'start' => $key,
        'end'   => $key,
        'description'=>$record[0]["description"],
        'allDay'=> false,
        'backgroundColor'=>'#1DA275'
        );
        }

foreach ($leave_array as $key => $value) {
if($value == "Full Day")
  { 
    $record[0]["title"]='Leave';
    $record[0]["id"]="1";
    $record[0]["description"]=$value;
    $event_array[] = array(
      'id' => $record[0]['id'],
      'title' =>$record[0]['title'],
      'start' =>$key, 
      'end'   => $key,
      'description'=>$record[0]["description"],
      'allDay'=> false,
      'backgroundColor'=>'#FF6600'
      );
}
else
{      
$record[0]["title"]='Leave';
$record[0]["id"]="1";
$record[0]["description"]=$value;
$event_array[] = array(
  'id' => $record[0]['id'],
  'title' =>$record[0]['title'],
  'start' =>$key, 
  'end'   => $key,
  'description'=>$record[0]["description"],
  'allDay'=> false,
  'backgroundColor'=>'#FF6600'
  );
} 
}

foreach ($work_from_array as $key => $value) {
$record[0]["title"]="Work From Home";
$record[0]["id"]="1";
$record[0]["description"]='';
$event_array[] = array(
  'id' => $record[0]['id'],
  'title' => $record[0]['title'],
  'start' => $key,
  'end'   => $key,
  'description'=>$record[0]["description"],
  'allDay'=> false,
  'backgroundColor'=>'#F0B416'

  );
}
foreach ($work_from_client_array as $key => $value) {
  $record[0]["title"]="Work From Client Location";
  $record[0]["id"]="1";
  $record[0]["description"]='';
  $event_array[] = array(
    'id' => $record[0]['id'],
    'title' => $record[0]['title'],
    'start' => $key,
    'end'   =>$key,
    'description'=>$record[0]["description"],
    'allDay'=> false,
    'backgroundColor'=>'#BBDD40'
      );
    }
  echo json_encode($event_array);                 
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
public function getData($id="")
{ 
  if($id) {
    $employee_code=$id;
  }else {
      $login_id=\Auth::user()->employee_code ;
      $employee_code=$login_id; 
  }
$temp_start=$_REQUEST['start']; 
$temp_end=$_REQUEST['end'];
 
$temp_start_time_format=date("Y-m-d H:i:s",strtotime($temp_start));
$temp_end_time_format=date("Y-m-d H:i:s",strtotime($temp_end));
 
 $comment_array = array();
$getData_report=DB::collection('report')
        ->where('emp_code','=',$employee_code)
        ->whereBetween('att_date',[$temp_start,$temp_end])
        ->get();

     if(count($getData_report)==0)
     {
         $dateRecord[][] = "";
     }   
     else{
      foreach ($getData_report as $key => $value) {
            
            if(isset($value['comment']))
            {
                 $comment_array[$value['att_date']]=$value['comment'];
            }
            else
            {  
              $dateRecord[$value['att_date']][] = $value;
            }
      }
    }

$alldate_array=array();
$alldate_array1=array();
$checkin_mul=array();
$working = array();
 $all_data=array();
if(isset($dateRecord)) {

  $countRecord = count(array_keys($dateRecord));
  $all_Checkin= array();
  $min = array();
  $max =array();
 
  
  foreach ($dateRecord as $key => $value) {
    if($key == 0)
    {}
    else
    {
  if(count($value) == 1){ 
      $checkin_time = date("H:i:s",strtotime($value[0]['checkin']));
      $checkout_time = date("H:i:s",strtotime($value[0]['checkout']));
      $hours=date("G",strtotime($value[0]['worked']));    
      $minutes=date("i",strtotime($value[0]['worked']));  
      $time=sprintf("%02d", $hours). ":" . $minutes; 
      $checkout_array=array($key => $checkout_time);
      $all_date="In time:".$checkin_time."<br>Out time:".$checkout_time."<br>Total Hours:".$time;
      $alldate_array[$key]=$all_date;
    }else {
    $all_Checkin=array();
    $all_checkout=array();
    $millisecond="0";
    for($i=0;$i<count($value);$i++) 
        {  
          $all_Checkin[]=date("H:i",strtotime($value[$i]['checkin']));
          $all_checkout[]=date("H:i",strtotime($value[$i]['checkout'])); 
          $working[$key][] = date("H:i:s",strtotime($value[$i]['worked'])); 
        } 

        $all_data[$key]['min']=min($all_Checkin);
        $all_data[$key]['max']=max($all_checkout);
        $seconds = 0;
        $total_hours=0;   
    } 
  }
  }  
}    
/* if(count($working) != 0)
 {*/
 foreach ($working as $key => $value) {  
         for($i=0;$i<count($value);$i++)
                          {    
                               $hour = Chart::_inputhoursCalculation($value); 
                          }
                           $all_data[$key]['hours']=$hour.''; 
          } 
       foreach ($all_data as $key => $value) {
           $all_date="In time:".$value['min']."<br>out time:".$value['max']."<br>Total Hours:".$value['hours'];
           $alldate_array1[$key]=$all_date;        
       }  
/* }*/
      $start = new MongoDate(strtotime($temp_start));
      $end = new MongoDate(strtotime($temp_end)); 
      $holiday =DB::collection('holiday')->whereBetween('Date',[$start,$end])->get();

      $holiday_array = array();       
      foreach ($holiday as $key => $value) {
      $day = date('Y-m-d',$value['Date']->sec);
      $title=$value['Title'];
      $holiday_array[$day]=$title;
      }
      $leave= emp_details::where('employee_code','=',$employee_code)
                        ->where('type','=','leave')
                        ->where('state','=',"Approved")
                        ->whereBetween('from_date',[$start, $end])
                        ->whereBetween('to_date',[$start, $end])
                        ->get();
  
      $leave_array=array();
      $half_date = array();
      foreach ($leave as $key => $value) {
       
        if($value['from_session'] == "Half Day")
        { 
             $leave_from_date = date('Y-m-d',strtotime($value['from_date']));
             $half_date[]=$leave_from_date;
             $leave_type1=$value['from_session'];
             $leave_state=$value['state'];
             $leave_record=$leave_type1."</br>".$leave_state;
             $leave_array[$leave_from_date]=$leave_record;
        }
        else
        {
            $leave_from_date = date('Y-m-d',strtotime($value['from_date']));
            $leave_to_date = date('Y-m-d',strtotime($value['to_date'])); 
            if($leave_from_date == $leave_to_date){
                 $leave_type1=$value['from_session'];
                 $leave_state=$value['state'];
                 $leave_array[$leave_from_date]=$leave_state;
            }
            else{
                  $dates =$this->getDatesFromRange($leave_from_date,$leave_to_date); 
                  $leave_state=$value['state'];
                  foreach ($dates as $key => $value) {  
                    $date = strtotime($value);
                    $date = date("l", $date);
                    $date = strtolower($date); 
                    if($date != "saturday" && $date != "sunday") { 
                       $leave_array[$value]=$leave_state;
                    }   
                  }  
            } 
        }
      }
         
      $work_from_home=emp_details::where('employee_code','=',$employee_code)
                     ->where('type','=','work_from_home')
                     ->where('state','=',"Approved")
                     ->where('from_date','<=', $end) 
                     ->where('to_date','>=',$start)
                     ->get();

      $work_from_array=array();
      foreach ($work_from_home as $key => $value) {
                  $home_from_date = date('Y-m-d',strtotime($value['from_date']));
                  $home_to_date = date('Y-m-d',strtotime($value['to_date']));

                /* $home_to_date=date('Y-m-d',strtotime($value['to_date']));
                  $temp1=$home_from_date.",".$home_to_date;*/
                  if($home_from_date == $home_to_date)
                  {
                   $home_type=$value['type'];
                   $work_from_array[$home_from_date]=$home_type;  
                  }
                  else
                  {  
                    $dates =$this->getDatesFromRange($home_from_date,$home_to_date);
                    foreach ($dates as $key1 => $value1) {
                        $date = strtotime($value1);
                        $date = date("l", $date);
                        $date = strtolower($date); 
                        if($date != "saturday" && $date != "sunday") {  
                            $home_type=$value['type'];
                            $work_from_array[$value1]=$home_type; 
                        }
                    }
                  }
      }  

      /*$work_from_client=wfc_details::where('employee_code','=',$employee_code)
                                  ->where('state','=',"Approved")
                                  ->whereBetween('from_date',[$start,$end])
                                  ->whereBetween('to_date',[$start,$end])
                                  ->get();*/

     $work_from_client=emp_details::where('employee_code','=',$employee_code)
                                  ->where('type','=','work_from_client_location')
                                  ->where('state','=',"Approved")
                                  ->where('from_date','<=', $end) 
                                  ->where('to_date','>=',$start)
                                  ->get();

      $work_from_client_array=array(); 
      foreach ($work_from_client as $key => $value) {
               $client_from_date = date('Y-m-d',strtotime($value['from_date']));
               $client_to_date = date('Y-m-d',strtotime($value['to_date']));
               
              if($client_from_date == $client_to_date)
                  {
                   $client_type=$value['type'];
                   $work_from_client_array[$client_from_date]=$client_type;  
                  }
              else
                  { 
                    $dates =$this->getDatesFromRange($client_from_date,$client_to_date);
                    foreach ($dates as $key1 => $value1) {
                    $date = strtotime($value1);
                    $date = date("l", $date);
                    $date = strtolower($date); 
                    if($date != "saturday" && $date != "sunday") {  
                       $client_type=$value['type'];
                        $work_from_client_array[$value1]=$client_type; 
                    }    

                    }
 
                  } 
            }   
 
   
///comment/////
 
 
    $comment_absent = array();
    foreach ($comment_array as $key => $value) {
            $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
            $comment_absent[] = $date_time;
       } 


      $report_absent = array();
      foreach ($alldate_array as $key => $value) {
            $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
             $report_absent[] = $date_time;
       } 
       
      $alldate_array1_absent = array();
      foreach ($alldate_array1 as $key => $value) {
          $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
          $alldate_array1_absent[] = $date_time;
      }


      $difference1 = array_merge(array_diff($report_absent,$alldate_array1_absent),                         array_diff($alldate_array1_absent, $report_absent));
      
      $wfh_absent = array();
      foreach ($work_from_array as $key => $value) {
          $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
            $wfh_absent[] =$date_time;
      }
       
      $wfc_absent = array();
      foreach ($work_from_client_array as $key => $value) {
          $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
            $wfc_absent[] =$date_time;
      }

      $difference2 = array_merge(array_diff($wfh_absent,$wfc_absent),                         array_diff($wfc_absent, $wfh_absent));
       

   $leave_absent = array();
       foreach ($leave_array as $key => $value){
            $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
            $leave_absent[] = $date_time;
       }
      $holiday_absent = array();
      foreach ($holiday_array as $key => $value) { 
            $date =  date('Y-m-d', strtotime($key));
            $date_time = $date. " 00:00:00"; 
            $holiday_absent[] = $date_time; 
        }
 
   $difference3 = array_merge(array_diff($leave_absent,$holiday_absent), array_diff($holiday_absent, $leave_absent));
     
   $difference11 = array_merge(array_diff($difference1,$difference2), array_diff($difference2, $difference1)); 

   $difference12 = array_merge(array_diff($difference11,$difference3),array_diff($difference3, $difference11));    


    foreach ($half_date as $key => $value) {
          $difference12[] = $value." 00:00:00"; 
    }

    foreach ($comment_absent as $key => $value){
        $difference12[] = $value; 
    }
      
    foreach ($wfh_absent as $key => $value) {
         $difference12[] = $value;
    }
      
    foreach ($wfc_absent as $key => $value) {
         $difference12[] = $value;
    } 

  foreach ($leave_absent as $key => $value) {
         $difference12[] = $value;
    }

   

   $dates_range  =$this->getDatesFromRange($temp_start,$temp_end);
   $dates_range_array = array();
   $current_date = date("Y-m-d"); 
   foreach ($dates_range as $key => $value) { 
                      $date = strtotime($value);
                      $date = date("l", $date);
                      $date = strtolower($date); 
                      if($date != "saturday" && $date != "sunday") { 
                          if($value < $current_date)
                          {
                             $dates_range_array[] = $value . " 00:00:00";
                          } 
                    }  
   }  
    
  $final_absent = array_merge(array_diff($difference12 ,$dates_range_array),  array_diff($dates_range_array, $difference12)); 
     
    $absent_day_array = array();
    foreach ($final_absent as $key => $value) {  

              $date = strtotime($value);
              $date = date("l", $date);
              $date = strtolower($date); 
              $june = "2016-06-01 00:00:00";
              if($date != "saturday" && $date != "sunday") { 
                if($value < $current_date && $value > $june)
                    {
                        $absent_day_array[$value]= "In time:00:00:00<br>Out time:00:00:00<br> Total Hours:00:00:00"; 
                    }
            }
    }
     $this->showCalender($alldate_array,$alldate_array1,$holiday_array,$leave_array,$work_from_array,$work_from_client_array,$absent_day_array,$comment_array);
    } 
}