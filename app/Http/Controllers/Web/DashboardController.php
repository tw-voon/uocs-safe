<?php
namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\report_posts;
use App\Model\mobile_user;
use App\Model\approve_handler;
use App\Model\report_types;
use App\Model\report_handler;
use App\Http\Controllers\Helper\helper;
use App\Http\Controllers\Web\EmailController;
use Mapper;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller{

	private $helper;

	public function __construct()
    {
        $this->helper = new helper();
    }

	function index(){

		$locations = report_posts::with('location')->get();

		Mapper::map(1.464945, 110.426859, ['zoom' => 15, 'fullscreenControl' => false, 'center' => true, 'marker' => false, 'cluster' => true, 'clusters' => ['center' => false, 'zoom' => 15, 'size'=> 4], 'language' => 'en']);

		foreach ($locations as $value) {
				Mapper::informationWindow(
			    $value->location->location_latitute, 
			    $value->location->location_longitute, 
			    '<div class="infowin"><h3>'.$value->report_Title.'</h3><p>'.$value->report_Description.'</p></div>',
			    [
			        'title' => $value->location->location_name,
			        'animation' => 'NONE'
			    ]
			);
		}

		$unapprove = approve_handler::where('status_id', 3)->count();
		$users = mobile_user::all()->count();

		$item = report_posts::groupBy('type_ID')
						->orderBy('count', 'desc')
				    	->get(['type_ID', DB::raw('count(type_ID) as count')]);

		$trend = report_types::find($item[0]->type_ID);
		$types = report_types::all();
		$isAutoReport = report_handler::select('report_type.typeName', 'report_type.isAutoReport', 
			DB::raw('count(report_handler.type_id) as count'))
			->join('report_type', 'report_type.id', 'report_handler.type_id')
			->groupBy('report_type.typeName', 'report_type.isAutoReport')
			->orderBy('count', 'desc')
			->where('report_handler.reported', 0)
			->where('report_type.isAutoReport', '!=', 0)
			->distinct()
			->get();

		return view('dashboard.index', compact('unapprove', 'users', 'trend', 'types', 'isAutoReport'));
	}

	function filter(Request $request){

		$unapprove = approve_handler::where('status_id', 3)->count();
		$users = mobile_user::all()->count();
		$types = report_types::all();

		$isAutoReport = report_handler::select('report_type.typeName', 'report_type.isAutoReport', 
			DB::raw('count(report_handler.type_id) as count'))
			->join('report_type', 'report_type.id', 'report_handler.type_id')
			->groupBy('report_type.typeName', 'report_type.isAutoReport')
			->orderBy('count', 'desc')
			->where('report_handler.reported', 0)
			->where('report_type.isAutoReport', '!=', 0)
			->distinct()
			->get();

		$item = report_posts::groupBy('type_ID')
						->orderBy('count', 'desc')
						->limit(1)
				    	->get(['type_ID', DB::raw('count(type_ID) as count')]);

		$trend = report_types::find($item[0]->type_ID);

		if($request->input('filter') != 0)
			$locations = report_posts::where('type_ID', $request->input('filter'))->with('location')->get();
		else 
			$locations = report_posts::with('location')->get();

		Mapper::map(1.464945, 110.426859, ['zoom' => 15, 'fullscreenControl' => false, 'center' => true, 'marker' => false, 'cluster' => true, 'clusters' => ['center' => false, 'zoom' => 15, 'size'=> 4], 'language' => 'en']);

		foreach ($locations as $value) {

				Mapper::informationWindow(
			    $value->location->location_latitute, 
			    $value->location->location_longitute, 
			    '<div class="infowin"><h3>'.$value->report_Title.'</h3><p>'.$value->report_Description.'</p></div>',
			    [
			        'title' => $value->location->location_name,
			        'animation' => 'NONE'
			    ]
			);
		}

		return view('dashboard.index', compact('unapprove', 'users', 'trend', 'types', 'item', 'isAutoReport'));

	}

	function report(){

		$toReport = report_handler::where('reported', 0)->with('report', 'type')->get();

		return view('email.select', compact('toReport'));
		// Mail::to('to@example.com')->send(new EmailController());
		// return redirect()->route('dashboard.index');

	}

	function send(Request $request){
		
		$report = $request->input('report');
		$ids = $request->input('id');
		$selected = array();

		foreach ($report as $key => $value) {
			
			if($value == 1)
				array_push($selected, $ids[$key]);
		}

		$select = report_handler::whereIn('report_handler.report_id', $selected)->with('report', 'report.location', 'type')->get();

		Mail::to('to@example.com')->send(new EmailController($select));
		$this->helper->mark_report($selected);
		return redirect()->route('dashboard.index');

		// var_dump($select);
	}
}