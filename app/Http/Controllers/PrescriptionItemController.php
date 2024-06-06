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

     public function changeStatus(Request $request, PrescriptionItem $prescriptionItem)
    {
        // Validate the input
        $validatedData = $request->validate([
            'is_delivered' => 'required',
        ]);

        // Update the prescription
        $prescriptionItem->update($validatedData);

        // Redirect to the prescriptions index page with a success message
        return redirect()->back()->with('success', 'prescription updated successfully.');
    }
}
