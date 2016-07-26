<?php

namespace App\Http\Middleware;
use App\User;
use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $loggedin_empcode= \Auth::user()->employee_code;
        $users_role=User::where('employee_code', '=', $loggedin_empcode)->get(['role']);
        
        if(in_array('Director',explode(",",$users_role[0]['role'])) || in_array('HR',explode(",",$users_role[0]['role'])) || in_array('Delivery Head',explode(",",$users_role[0]['role']))) { 
            $users_data=User::get(['name','employee_code','role']);  
            return $next($request);
        } 

       

        else{
             return redirect('role'); 
        }
    }
}
