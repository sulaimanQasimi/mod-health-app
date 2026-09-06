<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScanCodeController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('ScanCode', [
            'error' => $request->session()->get('error'),
            'urls' => [
                'search' => route('scan-code.search'),
                'patients' => route('patients.index'),
            ],
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'string', 'max:50'],
        ]);

        $patientId = trim($validated['patient_id']);

        $patient = Patient::query()
            ->where('id', $patientId)
            ->where('branch_id', auth()->user()->branch_id)
            ->first();

        if ($patient) {
            return redirect()->route('patients.show', $patient);
        }

        return redirect()
            ->route('scan-code')
            ->with('error', localize('global.patient_not_found'));
    }
}
