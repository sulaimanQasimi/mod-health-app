<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;

class PrescriptionItemController extends Controller
{
    public function getItems($id){
        $prescription_items = PrescriptionItem::where('prescription_id',$id)->get();
        return view('pages.appointments.prescription_items',compact('prescription_items'));
     }
}
