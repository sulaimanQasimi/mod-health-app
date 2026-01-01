<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalNote;
use Illuminate\Http\Request;
use HanifHefaz\Dcter\Dcter;

class DentalNoteController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'note_date' => 'required|string',
            'note_type' => 'required|string',
            'content' => 'required|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['note_date'])) {
            try {
                $validatedData['note_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['note_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['note_date'])) {
                    return redirect()->back()->withErrors(['note_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

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
            'note_date' => 'required|string',
            'note_type' => 'required|string',
            'content' => 'required|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['note_date'])) {
            try {
                $validatedData['note_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['note_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['note_date'])) {
                    return redirect()->back()->withErrors(['note_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

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
