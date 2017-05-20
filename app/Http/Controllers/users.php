<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\mobile_user;
use App\Model\message;
use App\Model\chat_rooms;
use App\Model\chat_handler;
use App\Model\activity_handler;
use App\Http\Controllers\GCM;
use App\Http\Controllers\Push;
use App\Http\Controllers\Helper\helper;
use Validator;
use DB;
use File;
use Carbon\Carbon;

class users extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new helper();
    }

    function login(Request $request)
    {

        // return json_encode($request);

    	if (mobile_user::where('name', '=', $request['name'])->exists()) 
    	{
    		$userID = mobile_user::where('name', '=', $request['name'])->select('id','password')->get();
    		$user = mobile_user::find($userID[0]->id);
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
        // $serverPath = "http://" . $_SERVER['SERVER_ADDR'] ."/uocs-safe/public/profile";
        $serverPath = "http://" . $_SERVER['SERVER_NAME'] ."/uocs-safe/public/profile";

        $image = base64_decode($images);
        $image_name= $userID. "-" . $myDate . $myTime . '.png';
        $path = public_path() . "/profile/". $userID ."/".$image_name;
        $dir = public_path() . "/profile/". $userID ."/";

        if(!File::exists($dir)) {
            $result = File::makeDirectory(public_path() . "/profile/". $userID ."/", 0777, true);
        }

        $result2 = file_put_contents($path, $image);
        $user = mobile_user::find((int)$userID);
        $user->avatar_link = $serverPath."/".$userID."/".$image_name;
        $user->update();

        return json_encode(["status" => "success", "avatar" => $user->avatar_link]);

    }

    function register_user(Request $request)
    {
        $data = $request->all();

        $v = Validator::make($data, [
            'name' => 'required|unique:mobile_user',
            'password' => 'required|min:6'
        ]);

        if ($v->fails()) {
            return $v->messages()->first();
        }

        $users = new mobile_user();
        $users->name = $data['name'];
        $users->password = $data['password'];
        $status = $users->save();

        if($status)
            return ["status" => "success", "data" => mobile_user::find($users->id)];
        else
            return ["status" => "Something went wrong"];
    }

    function register_key(Request $request)    
    {
        $data = $request->all();
        $user = mobile_user::find($data['userID']);
        $user->firebaseID = $data['token'];
        return json_encode(["status" => $status = $user->update()]);
    }

    function search_user(Request $request){
        $data = $request->all();
        $response = array();

        if($request['name'] != null)
        if (mobile_user::where('name', 'like', '%'.$request['name'].'%')->exists())
        {
            $userID = mobile_user::where('name', 'like', '%'.$request['name'].'%')->select('id')->get();
            foreach ($userID as $id) {
                $user = mobile_user::find($id);
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
                    ->select('chat_rooms.chat_room_id', 'chat_rooms.name', 'chat_rooms.created_at')
                    ->join('chat_rooms', 'chat_rooms.chat_room_id', 'chat_handler.chat_room_id')
                    ->where('chat_handler.user_id', $user_id)
                    ->orwhere('chat_handler.target_user_id', $user_id)
                    ->orderBy('chat_rooms.created_at', 'desc')
                    ->distinct()
                    ->get();

        echo json_encode(["chat_rooms" => $chat_room, "error" => false]);
    }

    function addUser(Request $request){

        $target_user_id = $request->input('target_user_id');
        $user_id = $request->input('user_id');
        $group_name = $request->input('group_name');
        $found = true;


        $process_1 = trim($target_user_id,'[]');
        $process_2 = preg_replace('/\s+/', '', $process_1);
        $result = explode(',',$process_2);

        /*Validate whether this group has been created before*/
        foreach ($result as $value) {

            $find_room = chat_handler::where('target_user_id', $value)
                    ->where('user_id', $user_id)
                    ->get();

            if(count($find_room) == 0)
                $found = false;

        }

        if(!$found){

            /*Create the room first*/
            $chat_rooms = new chat_rooms();
            $chat_rooms->name = $group_name;
            $chat_rooms->save();

            foreach ($result as $id) {

                $chat_handler = new chat_handler();
                $chat_handler->user_id = $user_id;
                $chat_handler->target_user_id = (int)$id;
                $chat_handler->chat_room_id = $chat_rooms->chat_room_id;
                $chat_handler->save();

            }

        }
        if(!$found)
            return json_encode(["target_user_id" => $user_id, "user_id" => $user_id]);
        else return "room found";
        
    }

    function fetchSingleChatRoom(Request $request){

        // concat message with user details together
        $int = 0;

        $messageID = DB::table('messages')
                        ->where('chat_room_id', $request['chat_room_id'])
                        ->pluck('message_id');

        $messageDetails = DB::table('messages')
                        ->select('messages.message_id', 'messages.message', 'messages.created_at', 'mobile_user.id', 'mobile_user.name')
                        ->join('mobile_user', 'messages.user_id', 'mobile_user.id')
                        ->orderBy('messages.created_at', 'asc')
                        ->where('messages.chat_room_id', $request['chat_room_id'])
                        ->get();

                        if(count($messageDetails)!=0)
                            return ["messages" => $messageDetails, "error" => false];
                        else return json_encode(["error" => true, "messages" => "No Message"]);        
    }

    function addMessage(Request $request){
        $data = $request->all();
        $newMessage = new message();
        $newMessage->chat_room_id = $data['chat_room_id'];
        $newMessage->user_id = $data['user_id'];
        $newMessage->message = $data['message'];
        $newMessage->save();

        $i = 0;
        $users = array();

        $userID = (int)$data['user_id'];

        $user = mobile_user::where('id', '=', $request['user_id'])->select('id')->get();

        $chatRoomTargetUser = chat_handler::where('chat_room_id', $request['chat_room_id'])
                            ->distinct()
                            ->pluck('target_user_id');

        foreach ($chatRoomTargetUser as $key => $value) {
            if($value != $request['user_id']){
                if(!in_array($value, $users))
                    array_push($users, $value);
            }
        }

        $chatRoomUser = chat_handler::where('chat_room_id', $request['chat_room_id'])->select('user_id')->distinct()->pluck('user_id');

        foreach ($chatRoomUser as $key => $value) {
            if($value != $request['user_id']){
                if(!in_array($value, $users))
                    array_push($users, $value);
            }
        }
        
        // return $users;
        $userData = mobile_user::find($user[0]->id);
        // echo $chatRoomUser;
        while ($i < count($users)) {
        
        $userFIrebaseID = mobile_user::find($users[$i]);
        $info = array();
        $info['user'] = $userData;
        $info['message'] = $newMessage;
        $info['chat_room_id'] = $request['chat_room_id'];
        $info['created_at'] = date('Y-m-d G:i:s');

        $push = new Push();
        $push->setTitle($userData->name);
        $push->setIsBackground(FALSE);
        $push->setFlag(1);
        $push->setData($info);

        $gcm = new GCM();
        $gcm->send($userFIrebaseID['firebaseID'], $push->getPush());
        // print_r($userFIrebaseID);
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

        $user = mobile_user::where('id', '=', $request['user_id'])->select('id')->get();
        // return $user[0]->id;

        $userData = mobile_user::find($user[0]->id);

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

    function getOwnReport(Request $request){

        $own_id = $request->input('user_id');
        $report = DB::table('approve_handler')
                ->join('status_table', 'status_table.id', 'approve_handler.status_id')
                ->join('report', 'report.id', 'approve_handler.report_id')
                ->where('report.user_ID', $own_id)
                ->get();

        return json_encode($report);
    }

    function get_Activity(Request $request){

        $data = $request->all();

        $activity = activity_handler::where('action_done_on', $data['user_id'])
                    ->select('mobile_user.id', 
                        'mobile_user.avatar_link',
                        'activity_handler.report_id', 
                        'activity_handler.action_name',
                        'activity_handler.created_at',
                        'activity_handler.action_done_by')
                    ->join('mobile_user', 'mobile_user.id', 'activity_handler.action_done_by')
                    ->orderBy('activity_handler.created_at', 'desc')
                    ->get();
        return json_encode($activity);

        /*$activity = DB::table('activity_handler')
                    ->where('action_done_on', $data['user_id'])
                    ->pluck('action_done_on', 'action_done_by');

        foreach ($activity as $key => $value) {
            array_push($user_id, $key);
            array_push($user_id, $value);
        }

        foreach ($user_id as $value) {
            $data = users::find($value);

        }
        return json_encode($user_id);*/
    }
}
