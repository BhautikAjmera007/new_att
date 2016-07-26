<?php

namespace App;
use DB;
use Mail;
use config\email;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class sendmail_detail extends Eloquent 
{
	protected $collection = 'sendmail_detail';

	protected $fillable = [
        'from', 'to','subject','body',
    ];


     protected function emailTo($data,$subject,$view,$action,$save_status)
     {
        Mail::send($view, $data, function($message)use($data,$subject,$action,$save_status)
        {   
            $message->from(config('email.email'), config('email.name'));
            $message->to($data[2], $data[0])->subject($subject);
            $message->cc(config('email.email'), config('email.name'));
            
            // Save Status = 1 mean's Save Record and Save Status = 0 mean's record is not save.
            // data[0] = Employee Name, data[1] = Employee Code, data[2] = Email id

            if($save_status != "0"){
                $sendmail_detail = new sendmail_detail;
                $sendmail_detail->from = (config('email.email'));
                $sendmail_detail->to = $data[2];
                $sendmail_detail->emp_code = $data[1];
                $sendmail_detail->name = $data[0];
                $sendmail_detail->subject = $message->getSubject();
                $sendmail_detail->cron_date = date('Y-m-d');
                $sendmail_detail->reminder_date = date('Y-m-d',strtotime("-1 days"));
                $sendmail_detail->body = $message->getBody(); 
                $sendmail_detail->status = "Pending"; 
                $sendmail_detail->action = $action;
                $sendmail_detail->comment = "";
                $sendmail_detail->save();
            }
        });
    }
}