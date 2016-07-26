<?php
	// Attendance API
	Route::any('api/v1/attendance','Api\V1\AttendanceController@att_data');
	Route::any("attendancereport",'Api\ResourceController@attendanceReport');
	Route::any("dailyreport",'Api\ResourceController@dailyReport');
	
    Route::group(['middleware' => 'web'], function () {
		$authmiddleware = Config::get('cas.status') == "enable" ? 'casauth' : 'auth';

		// ................Auth Route......................
	    Route::auth();
	    Route::get('auth/login', 'Auth\AuthController@getLogin');
		Route::post('auth/login', 'Auth\AuthController@postLogin');
		Route::get('auth/logout', 'Auth\AuthController@getLogout');
		Route::get('user','user\UserController@show');

		Route::group(['middleware' => [$authmiddleware]], function () {	

		// ................Default Route....................
	    Route::get('/', function () {	
			return redirect('calender');
		});	

		// ................Registration Route...............
		Route::get('auth/register', 'Auth\AuthController@getRegister');
		Route::post('auth/register', 'Auth\AuthController@postRegister');

		Route::controllers([
		  'auth' => 'Auth\AuthController',
		  'password' => 'Auth\PasswordController',
		]);
 
		// ................Calender Route....................
		Route::get('calender/{id?}','Calender\CalenderController@calender');
        Route::get("myevent/{id?}",'Calender\CalenderController@getData');
        Route::any("showCalender",'Calender\CalenderController@showCalender');

		Route::get('user','user\UserController@show');
		Route::get('absent','user\UserController@absent_employee');

		// ................Chart Route....................
		Route::get('chart/{id?}','Chart\ChartController@renderChart');
		Route::get('chart/next/{currMonth?}{currYear?}','Chart\ChartController@renderChart');
		Route::get('chart/previous/{currMonth?}{currYear?}','Chart\ChartController@renderChart');

		// ................Reports Routes....................
		Route::get('/worktime', ['middleware' => 'role','uses' => 'worktime\WorktimeController@index']);

		Route::any('/status/{date?}', ['middleware' => 'role','uses' => 'user\UserController@showStatus']); 

		Route::post('/updatestatus','user\UserController@updateStatus'); 
 
  		Route::any('userBy', ['middleware' => 'role','uses' => 'user\UserController@show']);  
        Route::any('monthBy', ['middleware' => 'role','uses' => 'user\UserController@employee_report']);    
        Route::any('day_report', ['middleware' => 'role','uses' => 'user\UserController@day_report_data']);    
        Route::post('manuallyReportInsert','user\UserController@manuallyReportInsert');

        Route::any('get_all_detail_report_data','user\UserController@get_all_detail_report_data');
        Route::any('monthBy/print/{year?}{month?}','user\UserController@print_report');
        Route::any('manually', ['middleware' => 'role','uses' => 'user\UserController@manuallyReport']);    

        Route::any('dateFormat','user\UserController@dateFormat'); 
        Route::any('getDatesFromRange','user\UserController@dateFormat'); 
           
        Route::get('role',function(){
        		return view('errors/401');
        });
 		
 		Route::get('searchName/{id}/{year}/{month}','user\UserController@searchName');
 		 
	});  //Auth Middleware End
});  //Web Middleware End