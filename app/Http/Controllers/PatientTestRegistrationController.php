<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\LabTest;
use App\Models\Patient;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\TestCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Patient Test Registration Controller
 * 
 * Handles patient test registration operations
 */
class PatientTestRegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:register-patient-tests');
    }


    /**
     * Get test list for display
     */
    public function getTestList()
    {
        $tests = PatientTestRegistration::with(['testable.patient', 'labTest', 'doctor', 'branch'])
            ->orderBy('id', 'desc')
            ->get();

        return view('pages.laboratory.registrations.index', compact('tests'));
    }



    /**
     * Mark registration as in progress
     */
    public function markInProgress($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->markInProgress();
        
        return redirect()->back()->with('success', 'Test registration marked as in progress.');
    }

    /**
     * Mark registration as completed
     */
    public function markCompleted($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->markCompleted();
        
        return redirect()->back()->with('success', 'Test registration marked as completed.');
    }

    /**
     * Cancel registration
     */
    public function cancel($id)
    {
        $registration = PatientTestRegistration::findOrFail($id);
        $registration->cancel();
        
        return redirect()->back()->with('success', 'Test registration cancelled.');
    }
}