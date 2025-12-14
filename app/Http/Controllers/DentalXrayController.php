<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalXray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DentalXrayController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'xray_type' => 'required|string',
            'xray_date' => 'required|date',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dental_xrays', $filename, 'public');
            $validatedData['file_path'] = $path;
        }

        $xray = DentalXray::create($validatedData);

        return redirect()->back()->with('success', localize('global.xray_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalXray $dentalXray)
    {
        $validatedData = $request->validate([
            'xray_type' => 'required|string',
            'xray_date' => 'required|date',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($dentalXray->file_path && Storage::disk('public')->exists($dentalXray->file_path)) {
                Storage::disk('public')->delete($dentalXray->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('dental_xrays', $filename, 'public');
            $validatedData['file_path'] = $path;
        }

        $dentalXray->update($validatedData);

        return redirect()->back()->with('success', localize('global.xray_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalXray $dentalXray)
    {
        // Delete file if exists
        if ($dentalXray->file_path && Storage::disk('public')->exists($dentalXray->file_path)) {
            Storage::disk('public')->delete($dentalXray->file_path);
        }

        $dentalXray->delete();

        return redirect()->back()->with('success', localize('global.xray_deleted_successfully'));
    }
}
