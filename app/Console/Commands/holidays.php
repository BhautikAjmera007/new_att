<?php

namespace App\Console\Commands;
use Illuminate\Http\Request;
use App\holiday;
use App\cron;
use MongoDate;
use config\attendance;
use Illuminate\Console\Command;

class holidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $cron = cron::orderBy('cron_time', 'desc')->where('cron_type', 'holiday')->first();
        $last = isset($cron->cron_time) ? $cron->cron_time :"All";
      
            $cron = new cron;
            $cron->cron_type = "holiday";
            $cron->cron_time = time();
            $cron->save();
      
        $url = config('attendance.url');
        $post = $request->all();
        
        $response = \Httpful\Request::get($url.'holidaysapi/views/holidays_service')->send();
       
            foreach ($response->body as $key => $value) {
                $results = holiday::where('Date', '=', $value->Date)->get();
                $len=count($results);
					
                if($len == "0") {
                    $holiday = new holiday;
                    $holiday->Title = $value->Title;
                    $holiday->Date = new MongoDate(strtotime($value->Date));
                    $holiday->save();  

                }else if($len == "1") {
                     $post = $request->all();
                        $data =  holiday::where('Date', '=', $value->Date)->get();
                        unset($post['_token']);
                        $result =  holiday::where('Date','$Date')->update($post);
                      
                        echo "Record is updated date.......";
                }
            }  

              $this->info('holiday Data Save Successfully ...');             
    }
}
