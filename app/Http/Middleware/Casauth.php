<?php
namespace App\Http\Middleware;
use Closure;
use Xavrsl\Cas\Facades\Cas;
use Illuminate\Support\Facades\Auth;
use App;
use Config;
class Casauth
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
     	if(!Cas::isAuthenticated()){
			 Cas::authenticate();
		}else {
			$casUser = Cas::getCurrentUser();
			$user = App\User::where('username','=',$casUser)->first();

            if($user){
				Auth::loginUsingId($user->id);
            }else{
 				$apiUrl    = Config::get('attendance.url');
				$preDefinesystemApi = Config::get('attendance.preDefinesystemApi');
				$preDefinePassword = Config::get('attendance.preDefinePassword');
				$hrmsapitoken = Config::get('attendance.hrmsapitoken');

				$response = \Httpful\Request::get($apiUrl."employee_info_api/employee_info_api/".base64_encode($preDefinesystemApi)."?token=".base64_encode($preDefinePassword).$hrmsapitoken)
				    ->send();

				if($response->body->status == "success"){
					$token=$response->body->token;

					$response = \Httpful\Request::get($apiUrl."employee_info_api/employee_info_api/".base64_encode($casUser)."?verify_token=".$token)
				        ->send();

				    // Check Response Start
					if(empty($response->body->first_name) || empty($response->body->last_name) || empty($response->body->designation) || empty($response->body->employee_code) || empty($response->body->user_name) || empty($response->body->user_email)){
							return \view('errors/503');
					}else if(count($response->body->role) == "0"){
							return \view('errors/503');
					}
					// Check Response End

					if (isset($response->body)) {
						$userI = array(
			                'name' 		=> $response->body->first_name." ".$response->body->last_name,
			                'designation' 	=> $response->body->designation,
			                'employee_code' 	=> $response->body->employee_code,
			                'username' 	=> $response->body->user_name,
							'password' 	=> bcrypt("test123"),          
							'email' 	=> $response->body->user_email,              
							'uimg' 	=> $response->body->user_image,           
							'uid' 	=> $response->body->uid,
			            );

						foreach($response->body->role as $r){
							$role[] = $r;
						}
			         
						$userI['role'] = implode(",",$role);
			                  
						// 
						$findOrFailUser = App\User::where('employee_code','=',$response->body->employee_code)->first();
						if(count($findOrFailUser) != "0"){
							$getUser = App\User::where('employee_code','=',$response->body->employee_code)->get();
							$_id=$getUser[0]['_id'];

							Auth::loginUsingId($_id);
							return $next($request);
						}elseif(count($findOrFailUser) == "0"){
							$userData = App\User::create($userI);
							Auth::loginUsingId($userData->id);
						}
						// 
					}else{
						return \view('errors/503');
					}
 
				}else{
					return \view('errors/token');
				}
			}
			return $next($request);
		}
	}
}