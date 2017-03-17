<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\message;
use App\Model\chat_rooms;
use App\Http\Controllers\GCM;
use Validator;
use DB;

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
        $user->firebaseID = $data['token'];
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

    function fetchChatRoom(){
        echo json_encode(["chat_rooms" => chat_rooms::all(), "error" => false]);
    }

    function fetchSingleChatRoom(Request $request){

        // concat message with user details together
        $int = 0;

        $messageID = DB::table('messages')
                        ->where('chat_room_id', $request['chat_room_id'])
                        ->pluck('message_id');

        $messageDetails = DB::table('messages')
                        ->select('messages.message_id', 'messages.message', 'messages.created_at', 'users.id', 'users.name')
                        ->join('users', 'messages.user_id', 'users.id')
                        ->orderBy('messages.created_at', 'asc')
                        ->where('messages.chat_room_id', $request['chat_room_id'])
                        ->get();

                        if(count($messageDetails)!=0)
                            return ["messages" => $messageDetails, "error" => false];
                        else return ["error" => true];

        // while ($int < count($messageID)) {
        //     $userInvolved = DB::table('messages')
        //                 ->where('chat_room_id', $request['chat_room_id'])
        //                 ->select('user_id')
        //                 ->distinct()
        //                 ->get();

        //                 $int++;

        //                 return $userInvolved;
        // }

        // $userInvolved = DB::table('messages')
        //                 ->where('chat_room_id', $request['chat_room_id'])
        //                 ->pluck('user_id');

        // $userDetails = DB::table('users')
        //             ->whereIn('id', $userInvolved)
        //             ->select('id','name')
        //             ->get();

        // $messages = DB::table('messages')
        //             ->whereIn('message_id', $messageID)
        //             ->get();

        // $messages['user'] = $userDetails;

        // if(count($messages) == 0){
        //     return json_encode(["error" => true]);
        // } else {
        //     return json_encode(["messages" => $messages, "error" => false]);
        // }
        
    }

    function addMessage(Request $request){
        $data = $request->all();
        $newMessage = new message();
        $newMessage->chat_room_id = $data['chat_room_id'];
        $newMessage->user_id = $data['user_id'];
        $newMessage->message = $data['message'];
        $newMessage->save();

        $userID = (int)$data['user_id'];

        $user = User::where('id', '=', $request['user_id'])->select('id')->get();
        // return $user[0]->id;

        $userData = User::find($user[0]->id);

        $info = array();
        $info['user'] = $userData;
        $info['message'] = $request['message'];
        $info['chat_room_id'] = $request['chat_room_id'];

        $res = array();
        $res['title'] = "Google Cloud Messaging";
        $res['is_background'] = FALSE;
        $res['flag'] = 2;
        $res['data'] = $newMessage;

        $gcm = new GCM();
        $gcm->send($userData['firebaseID'], $res);


        echo json_encode(['message' => $newMessage,"user" =>$userData,  "error" => false]);
    }
}
