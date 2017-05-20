<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\safety_tips;
use App\Model\tip_categories;
use view, Validator, Redirect;

class TipDetailsController extends Controller
{
    function index($id){

    	$tips = safety_tips::where('category_id', $id)->paginate(10);
    	$name = tip_categories::find($id);
    	return view('tip_details.index', compact('tips', 'name'));

    }

    function create($id){

    	$name = tip_categories::find($id);
    	return view('tip_details.add', compact('name'));

    }

    function edit($id){

    	$name = safety_tips::find($id);
    	return view('tip_details.edit', compact('name'));

    }

    function store(Request $request, $id){

    	$validator = Validator::make($request->all(), [
            'tip_name' => 'required',
            'tip_desc' => 'required',
        ]);

        if($validator->fails()){
            // var_dump($validator->messages());
            return Redirect::to('tip_details/'.$id.'/create')->withErrors($validator);
        }

        $tips = new safety_tips();
        $tips->tip_name = $request->input('tip_name');
        $tips->tip_desc = $request->input('tip_desc');
        $tips->category_id = $id;
        $tips->status = $request->input('status');

        if($tips->save())
        	return redirect()->route('tip_category.index', $id)->with('success','Data Has been Saved!');
    	else 
    		return redirect()->route('tip_category.index', $id)->with('fail','Fail to update data!');

    }

    function update(Request $request, $id){

    	$validator = Validator::make($request->all(), [
            'tip_name' => 'required',
            'tip_desc' => 'required',
        ]);

        if($validator->fails()){
            // var_dump($validator->messages());
            return Redirect::to('tip_details/'.$id.'/edit')->withErrors($validator);
        }

        $tips = safety_tips::find($id);
        $tips->tip_name = $request->input('tip_name');
        $tips->tip_desc = $request->input('tip_desc');
        $tips->status = $request->input('status');

        if($tips->update())
        	return redirect()->route('tip_category.index', $tips->category_id)->with('success','Data Has been Saved!');
    	else 
    		return redirect()->route('tip_category.index', $tips->category_id)->with('fail','Fail to update data!');

    }

    function destroy($id){

    	$tips = safety_tips::find($id);
    	$category_id = $tips->category_id;

        if($tips->delete())
        	return redirect()->route('tip_category.index', $category_id)->with('success','Data Has been Deleted!');
    	else 
    		return redirect()->route('tip_category.index', $category_id)->with('fail','Fail to delete data!');

    }
}
