<?php namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class SearchEmployee extends Eloquent
{    
    public function search($data)
    {
        $user = new \App\User();
        $users = $user::where('name','LIKE', $data['^a-zA-Z0-9'] . '%')->paginate();
        // orWhere('fname','LIKE', '%' . $data['keyword'] . '%')->orWhere('lname','LIKE', '%' . $data['keyword'] . '%')->orWhere('email','LIKE', '%' . $data['keyword'] . '%')->orWhere('role','LIKE', '%' . $data['keyword'] . '%')->paginate();

      
        return $users;         
    }
}
    