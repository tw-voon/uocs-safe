<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\report_posts;
use App\Model\locations;
use App\Model\comments;
use App\Model\approve_handler;
use App\Model\activity_handler;
use App\Model\mobile_user;
use App\Model\report_handler;
use App\Model\report_types;
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
    	// $serverPath = "http://" . $_SERVER['SERVER_ADDR'] ."/uocs-safe/public/images";
        $serverPath = "http://" . $_SERVER['SERVER_NAME'] ."/uocs-safe/public/images";
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
    	$newPost->approve_ID = 0;
        $newPost->approve_status = 3;
        $newPost->location_ID = $newLocation->id;
    	$newPost->report_Title = $title;
    	$newPost->report_Description = $desc;
    	$newPost->image = $serverPath."/".$userID."/".$image_name;

    	$status = $newPost->save();

        $post_handler = new approve_handler();
        $post_handler->report_id = $newPost->id;
        $post_handler->status_id = 3;
        $post_handler->save();

        $isReported = report_types::find($typeID);
        if($isReported->isAutoReport == 1){
            /*If this reported type is set to Auto Report then it will be store in report handler table
            for the purpose of keep track whether this report had been reported to the authority*/
            $report_handler = new report_handler();
            $report_handler->report_id = $newPost->id;
            $report_handler->type_id = $typeID;
            $report_handler->reported = 0;
            $report_handler->save();
        }
        

        $this->helper->keep_activity($userID, $userID, 'Report', $newPost->id);

    	if($status)
    		return "Success";
    	else
    		return "Fail";

    }

    function getReport(Request $request){

        $userID = $request->input('userID');

        $report = DB::table('report')
            ->select('report.id as ids', 'report.*', 'mobile_user.*', 'location.*')
            ->join('mobile_user', 'report.user_ID', '=', 'mobile_user.id')
            ->join('location', 'report.location_ID', '=', 'location.id')
            ->where('report.approve_status', 1)
            ->orderby('report.created_at', 'desc')
            ->get();

        return response()->json($report);
    }

    function getSingleReport(Request $request){

        $reportID = $request->input('report_id');

        $report = DB::table('report')
            ->select('report.id as ids', 'report.*', 'mobile_user.*', 'location.*', 'approve_handler.*')
            ->join('mobile_user', 'report.user_ID', '=', 'mobile_user.id')
            ->join('location', 'report.location_ID', '=', 'location.id')
            ->join('approve_handler', 'report.id', '=', 'approve_handler.report_id')
            ->where('report.id', $reportID)
            ->get();

        return response()->json($report);

    }

    function addComment(Request $request){

        $report_id = $request->input('report_id');
        $user_id = $request->input('user_id');
        $comment = $request->input('comment');
        $i = 0;

        $currentUser = mobile_user::find($user_id);

        $newComment = new comments();
        $newComment->report_id = $report_id;
        $newComment->user_id = $user_id;
        $newComment->comment_msg = $comment;

        if($status = $newComment->save()){
            $user = report_posts::where('id', '=', $report_id)
                    ->select('user_id')->get();

            while($i < count($user)){
                if($user_id != $user[$i]->user_id){
                    $userFIrebaseID = mobile_user::find($user[$i]->user_id);
                    $info = array();
                    $info['message'] = $currentUser->name." has commented on your report";
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
            ->select('comments.*', 'mobile_user.name', 'mobile_user.id as user_id', 'mobile_user.avatar_link')
            ->join('mobile_user', 'comments.user_id', '=', 'mobile_user.id')
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
            ->select('comments.*', 'mobile_user.name', 'mobile_user.id as user_id', 'mobile_user.avatar_link')
            ->join('mobile_user', 'comments.user_id', '=', 'mobile_user.id')
            ->where('comments.report_id', $report_id)
            ->orderby('comments.created_at', 'asc')
            ->get();

        return response()->json($comments);
    }
}
