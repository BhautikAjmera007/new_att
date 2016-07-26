<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use MongoDate;
use App\leave_details;
use App\wfc_details;
use App\wfh_details;
use App\holiday;
use App\Chart;
use DB;
use Mail;
use config\email;
use App\sendmail_detail;
use App\emp_details;

class Absents extends Command
{
    protected $signature = 'absent';

    protected $description = 'Send Email to Absent Employee';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $currentDate=date('Y-m-d',strtotime("-1 days"));
        $isoCurrentDate = new MongoDate(strtotime($currentDate));

        // If Current date is either Saturday or Sunday than Cron will not executed.
        if(date('w', strtotime($currentDate)) == 6 || date('w', strtotime($currentDate)) == 0) {
            echo 'Because of either Saturday or Sunday on '.date('d-m-Y',strtotime($currentDate))." cron is not executed";
        }else{
            // If Current date is holiday than Cron will not executed.
            $holiday = holiday::where('Date','=', $isoCurrentDate)->get();
            if(count($holiday)!= "0"){
                echo "Because of Holiday on ".date('d-m-Y',strtotime($currentDate))." cron is not executed";
            }else if(count($holiday) == "0"){
                $absentEmployee=array();
                $booleanArray=array();

                $userData=User::All();

                // Store All Employee Code in $userArray[] variable.
                foreach ($userData as $key => $value) {
                    $userArray[]=$userData[$key]['employee_code'];
                }

                // Check if data is synchronized in report table or not start
                foreach ($userArray as $key => $value) {
                    $rpt=Chart::where('emp_code','=',$value)
                              ->where('att_date','=',$currentDate)
                              ->get();

                    if(count($rpt) != "0"){
                        $booleanArray[]=$rpt;
                    }
                }
                // Check if data is synchronized in report table or not end

                if(count($booleanArray) != "0"){
                    // Check all employee code of users table with Report Table, Work From Home, Work From CLient and Leave table and if employee code is not found in Report Table, WFH, WFC, Leave than fetch that emploee detail from user table and send store it in Send Mail Detail table and send mail.
                    foreach ($userArray as $key => $value) {
                        $rpt=Chart::where('emp_code','=',$value)
                                  ->where('att_date','=',$currentDate)
                                  ->get();
                        
                        $manuallyInOutComment=Chart::where('emp_code','=',$value)
                                  ->where('att_date','=',$currentDate." 00:00:00")
                                  ->get();

                        $wfh=emp_details::where('employee_code','=',$value)
                                        ->where('type','=','work_from_home')
                                        ->where('from_date','<=',$isoCurrentDate)
                                        ->where('to_date','>=',$isoCurrentDate)
                                        ->where('state','=','Approved')
                                        ->get();

                        $wfc=emp_details::where('employee_code','=',$value)
                                        ->where('type','=','work_from_client_location')
                                        ->where('from_date','<=',$isoCurrentDate)
                                        ->where('to_date','>=',$isoCurrentDate)
                                        ->where('state','=','Approved')
                                        ->get();

                        $leave=emp_details::where('employee_code','=',$value)
                                        ->where('type','=','leave')
                                        ->where('from_date','<=',$isoCurrentDate)
                                        ->where('to_date','>=',$isoCurrentDate)
                                        ->where('state','=','Approved')
                                        ->get();


                        if(count($rpt) == "0" && count($wfh) == "0" && count($wfc) == "0" && count($leave) == "0" && count($manuallyInOutComment) == "0"){
                            $absentEmployee[]=$value;
                        }
                    }

                    if(count($absentEmployee) == "0"){
                        echo "There is no any Employee Absent Today ...";
                    }else{
                        foreach ($absentEmployee as $key => $value) {
                            $empData1=User::select('name','employee_code','email')->where('employee_code','=',$value)->get();
                            $todayDate=date('d/m/Y',strtotime("-1 days"));

                            $empData[]=array(
                                                $empData1[0]['name'],
                                                $empData1[0]['employee_code'],
                                                $empData1[0]['email'],
                                                $empData1[0]=$todayDate,
                                            );
                        }

                        $todayDate=date('d/m/Y',strtotime("-1 days"));
                        // Send Mail to Employee start
                        foreach ($empData as $data) {
                                $subject="Your work status discrepancy for ".$todayDate;
                                $action= "Absent";
                                session(['absentEmployeeName' => $data[0]]);
                                $view = "emails.user";
                                $save_status="1";
                                sendmail_detail::emailTo($data,$subject,$view,$action,$save_status);           
                        }
                        // Send Mail to Employee End

                        // Send Email to HR Start
                        \View::share('data',$empData);
                        $email1 = config('email.email');
                        $name1 =  config('email.name');
                        $emp_code =  config('email.emp_code');
                        $data = array(
                            '0'=>$name1,
                            '1'=>$emp_code, 
                            '2'=>$email1,
                            );
                        $subject="Absent Employee!";
                        $action="Hr_Absent";
                        $view = "emails.hr";
                        $save_status="1";
                        sendmail_detail::emailTo($data,$subject,$view,$action,$save_status);
                        // Send Email to HR End  

                         $this->info('Absent Employee Mail Send Successfully ...');     
                    } //Check Absent Employee else over
                }else{
                    echo "Data is not synchronized yet...";
                }
            } //Holiday bracket over
        } //Wekend bracket over
    } //Fnction End
}