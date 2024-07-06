<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;

class PrescriptionItemController extends Controller
{
    public function getItems($id){
        $prescription_items = PrescriptionItem::where('prescription_id',$id)->get();
        $prescription = Prescription::findOrFail($id);
        $appointment = $prescription->appointment;
        return view('pages.appointments.prescription_items',compact('prescription_items','appointment','prescription'));
     }

     public function changeStatus($id)
    {

        $item = PrescriptionItem::findOrFail($id);
        $item->is_delivered = $item->is_delivered == '1' ? '0' : '1';
        $item->save();

        return redirect()->back()->with('success', localize('global.prescription_item_status_updated_successfully.'));

    }
}
