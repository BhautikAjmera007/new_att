<?php

namespace App\Console\Commands;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use App\leave_details;
use App\wfc_details;
use App\wfh_details;
use App\cron;
use config\leave;
use MongoDate;
use App\emp_details;

class leaves extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Leave';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Request $request)
    {   
        $cron = cron::orderBy('cron_time', 'desc')->where('cron_type', 'leave')->first();

        $last = isset($cron->cron_time) ? $cron->cron_time :"All";
        $cron = new cron;
        $cron->cron_type = "leave";
        $cron->cron_time = time();
        $cron->save();
      
        $username = config('attendance.username');
        $password = config('attendance.passwords');
        $url = config('attendance.url');

        $arrayName = array('username' => $username,
                        'password' => $password);
         
        $array = json_encode($arrayName,true);
        $post = $request->all();

        $response = \Httpful\Request::post($url.'userloginapi/user/login')->sendsJson()->body($array)->send();

        $cookie_session = $response->body->session_name . '=' . $response->body->sessid;

        $last="All";

        $response = \Httpful\Request::get($url.'employee_attendance/employee_attendance_api/'.$last)->addHeader('Cookie', $cookie_session )->send();
        
        foreach ($response->body as $key => $value) {
            if(!empty($value->leave_details)){
                foreach ($value->leave_details as $key => $value1){    
                    $results = emp_details::where('nid', '=', $value1->nid)->get();
                    $len=count($results);

                        if($len == "0"){
                         $leave_details = new emp_details;
                         $leave_details->employee_code = $value1->employee_code;
                         $leave_details->nid = $value1->nid;
                         $leave_details->type = $value1->type;
                         $leave_details->user_name =$value1->user_name;
                         $leave_details->from_date =new MongoDate(strtotime(str_replace('/', '-', $value1->from_date)));
                         $leave_details->to_date = new MongoDate(strtotime(str_replace('/', '-', $value1->to_date)));
                         $leave_details->total_days =$value1->total_days;
                         $leave_details->from_session = $value1->from_session;
                         $leave_details->to_session =$value1->to_session;
                         $leave_details->from_session_ampm =$value1->from_session_ampm;
                         $leave_details->to_session_ampm =$value1->to_session_ampm;
                         $leave_details->state = $value1->state;
                         $leave_details->updated_date = $value1->updated_date;
                         $leave_details->save();
                        }else{
                            $data = emp_details::where('nid','=',$value1->nid)->get();
                            // Check whether leave status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record start

                            $apiLeaveStatus=$value1->state;

                            if($apiLeaveStatus == "Cancelled" || $apiLeaveStatus == "Rejected" ||$apiLeaveStatus == "Request For Cancel"){
                                $deletedRows = emp_details::where('nid', $value1->nid)->delete();
                            }else{
                                \DB::table('emp_details')
                                ->where('nid', $value1->nid)
                                ->update(['state' => $apiLeaveStatus]);

                                echo "Leave Record is updated_date successfully...";       
                            }

                            // Check whether leave status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record end
                        }
                }  
            }          

            if(!empty($value->wfc_details)){
                foreach ($value->wfc_details as $key => $value1){
                    $results = emp_details::where('nid', '=', $value1->nid)->get();
                    $len=count($results);
                    if($len == "0"){
                             $wfc_details = new emp_details;
                             $wfc_details->employee_code = $value1->employee_code;
                             $wfc_details->nid = $value1->nid;
                             $wfc_details->type = $value1->type;
                             $wfc_details->user_name =$value1->user_name;
                             $wfc_details->from_date =new MongoDate(strtotime(str_replace('/', '-', $value1->from_date)));
                             $wfc_details->to_date = new MongoDate(strtotime(str_replace('/', '-', $value1->to_date)));
                             $wfc_details->total_days =$value1->total_days;
                             $wfc_details->from_session = $value1->from_session;
                             $wfc_details->to_session =$value1->to_session;
                             $wfc_details->from_session_ampm =$value1->from_session_ampm;
                             $wfc_details->to_session_ampm =$value1->to_session_ampm;
                             $wfc_details->state = $value1->state;
                             $wfc_details->updated_date = $value1->updated_date;
                             $wfc_details->save();
                         }else{
                                $data =  emp_details::where('nid','=',$value1->nid)->get();
                                // Check whether wfc status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record start

                                $apiWfcStatus=$value1->state;

                                if($apiWfcStatus == "Cancelled" || $apiWfcStatus == "Rejected" ||$apiWfcStatus == "Request For Cancel"){
                                $deletedRows = emp_details::where('nid', $value1->nid)->delete();
                                }else{
                                    \DB::table('emp_details')
                                    ->where('nid', $value1->nid)
                                    ->update(['state' => $apiWfcStatus]);

                                    echo "WFC Record is updated_date successfully...";       
                                }

                                // Check whether wfc status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record end
                        }
                    }            
            }

            if(!empty($value->wfh_details)){
                foreach ($value->wfh_details as $key => $value1){
                    $results = emp_details::where('nid', '=', $value1->nid)->get();
                    $len=count($results);
                    if($len == "0"){
                     $wfh_details = new emp_details;
                     $wfh_details->employee_code = $value1->employee_code;
                     $wfh_details->nid = $value1->nid;
                     $wfh_details->type = $value1->type;
                     $wfh_details->user_name =$value1->user_name;
                     $wfh_details->from_date =new MongoDate(strtotime(str_replace('/', '-', $value1->from_date)));
                     $wfh_details->to_date = new MongoDate(strtotime(str_replace('/', '-', $value1->to_date)));
                     $wfh_details->total_days =$value1->total_days;
                     $wfh_details->from_session = $value1->from_session;
                     $wfh_details->to_session =$value1->to_session;
                     $wfh_details->from_session_ampm =$value1->from_session_ampm;
                     $wfh_details->to_session_ampm =$value1->to_session_ampm;
                     $wfh_details->state = $value1->state;
                     $wfh_details->updated_date = $value1->updated_date;
                     $wfh_details->save();
                    }else{
                        $data =  emp_details::where('nid','=',$value1->nid)->get();

                        // Check whether wfh status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record start

                        $apiWfhStatus=$value1->state;

                        if($apiWfhStatus == "Cancelled" || $apiWfhStatus == "Rejected" ||$apiWfhStatus == "Request For Cancel"){
                        $deletedRows = emp_details::where('nid', $value1->nid)->delete();
                        }else{
                            \DB::table('emp_details')
                            ->where('nid', $value1->nid)
                            ->update(['state' => $apiWfhStatus]);

                            echo "WFH Record is updated_date successfully...";       
                        }

                        // Check whether wfh status is Cancelled, Rejected or Request For Cancel than delete that entry from leave table else update the record end
                    }
                }
            }
        }
        $this->info('Work From Home, Work From Client and Leave Data Save Successfully ...'); 
    }   
}
