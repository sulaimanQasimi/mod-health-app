<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\LabItem;
use App\Models\LabType;
use Illuminate\Http\Request;

class LabItemController extends Controller
{
    //


    public function getItems($id){
       $lab_items = Lab::find($id)->labItems;
       $lab = Lab::findOrFail($id);
       return view('pages.appointments.lab_items',compact('lab_items','lab'));
    }

    public function updateStatus($id)
    {
        $item = LabItem::findOrFail($id);
        $item->status = $item->status == '1' ? '0' : '1';
        $item->save();

        return redirect()->back()->with('success', localize('global.lab_item_status_updated_successfully.'));
    }

    public function deleteItem($id)
    {
        $item = LabItem::findOrFail($id);
        $lab = $item->lab;

        $item->delete();

        // Check if the lab has any other items
        if ($lab->labItems()->count() === 0) {
            $lab->delete();
            return redirect()->back()->with('success', localize('global.lab_deleted_successfully.'));
        }

        return redirect()->back()->with('success', localize('global.lab_item_deleted_successfully.'));
    }

    public function edit(LabItem $item)
    {
        $lab_types = LabType::all();
        return view('pages.appointments.lab_item_edit', compact('item','lab_types'));
    }

    public function update(Request $request, LabItem $item)
    {
        $request->validate([
            'lab_type_id' => 'required|string|max:255',
        ]);

        $item->update($request->all());

        return redirect()->route('appointments.index')->with('success', 'Lab item updated successfully.');
    }
}
