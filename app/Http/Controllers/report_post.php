<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\report_posts;
use App\Model\locations;
use File;
use Carbon\Carbon;
use DB;

class report_post extends Controller
{
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

    	if($status){
    		
    		return ['text'=>"Success"];
    	}
    	else
    		return ['text'=>"Success"];
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

    function getLocation(Request $request){
        // $userID = $request->input('userID');

        // $report = DB::table('report')
        //         ->join('location', 'report.location_ID')
    }
}
