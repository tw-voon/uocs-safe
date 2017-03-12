<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Validator;

class users extends Controller
{
    function login(Request $request)
    {

    	if (User::where('name', '=', $request['name'])->exists()) 
    	{
    		$userID = User::where('name', '=', $request['name'])->select('id','password')->get();
    		$user = User::find($userID[0]->id);
    		if($this->verifyUser($user, $request))
    			return ["status" => "success", "data" => $user];
    		else
    			return ["status"=>"fail", "data" => "Wrong email/password"];

		}
		else
		{
			return ["status" => "fail", "data" => "User does not exist, Please register or continue as Guest User"];
		}

    	// return $request['pass'];
    }

    function verifyUser($user, $userData)
    {

    	if($user->password == $userData['pass'])
    		return true;
    	else 
    		return false;
    }

    function register_user(Request $request)
    {
        $data = $request->all();

        $v = Validator::make($data, [
            'name' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($v->fails()) {
            return $v->messages()->first();
        }

        // $this->validate($request, 
        //     ['name' => 'required|unique:users'],
        //     ['name.required' => 'Name is required'],
        //     ['name.unique:users' => 'This name was taken by another user'],
        //     ['pass' => 'required|min:6'],
        //     ['pass.required' => 'Password is required'],
        //     ['pass.min:6' => 'Password\' must be at least 6']);



        // $this->validate($request, [
        //     'name' => 'required|unique:users',
        //     'password' => 'required|min:6'
        //     ]);

        $users = new User();
        $users->name = $data['name'];
        $users->password = $data['password'];
        $status = $users->save();

        if($status)
            return ["status" => "success", "data" => User::find($users->id)];
        else
            return ["status" => "Something went wrong"];
    }

    function register_key(Request $request)
    {
        $data = $request->all();

        $user = User::find($data['userID']);
        $user->firebaseID = $data['key'];
        $status = $user->update();

        if($status)
            return "success";
        else return "fail";

    }

    function searchUser(Request $request){
        $data = $request->all();

        if (User::where('name', '=', $request['name'])->exists()){
            $userID = User::where('name', '=', $request['name'])->select('id','password')->get();
            $user = User::find($userID[0]->id);
            return ["status" => "success", "data" => $user];
        }
        else return ["status" => "fail", "data" => "User not exists in this application"];
    }
}
