<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionAlternativeItem;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use Illuminate\Http\Request;

class PrescriptionAlternativeItemController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription_id' => 'required|exists:prescriptions,id',
            'prescription_item_id' => 'required|exists:prescription_items,id',
            'medicine_id' => 'required|exists:medicines,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'usage_type_id' => 'required|exists:medicine_usage_types,id',
            'dosage' => 'required|string',
            'frequency' => 'required|string',
            'amount' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        PrescriptionAlternativeItem::create($data);

        return redirect()->back()->with('success', localize('global.alternative_medicine_added_successfully'));
    }

    public function update(Request $request, PrescriptionAlternativeItem $alternativeItem)
    {
        $data = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'usage_type_id' => 'required|exists:medicine_usage_types,id',
            'dosage' => 'required|string',
            'frequency' => 'required|string',
            'amount' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $alternativeItem->update($data);

        return redirect()->back()->with('success', localize('global.alternative_medicine_updated_successfully'));
    }

    public function destroy(PrescriptionAlternativeItem $alternativeItem)
    {
        $alternativeItem->delete();

        return redirect()->back()->with('success', localize('global.alternative_medicine_deleted_successfully'));
    }

    public function selectAlternative(PrescriptionAlternativeItem $alternativeItem)
    {
        // Deselect all other alternatives for this prescription item
        PrescriptionAlternativeItem::where('prescription_item_id', $alternativeItem->prescription_item_id)
            ->update(['is_selected' => 0]);

        // Select this alternative
        $alternativeItem->update(['is_selected' => 1]);

        return redirect()->back()->with('success', localize('global.alternative_medicine_selected_successfully'));
    }

    public function changeStatus(PrescriptionAlternativeItem $alternativeItem)
    {
        $alternativeItem->update([
            'is_delivered' => $alternativeItem->is_delivered == 0 ? 1 : 0
        ]);

        $status = $alternativeItem->is_delivered == 1 ? 'delivered' : 'not_delivered';
        return redirect()->back()->with('success', localize('global.alternative_medicine_' . $status . '_successfully'));
    }
}
