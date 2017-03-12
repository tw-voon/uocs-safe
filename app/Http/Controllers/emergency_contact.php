<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\emergency_contacts;

class emergency_contact extends Controller
{
    function index(){

    	return response()->json(emergency_contacts::all());

    }
}
