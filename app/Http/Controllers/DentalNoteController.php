<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalNote;
use Illuminate\Http\Request;

class DentalNoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'note_date' => 'required|date',
            'note_type' => 'required|string',
            'content' => 'required|string',
        ]);

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;
        $note = DentalNote::create($validatedData);

        return redirect()->back()->with('success', localize('global.note_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalNote $dentalNote)
    {
        $validatedData = $request->validate([
            'note_date' => 'required|date',
            'note_type' => 'required|string',
            'content' => 'required|string',
        ]);

        $dentalNote->update($validatedData);

        return redirect()->back()->with('success', localize('global.note_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalNote $dentalNote)
    {
        $dentalNote->delete();

        return redirect()->back()->with('success', localize('global.note_deleted_successfully'));
    }
}
