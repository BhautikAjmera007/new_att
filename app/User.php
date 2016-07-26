<?php

namespace App;
use DB;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Gbrock\Table\Traits\Sortable;


 class User extends Eloquent implements Authenticatable
 {

    protected $collection = 'users';
    use AuthenticableTrait;
    use Sortable;
     
 
    protected $fillable = ['name', 'email', 'password','username','role','employee_code', 'designation', 'uimg','uid',];
 

    protected $sortable = ['name', 'email', 'password','username','role','employee_code', 'designation', 'uimg', 'uid',];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function checkManuallyInpoutStatus()
    {
    	$loggedin_empcode = \Auth::user()->employee_code;
    	$users_role = User::orderBy('username', 'desc')
                          ->where('employee_code', '=', $loggedin_empcode)
                          ->get(['role']);
    
       // Check whether loggedon user has HR role than display Manually In-Out Menu Start
       $displayManuallyInOutStatus=$users_role;
       if(in_array('HR',explode(",",$displayManuallyInOutStatus[0]['role']))){
         $boolManuallyInoutStatus="1";
         \View::share('boolManuallyInoutStatus',$boolManuallyInoutStatus);
       }else{
          $boolManuallyInoutStatus="0";
         \View::share('boolManuallyInoutStatus',$boolManuallyInoutStatus);
       }
       // Check whether loggedon user has HR role than display Manually In-Out Menu End

       return true;
    }
}
