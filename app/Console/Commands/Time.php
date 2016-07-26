<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Worktime;
use App\User;

class Time extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worktime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commnad is used to store working times.';

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
    public function handle()
    {
        $username = config('attendance.username');
        $password = config('attendance.passwords');
        $url = config('attendance.url');

        $arrayName = array('username' => $username,
                        'password' => $password);
         
        $array = json_encode($arrayName,true);

        $response = \Httpful\Request::post($url.'userloginapi/user/login')->sendsJson()->body($array)->send();

        $cookie_session = $response->body->session_name . '=' . $response->body->sessid;

        $response = \Httpful\Request::get($url.'worktimeservices/work_time_services')->addHeader('Cookie', $cookie_session )->send();

        if(!empty($response->body)){
            foreach ($response->body as $key => $value) {
                $results = Worktime::where('nid', '=', $value->Nid)->get();
                $len=count($results);

                if($len == "0"){
                    $userName=$value->User_Name;

                    $data=User::where('username','=',$value->User_Name)->get();
                    $employeeCode= $data[0]['employee_code'];
                    $name= $data[0]['name'];
                    $email= $data[0]['email'];
                    $role= $data[0]['role'];

                    if(count($employeeCode) != "0"){
                        $worktime_details = new Worktime;
                        $worktime_details->nid = $value->Nid;
                        $worktime_details->employee_code = $employeeCode;
                        $worktime_details->name = $name;
                        $worktime_details->email = $email;
                        $worktime_details->role = $role;
                        $worktime_details->username = $value->User_Name;
                        $worktime_details->settime = strip_tags($value->Set_Time);
                        $worktime_details->fromdate = date('Y-m-d',strtotime(str_replace("/", "-", strip_tags($value->From_Date))));
                        $worktime_details->todate = $worktime_details->todate = date('Y-m-d',strtotime(str_replace("/", "-", strip_tags($value->To_Date))));
                        $worktime_details->state = $value->State;
                        $worktime_details->save();
                    }
                }else{
                    $data = Worktime::where('nid','=',$value->Nid)->get();
                    $apiWorkTimeStatus=$value->State;

                    if($apiWorkTimeStatus == "Cancelled" || $apiWorkTimeStatus == "Rejected" ||$apiWorkTimeStatus == "Request For Cancel"){
                        $deletedRows = Worktime::where('nid', $value->Nid)->delete();
                    }else{
                        \DB::table('worktime_details')
                        ->where('nid', $value->Nid)
                        ->update(['state' => $apiWorkTimeStatus]);

                        echo "Worktime updated successfully...";
                    }
                }
            }
        }
        $this->info('Work Time Save Successfully ...'); 
    }
}