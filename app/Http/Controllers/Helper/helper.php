<?php

namespace App\Http\Controllers\Helper;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Model\message;
use App\Model\chat_rooms;
use App\Model\chat_handler;
use App\Model\report_posts;
use App\Http\Controllers\GCM;
use App\Http\Controllers\Push;
use App\Model\activity_handler;
use Validator;
use DB;
use File;
use Carbon\Carbon;

class helper extends Controller
{
	/* Helper function that keep all the user activity*/
    function keep_activity($user_done, $on_user, $action_name, $report_id)
    {
    	$newActivity = new activity_handler();

    	switch ($action_name) {

    		case 'Comment':
    			$done_by = User::find($user_done);
    			$report = report_posts::find($report_id);
    			$newActivity->action_done_by = $user_done;
    			$newActivity->action_done_on = $on_user;
    			$newActivity->report_id = $report_id;
    			$newActivity->action_name = "<b>".$done_by->name . "</b> has commented on your <b>". $report->report_Title . "'s</b> post.";
    			$newActivity->save();
    			break;

            case 'Report':
                $done_by = User::find($user_done);
                $report = report_posts::find($report_id);
                $newActivity->action_done_by = $user_done;
                $newActivity->action_done_on = $on_user;
                $newActivity->report_id = $report_id;
                $newActivity->action_name = "You have reported a new report: <b>".$report->report_Title."</b>";
                $newActivity->save();
                break;

    		case 'Approved':
    			$report = report_posts::find('report_id');
    			$newActivity->action_done_on = $on_user;
    			$newActivity->report_id = $report_id;
    			$newActivity->action_name = "Your <b>" . $report->report_Title . "</b> had been approved.";
    			$newActivity->save();
    			break;

    		case 'Canceled':
    			$report = report_posts::find('report_id');
    			$newActivity->action_done_on = $on_user;
    			$newActivity->report_id = $report_id;
    			$newActivity->action_name = "Your <b>" . $report->report_Title . "</b> had been canceled.";
    			$newActivity->save();
    			break;
    		
    		default:
    			break;
    	}
    	
    
    }
}
