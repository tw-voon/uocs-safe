<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\report_posts;
use App\Model\locations;
use App\Model\comments;
use App\Model\approve_handler;
use App\Model\activity_handler;
use App\User;
use App\Http\Controllers\GCM;
use App\Http\Controllers\Push;
use App\Http\Controllers\Helper\helper;
use File;
use Carbon\Carbon;
use DB;

class report_post extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new helper();
    }

    public function index(Request $request)
    {
    	$title = $request->input('title');
    	$desc = $request->input('description');
    	$images = $request->input('image');
    	$userID = $request->input('userID');
    	$typeID = $request->input('typeID');
        $location_name = $request->input('location_name');
        $latitute = $request->input('location_latitude');
        $longitute = $request->input('location_longitude');

    	$myDate = date("Y-m-d");
        $myTime = date("h-i-sa");
    	$serverPath = "http://" . $_SERVER['SERVER_ADDR'] ."/uocs-safe/public/images";
    	// return $mytime;


    	$image = base64_decode($images);
		$image_name= $userID. "-" . $myDate . $myTime . '.png';
		$path = public_path() . "/images/". $userID ."/".$image_name;
		$dir = public_path() . "/images/". $userID ."/";

        // return $_SERVER['SERVER_ADDR'];

        // return $image_name;
		

		if(!File::exists($dir)) {
    		$result = File::makeDirectory(public_path() . "/images/". $userID ."/", 0777, true);
    		// return "exist".$result;
		}

		$result2 = file_put_contents($path, $image);
		// return $result2;

        $newLocation = new locations();
        $newLocation->location_name = $location_name;
        $newLocation->location_latitute = $latitute;
        $newLocation->location_longitute = $longitute;
        $newLocation->save();
        
		
    	$newPost = new report_posts();
    	$newPost->user_ID = $userID;
    	$newPost->type_ID = $typeID;
    	$newPost->approve_ID = "true";
        $newPost->approve_status = 1;
        $newPost->location_ID = $newLocation->id;
    	$newPost->report_Title = $title;
    	$newPost->report_Description = $desc;
    	$newPost->image = $serverPath."/".$userID."/".$image_name;

    	$status = $newPost->save();

        $post_handler = new approve_handler();
        $post_handler->report_id = $newPost->report_ID;
        $post_handler->status_id = 3;
        $post_handler->save();

        $this->helper->keep_activity($userID, $userID, 'Report', $newPost->report_ID);

    	if($status){
    		
    		return "Success";
    	}
    	else
    		return "Fail";
    	// return ['data'=>$newPost->save();, 'text'=>"Hello world"];
    }

    function getReport(Request $request){

        $userID = $request->input('userID');

        $report = DB::table('report')
            ->join('users', 'report.user_ID', '=', 'users.id')
            ->join('location', 'report.location_ID', '=', 'location.id')
            ->where('report.approve_status', 1)
            ->orderby('report.created_at', 'desc')
            ->get();

        return response()->json($report);
    }

    function getSingleReport(Request $request){

        $reportID = $request->input('report_id');

        $report = DB::table('report')
            ->join('users', 'report.user_ID', '=', 'users.id')
            ->join('location', 'report.location_ID', '=', 'location.id')
            ->where('report.report_ID', $reportID)
            ->get();

        return response()->json($report);

    }

    function addComment(Request $request){

        $report_id = $request->input('report_id');
        $user_id = $request->input('user_id');
        $comment = $request->input('comment');
        $i = 0;

        $newComment = new comments();
        $newComment->report_id = $report_id;
        $newComment->user_id = $user_id;
        $newComment->comment_msg = $comment;

        if($status = $newComment->save()){
            $user = report_posts::where('report_id', '=', $report_id)
                    ->select('user_id')->get();

            while($i < count($user)){
                if($user_id != $user[$i]->user_id){
                    $userFIrebaseID = User::find($user[$i]->user_id);
                    $info = array();
                    $info['message'] = $userFIrebaseID['name']." has commented on your report";
                    $info['report_id'] = $request['report_id'];
                    $info['created_at'] = date('Y-m-d G:i:s');

                    $push = new Push();
                    $push->setTitle("New Comment");
                    $push->setIsBackground(FALSE);
                    $push->setFlag(3);
                    $push->setData($info);

                    $gcm = new GCM();
                    $status = $gcm->send($userFIrebaseID['firebaseID'], $push->getPush());
                    $this->helper->keep_activity($user_id, $user[$i]->user_id, "Comment", $report_id);
                }
                $i++;            
            }

            $comments = DB::table('comments')
            ->select('comments.*', 'users.name', 'users.id as user_id', 'users.avatar_link')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.report_id', $report_id)
            ->where('comments.id', $newComment->id)
            ->get();

            return response()->json($comments);
        }
        else {
            return "Fail";
        }

    }

    function getComment(Request $request){

        $report_id = $request->input('report_id');

        $comments = DB::table('comments')
            ->select('comments.*', 'users.name', 'users.id as user_id', 'users.avatar_link')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->where('comments.report_id', $report_id)
            ->orderby('comments.created_at', 'asc')
            ->get();

        return response()->json($comments);
    }
}
