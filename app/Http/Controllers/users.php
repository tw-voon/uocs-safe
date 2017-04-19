<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Model\message;
use App\Model\chat_rooms;
use App\Model\chat_handler;
use App\Http\Controllers\GCM;
use App\Http\Controllers\Push;
use Validator;
use DB;
use File;
use Carbon\Carbon;

class users extends Controller
{
    function login(Request $request)
    {

        // return json_encode($request);

    	if (User::where('name', '=', $request['name'])->exists()) 
    	{
    		$userID = User::where('name', '=', $request['name'])->select('id','password')->get();
    		$user = User::find($userID[0]->id);
    		if($this->verifyUser($user, $request))
    			return json_encode(["status" => "success", "data" => $user]);
    		else
    			return json_encode(["status"=>"fail", "data" => "Wrong email/password"]);

		}
		else
		{
			return json_encode(["status" => "fail", "data" => "User does not exist, Please register or continue as Guest User"]);
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

    function addAvatar(Request $request){

        $images = $request->input('image');
        $userID = $request->input('user_id');

        $myDate = date("Y-m-d");
        $myTime = date("h-i-sa");
        $serverPath = "http://" . $_SERVER['SERVER_ADDR'] ."/uocs-safe/public/profile";

        $image = base64_decode($images);
        $image_name= $userID. "-" . $myDate . $myTime . '.png';
        $path = public_path() . "/profile/". $userID ."/".$image_name;
        $dir = public_path() . "/profile/". $userID ."/";

        if(!File::exists($dir)) {
            $result = File::makeDirectory(public_path() . "/profile/". $userID ."/", 0777, true);
        }

        $result2 = file_put_contents($path, $image);
        $user = User::find((int)$userID);
        $user->avatar_link = $serverPath."/".$userID."/".$image_name;
        $user->update();

        return json_encode(["status" => "success"]);

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
        return json_encode(["status" => $status = $user->update()]);

        // $user = User::find($data['userID']);
        // // if(count($user) != 0){
        //     $user->firebaseID = $data['token'];
        //     $status = $user->update();
        // // }
        

        // if($status)
        //     return "success";
        // else 
        //     return "fail";

    }

    function search_user(Request $request){
        $data = $request->all();
        $response = array();

        if($request['name'] != null)
        if (User::where('name', 'like', $request['name'])->exists()){
            $userID = User::where('name', 'like', '%'.$request['name'].'%')->select('id')->get();
            foreach ($userID as $id) {
                $user = User::find($id);
                array_push($response, $user);
            }
            
            return json_encode(["status" => "success", "data" => $response]);
        }
        else return json_encode(["status" => "fail", "data" => "User not exists in this application"]);
        else return json_encode(["status" => "fail", "data" => "User not exists in this application"]);
    }


    function fetchChatRoom(Request $request){

        $user_id = $request->input('user_id');

        $chat_room = DB::table('chat_handler')
                    ->join('chat_rooms', 'chat_rooms.chat_room_id', 'chat_handler.chat_room_id')
                    ->where('chat_handler.user_id', $user_id)
                    ->orderBy('chat_rooms.created_at', 'desc')
                    ->get();

        echo json_encode(["chat_rooms" => $chat_room, "error" => false]);
    }

    function addUser(Request $request){

        $user_id = (int)$request->input('user_id');
        $target_user_id = (int)$request->input('target_user_id');
        $user = User::find($target_user_id);

        $chat_validate = DB::table('chat_handler')
                        ->where('user_id', $user_id)
                        ->where('target_user_id', $target_user_id)
                        ->pluck('handler_id');

        if(count($chat_validate) == 0){

            $chat_room = new chat_rooms();
            $chat_handler = new chat_handler();
            $chat_room->name = $user->name;
            $chat_room->save();

            $chat_handler->user_id =$user_id;
            $chat_handler->target_user_id = $target_user_id;
            $chat_handler->chat_room_id = $chat_room->chat_room_id;
            $chat_handler->save();

            return json_encode(["status" => "Success"]);

        } else return json_encode(["status" => "Fail"]);


        
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

        $i = 0;

        $userID = (int)$data['user_id'];

        $user = User::where('id', '=', $request['user_id'])->select('id')->get();
        $chatRoomUser = message::where('chat_room_id', $request['chat_room_id'])->select('user_id')->distinct()->get();
        // return $chatRoomUser[0]->user_id;
        $userData = User::find($user[0]->id);
        // echo $chatRoomUser;
        while ($i < count($chatRoomUser)) {
        
        $userFIrebaseID = User::find($chatRoomUser[$i]->user_id);
        $info = array();
        $info['user'] = $userData;
        $info['message'] = $newMessage;
        $info['chat_room_id'] = $request['chat_room_id'];
        $info['created_at'] = date('Y-m-d G:i:s');

        $push = new Push();
        $push->setTitle("Google Cloud Messaging");
        $push->setIsBackground(FALSE);
        $push->setFlag(1);
        $push->setData($info);

        $gcm = new GCM();
        $gcm->send($userFIrebaseID['firebaseID'], $push->getPush());
        $i++;        
    }      

        echo json_encode(['message' => $push->getPush(),"user" =>$userData,  "error" => false]);
    }

    function testMessage(Request $request){
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
        $info['message'] = $newMessage;
        $info['chat_room_id'] = $request['chat_room_id'];
        $info['created_at'] = date('Y-m-d G:i:s');

        $push = new Push();
        $push->setTitle("Google Cloud Messaging");
        $push->setIsBackground(FALSE);
        $push->setFlag(1);
        $push->setData($info);

        $gcm = new GCM();
        $gcm->send($userData['firebaseID'], $push->getPush());

        echo json_encode(['message' => $push->getPush(),"user" =>$userData,  "error" => false]);
    }
}
