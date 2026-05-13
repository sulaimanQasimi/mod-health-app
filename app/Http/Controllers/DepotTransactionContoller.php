<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use Illuminate\Http\Request;

class DepotTransactionContoller extends Controller
{
    public function manualImport(Request $request){
        $validatedData = $request->validate([
            'depot_id' => 'required|exists:depots,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'tool_id' => 'nullable|exists:tools,id',
            'medicine_type_id' => 'nullable|exists:medicine_types,id',
            'quantity' => 'nullable|integer|min:1',
            'unit_id' => 'nullable|exists:units,id',
            'batch_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:in,out,transfer',
            'from_depot_id' => 'nullable|exists:depots,id',
            'to_depot_id' => 'nullable|exists:depots,id',
            'notes' => 'nullable|string|max:2000',
        ]);
        $depot = Depot::find($validatedData['depot_id']);
        $transaction = $depot->transactions()->create($validatedData);
        return redirect()->back()->with('success', 'Transaction created successfully');
    }
    public function manualExport(Request $request){
        $validatedData = $request->validate([
            'depot_id' => 'required|exists:depots,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'tool_id' => 'nullable|exists:tools,id',
            'medicine_type_id' => 'nullable|exists:medicine_types,id',
            'quantity' => 'nullable|integer|min:1',
            'unit_id' => 'nullable|exists:units,id',
            'batch_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'transaction_type' => 'required|in:in,out,transfer',
            'from_depot_id' => 'nullable|exists:depots,id',
            'to_depot_id' => 'nullable|exists:depots,id',
            'notes' => 'nullable|string|max:2000',
        ]);
        $depot = Depot::find($validatedData['depot_id']);
        $transaction = $depot->transactions()->create($validatedData);
        return redirect()->back()->with('success', 'Transaction created successfully');
    }
    public function Transfer(Request $request){
        $validatedData = $request->validate([
            'depot_id' => 'required|exists:depots,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'tool_id' => 'nullable|exists:tools,id',
            'medicine_type_id' => 'nullable|exists:medicine_types,id',
            'quantity' => 'nullable|integer|min:1',
            'unit_id' => 'nullable|exists:units,id',
            'batch_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:2000',
        ]);
        $fromDepot = Depot::find($validatedData['from_depot_id']);
        $toDepot = Depot::find($validatedData['to_depot_id']);
        // source transaction
        $transaction = $fromDepot->transactions()->create($validatedData);
        // Ta
        $transaction = $toDepot->transactions()->create([
            'depot_id' => $toDepot->id,
            'medicine_id' => $validatedData['medicine_id'],
            'tool_id' => $validatedData['tool_id'],
            'medicine_type_id' => $validatedData['medicine_type_id'],
            'quantity' => $validatedData['quantity'],
            'unit_id' => $validatedData['unit_id'],
            'batch_number' => $validatedData['batch_number'],
        ]);

        $transaction = $toDepot->transactions()->create([
            'depot_id' => $toDepot->id,
            'medicine_id' => $validatedData['medicine_id'],
            'tool_id' => $validatedData['tool_id'],
            'medicine_type_id' => $validatedData['medicine_type_id'],
            'quantity' => $validatedData['quantity'],
            'unit_id' => $validatedData['unit_id'],
            'batch_number' => $validatedData['batch_number'],
        ]);
        
        return redirect()->back()->with('success', 'Transfer created successfully');
    }
}
