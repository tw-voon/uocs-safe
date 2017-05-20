<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\Traits\BreadRelationshipParser;
use TCG\Voyager\Http\Controllers\VoyagerBreadController;
use App\Model\custom_data_type;
use App\Model\report_posts;
use App\Model\approve_handler;
use App\Model\status_table;
use App\Model\mobile_user;
use App\Http\Controllers\Push;
use App\Http\Controllers\GCM;
use App\Http\Controllers\Helper\helper;

class ApproveHandler extends Controller
{

    private $helper;

    public function __construct()
    {
        $this->helper = new helper();
    }

    public function index(){

        $blogs =  approve_handler::with('status', 'report.location')->paginate(10);
        return view('report_post.index',['blogs' => $blogs]);

    }

    public function edit($id){

         $report = approve_handler::with('status', 'report.location', 'report.mobileuser', 'report.category')->where('approve_handler.id', $id)->get();
        $status_detail = status_table::all();
        return view('report_post.show', ['report' => $report, 'status_detail' => $status_detail]);

    }

    public function show($id){

        $report = approve_handler::with('status', 'report.location', 'report.mobileuser')->where('approve_handler.id', $id)->get();
        $status_detail = status_table::all();
        return view('report_post.show', ['report' => $report, 'status_detail' => $status_detail]);
    }

    public function update(Request $request,$id){

        $handler = approve_handler::findOrFail($id);
        $handler->status_id = $request->input('approve_status');
        $handler->reason = $request->input('reason');
        $handler->action_taken = $request->input('action_taken');
        $handler->save();

        $report = report_posts::findOrFail($handler->report_id);
        $report->approve_status = $handler->status_id;
        $report->update();

        $status = status_table::findOrFail($handler->status_id);

        $userFIrebaseID = mobile_user::find($report->user_ID);
        $info = array();
        
        if($request->input('approve_status') == 1){
            $action = "Approved";
            $info['message'] = "Your report ". $report->report_Title . " has been approved";
        } else if($request->input('approve_status') == 2){
            $action = "Canceled";
            $info['message'] = "Your report ". $report->report_Title . " has been canceled";
        } else {
            $action = "Pending";
            $info['message'] = "Your report ". $report->report_Title . " has been suspended";
        }
        
        $info['report_id'] = $handler->report_id;
        $info['created_at'] = date('Y-m-d G:i:s');

        $push = new Push();
        $push->setTitle($status->name. " for your report");
        $push->setIsBackground(FALSE);
        $push->setFlag(3);
        $push->setData($info);

        $gcm = new GCM();
        // $status2 = $gcm->send($userFIrebaseID[''], $push->getPush());
        $status2 = $gcm->send($this->helper->getAllToken(), $push->getPush());
        $this->helper->keep_activity($report->user_ID, $report->user_ID, $action, $report->id);

        return redirect()->route('report.index')->with('success','Data Has been Updated!');

    }

    public function destroy(){

    }

    // use BreadRelationshipParser;
    // private $VoyagerBreadController;

    // public function __construct()
    // {
    //     $this->VoyagerBreadController = new VoyagerBreadController();
    // }

    // function index(Request $request){

    // 	$slug = $this->VoyagerBreadController->getSlug($request);

    // 	// GET THE DataType based on the slug
    //     $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

    //     // Check permission
    //     Voyager::canOrFail('browse_'.$dataType->name);

    //     $getter = $dataType->server_side ? 'paginate' : 'get';

    //     // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
    //     if (strlen($dataType->model_name) != 0) {
    //         $model = app($dataType->model_name);

    //         $relationships = $this->getRelationships($dataType);

    //         if ($model->timestamps) {
    //             $dataTypeContent = call_user_func([$model->with($relationships)->latest(), $getter]);
    //         } else {
    //             $dataTypeContent = call_user_func([$model->with($relationships)->orderBy('id', 'DESC'), $getter]);
    //         }
    //         //Replace relationships' keys for labels and create READ links if a slug is provided.
    //         $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
    //     } else {
    //         // If Model doesn't exist, get data from table name
    //         $dataTypeContent = call_user_func([DB::table("report")->join('report_type', 'report_type.typeID', 'report.type_ID'), $getter]);
    //         $model = false;
    //     }

    //     // Check if BREAD is Translatable
    //     $isModelTranslatable = is_bread_translatable($model);

    //     $view = 'voyager::bread.browse';

    //     if (view()->exists("voyager::$slug.browse")) {
    //         $view = "voyager::$slug.browse";
    //     }

    //     return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    // }

    // function show(Request $request, $id){

    //     $slug = $this->VoyagerBreadController->getSlug($request);

    //     $dataType = Voyager::model('DataType')->where('slug', '=', 'approve-handler')->first();

    //     // Check permission
    //     Voyager::canOrFail('read_'.$dataType->name);

    //     $relationships = $this->getRelationships($dataType);
    //     if (strlen($dataType->model_name) != 0) {
    //         $model = app($dataType->model_name);
    //         $dataTypeContent = call_user_func([$model->with($relationships), 'findOrFail'], $id);
    //     } else {
    //         // If Model doest exist, get data from table name
    //         $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
    //     }

    //     //Replace relationships' keys for labels and create READ links if a slug is provided.
    //     $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);

    //     // Check if BREAD is Translatable
    //     $isModelTranslatable = is_bread_translatable($dataTypeContent);

    //     $view = 'voyager::bread.read';

    //     if (view()->exists("voyager::$slug.read")) {
    //         $view = "voyager::$slug.read";
    //     }

    //     return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));

    // }

    // function update(Request $request, $id){

    //     $slug = $this->VoyagerBreadController->getSlug($request);

    //     $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

    //     // Check permission
    //     Voyager::canOrFail('edit_'.$dataType->name);

    //     //Validate fields with ajax
    //     $val = $this->VoyagerBreadController->validateBread($request->all(), $dataType->addRows);

    //     if ($val->fails()) {
    //         return response()->json(['errors' => $val->messages()]);
    //     }

    //     if (!$request->ajax()) {
    //         $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

    //         $this->VoyagerBreadController->insertUpdateData($request, $slug, $dataType->editRows, $data);
    //         $report = report_posts::find($request->report_id);
    //         $report->approve_status = $request->status_id;
    //         $report->update();

    //         return redirect()
    //         ->route("voyager.{$dataType->slug}.edit", ['id' => $id])
    //         ->with([
    //             'message'    => "Successfully Updated {$dataType->display_name_singular}",
    //             'alert-type' => 'success',
    //             ]);
    //     }
    // }

    // public function edit(Request $request, $id)
    // {
    //     $slug = $this->VoyagerBreadController->getSlug($request);

    //     $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

    //     // Check permission
    //     Voyager::canOrFail('edit_'.$dataType->name);

    //     $relationships = $this->getRelationships($dataType);

    //     $dataTypeContent = (strlen($dataType->model_name) != 0)
    //         ? app($dataType->model_name)->with($relationships)->findOrFail($id)
    //         : DB::table($dataType->name)->where('id', $id)->first(); 
    //         // If Model doest exist, get data from table name

    //     // Check if BREAD is Translatable
    //     $isModelTranslatable = is_bread_translatable($dataTypeContent);

    //     $view = 'voyager::bread.edit-add';

    //     if (view()->exists("voyager::$slug.edit-add")) {
    //         $view = "voyager::$slug.edit-add";
    //     }

    //     return view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    // }
}
