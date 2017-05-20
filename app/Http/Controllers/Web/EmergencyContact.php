<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\emergency_contacts;
use view, Validator, Redirect;

class EmergencyContact extends Controller
{

    function index(){
    	$contacts = emergency_contacts::paginate(10);
    	return view('emergency_contact.index', compact('contacts'));
    }

    function create(){

        return view('emergency_contact.add');

    }

    function store(Request $request){

        $validator = Validator::make($request->all(), [
            'contact_number' => 'required|unique:emergency_contact',
            'contact_name' => 'required',
            'contact_description' => 'required',
            'status' => 'required'
        ]);

        if($validator->fails()){
            // var_dump($validator->messages());
            return Redirect::to('emergency_contact/create')->withErrors($validator);
        }

        $contact = new emergency_contacts();
        $contact->contact_number = $request->input('contact_number');
        $contact->contact_name = $request->input('contact_name');
        $contact->contact_description = $request->input('contact_description');
        $contact->status = $request->input('status');

        if($contact->save())
           return redirect()->route('emergency_contact.index')->with('success','Data Has been Saved!');
        else 
            return redirect()->route('emergency_contact.index')->with('fail','Data Saved fail!');
    }

    function edit($id){

    	$contacts = emergency_contacts::find($id);
    	return view('emergency_contact.edit', compact('contacts'));

    }

    function update(Request $request, $id){

        $contact = emergency_contacts::find($id);
        $contact->contact_number = $request->input('contact_number');
        $contact->contact_name = $request->input('contact_name');
        $contact->contact_description = $request->input('contact_description');
        $contact->status = $request->input('status');

        if($contact->save())
    	   return redirect()->route('emergency_contact.index')->with('success','Data Has been Saved!');
        else return redirect()->route('emergency_contact.index')->with('fail','Fail to save your data!');

    }

    function destroy($id){

        $contact = emergency_contacts::find($id);
        
        if($contact->delete())
            return redirect()->route('emergency_contact.index')->with('success','Data Has been Deleted!');
        else return redirect()->route('emergency_contact.index')->with('fail','Fail to delete');

    }
}
