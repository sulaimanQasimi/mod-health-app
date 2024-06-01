<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;

class LabItemController extends Controller
{
    //
    

    public function getItems($id){
       $lab_items = Lab::find($id)->labItems;
       return view('pages.appointments.lab_items',compact('lab_items'));
    }
}
