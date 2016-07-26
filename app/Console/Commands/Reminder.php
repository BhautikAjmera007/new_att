<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\sendmail_detail;
use App\leave_details;
use App\wfc_details;
use App\wfh_details;
use App\Chart;
use App\emp_details;

class Reminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command is used to send reminder mail';

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
       $pendingRecords= sendmail_detail::where('status','!=','Complete')
                                        ->where('action','=','Absent')
                                        ->get();
       
       if(count($pendingRecords) == "0"){
            echo "There  is no Absent Employee ...";
       }else{
           foreach ($pendingRecords as $key => $value) {
               $dateEmpCode[$key][]=$pendingRecords[$key]['emp_code'];
               $dateEmpCode[$key][]=$pendingRecords[$key]['reminder_date'];
           }
        
           foreach ($dateEmpCode as $key => $value) {
                $empCode=$dateEmpCode[$key][0];
                $date=$dateEmpCode[$key][1];
                $isoDate = new \MongoDate(strtotime($date));

                $rpt=Chart::where('emp_code','=',$empCode)
                                  ->where('att_date','=',$date)
                                  ->get();

                $manuallyInOutComment=Chart::where('emp_code','=',$empCode)
                                  ->where('att_date','=',$date." 00:00:00")
                                  ->get();

                $wfh=emp_details::where('employee_code','=',$empCode)
                                        ->where('type','=','work_from_home')
                                        ->where('from_date','<=',$isoDate)
                                        ->where('to_date','>=',$isoDate)
                                        ->where('state','=','Approved')
                                        ->get();

                $wfc=emp_details::where('employee_code','=',$empCode)
                                        ->where('type','=','work_from_client_location')
                                        ->where('from_date','<=',$isoDate)
                                        ->where('to_date','>=',$isoDate)
                                        ->where('state','=','Approved')
                                        ->get();

                $leave=emp_details::where('employee_code','=',$empCode)
                                        ->where('type','=','leave')
                                        ->where('from_date','<=',$isoDate)
                                        ->where('to_date','>=',$isoDate)
                                        ->where('state','=','Approved')
                                        ->get();

        if(count($rpt) == "0" && count($wfh) == "0" && count($wfc) == "0" && count($leave) == "0" && count($manuallyInOutComment) == "0"){
                    $pendingEmpCode[$empCode][]=$empCode;
                    $pendingEmpCode[$empCode][]=$date;
                }else{
                    $updateRec=sendmail_detail::where('emp_code', '=', $empCode)
                                               ->where('reminder_date', '=', $date)
                                               ->update(['status' => 'Complete']);
                }

           }

           if(count($pendingEmpCode) == "0"){
            echo "There is no any reminder ...";
           }else{
                foreach ($pendingEmpCode as $key => $value) {
                    $empCode=$pendingEmpCode[$key][0];
                    $date=$pendingEmpCode[$key][1];

                    $temp=sendmail_detail::where('emp_code','=',$empCode)
                                                    ->where('reminder_date','=',$date)
                                                    ->get();

                    if(($temp[0]['action']) == "Absent"){
                         $rec[]=array(
                                       $temp[0]['name'],
                                       $temp[0]['emp_code'],
                                       $temp[0]['to'],
                                $temp[0]=date("d/m/Y", strtotime($temp[0]['reminder_date'])),
                                    );
                    }
                }
                
                // Send Mail to Employee start
                foreach ($rec as $data) {
                        $reminderDate=date("d/m/Y", strtotime($data['3']));

                        $subject="Your work status discrepancy for ".$reminderDate;
                        $action= "Absent";
                        session(['absentEmployeeName' => $data[0]]);
                        session(['reminderDate' => $reminderDate]);
                        $view = "emails.user";
                        $save_status="0";
                        sendmail_detail::emailTo($data,$subject,$view,$action,$save_status);           
                }
                // Send Mail to Employee End

                // Send Email to HR Start
                \View::share('data',$rec);
                $email1 = config('email.email');
                $name1 =  config('email.name');

                $data = array(
                    '0'=>$name1,
                    '1'=>"HR",
                    '2'=>$email1,
                    );

                $subject="Reminder of Absent Employee!";
                $action="Hr_Absent";
                $view = "emails.hr";
                $save_status="0";
                sendmail_detail::emailTo($data,$subject,$view,$action,$save_status);
                // Send Email to HR End

                $this->info('Reminder Mail Send Successfully ...');
            }
        }
    }
}
