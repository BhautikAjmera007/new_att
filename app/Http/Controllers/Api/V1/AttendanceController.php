<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\Chart;
use App\holiday;
use App\Attendance;
use App\emp_details;
use MongoDate;

class AttendanceController extends Controller
{
    public function att_data(Request $request)
    {   
        $url = config('attendance.url');

        $user = $request->input('username');
        // $user = base64_encode('bhautik.ajmera');

        // session_name , session_id it will be changed.
        $session_name=$request->input('sessionName');
        $session_id=$request->input('sessionId');
        $cookie_session = $session_name . '=' . $session_id;
       
        // $cookie_session = 'SSESSf59c876274aacbd1fc4e74aa32967f0b' . '=' . '8-shRA2pN6QkmGbkE9_Xruk2RytFQnvbYTDt30-82Ts';

        $response = \Httpful\Request::get($url.'authentication/user_authentication/'.$user)
                        ->addHeader('Cookie', $cookie_session)
                        ->send();

        foreach ($response->body as $key => $value) {
          $success=$value->success;
        }

        if(!$success){
            return \Response::json(array(
                    'code'      =>  404,
                    'message'   =>  "User Name is not exists!"
                ), 404);
        }else{
	        foreach ($response->body as $key => $value) {
                $uname=$value->message->name;
            }
            
            $getUserName = User::select('employee_code')->where('username','=',$uname)->get();
            $loggedinEmployeeCode=$getUserName[0]['employee_code'];  
        }
        

        if($success != "1"){
            return \Response::json(array(
                    'code'      =>  401,
                    'message'   =>  "Unauthorized user!"
                ), 401);
        }else{
            $fromDate=$request->input('from_date'); 
            $toDate=$request->input('to_date');

            $fromDay=date("j",strtotime($fromDate));
            $toDay=date("j",strtotime($toDate));

            $fromMonth=date("n",strtotime($fromDate));
            $toMonth=date("n",strtotime($toDate));

            $fromYear=date("Y",strtotime($fromDate));
            $toYear=date("Y",strtotime($toDate));

            if(empty($fromDate) || empty($toDate)){
                return \Response::json(array(
                    'code'      =>  400,
                    'message'   =>  "Please enter From Data and To Date ...."
                ), 400);
            }else if(strpos($fromDate,"/") == true || strpos($toDate,"/") == true){
                return \Response::json(array(
                    'code'      =>  400,
                    'message'   =>  "Please enter from date and to date with '-' ...."
                ), 400);
            }else{
                $totalDays=Attendance::_totalDays($fromDate,$toDate);

                // Calculating workingdays Start
                // workingdays = alldays - satusun - holiday 
                $allDay=Attendance::_totalDays($fromDate,$toDate);
                $satSun=Attendance::_calSatsun($fromDate,$toDate);          
                $holiday=Attendance::_holidays($fromDate,$toDate);      
                $firstResult=array_diff($allDay,$satSun);
                $result=array_diff($firstResult,$holiday);
                $workingDays=count($result);        
                // Calculating workingdays End

                $totalWorkingHours=($workingDays * 8);
                $workinghours=Attendance::_workingHours($fromDate,$toDate,$loggedinEmployeeCode);
                $data=Attendance::_empData($fromDate,$toDate,$loggedinEmployeeCode);
                
                $master["fromDate"]=$fromDate;
                $master["toDate"]=$toDate;
                $master["totalDays"]=count($totalDays);
                $master["workingDays"]=$workingDays;
                $master["totalWorkingHours"]=$totalWorkingHours;
                $master["workingHours"]=$workinghours;
                $master["data"]=array_values($data); 
                print_r(json_encode($master));
            }
        }
    }
}
