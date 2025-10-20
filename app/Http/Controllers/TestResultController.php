<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\Patient;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Test Result Controller
 * 
 * Handles test result operations and reporting
 */
class TestResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-test-results');
    }

    /**
     * Display patient list for test results
     */
    public function patientList()
    {
        $patients = PatientTestRegistration::select('patient_id')
            ->distinct()
            ->with('patient')
            ->get();

        return view('pages.laboratory.registrations.index', compact('patients'));
    }

    /**
     * Update test results via AJAX
     */
    public function ajaxUpdateTestResults(Request $request)
    {
        $request->validate([
            'ref_no' => 'required|string',
            'test_registration_id' => 'required|exists:patient_test_registrations,id',
            'results' => 'required|array',
        ]);

        try {
            // Update all results based on ref_no and lab_parameter_id
            foreach ($request->results as $parameterId => $resultValue) {
                PatientTestResult::where('ref_no', $request->ref_no)
                    ->where('lab_parameter_id', $parameterId)
                    ->update(['result' => $resultValue]);
            }

            // Check if all parameters for this ref_no have results
            $allResults = PatientTestResult::where('ref_no', $request->ref_no)
                ->where('test_registration_id', $request->test_registration_id)
                ->get();

            $allFilled = $allResults->every(fn($r) => $r->result !== null && $r->result !== '');

            if ($allFilled) {
                // Update test registration status to completed
                $test = PatientTestRegistration::find($request->test_registration_id);
                $test->status = 'completed';
                $test->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Results updated successfully.',
                'completed' => $allFilled
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating results: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show test results for a specific patient
     */
    public function showTestResults($patient_id)
    {
        $patient = Patient::findOrFail($patient_id);

        // Completed and Pending tests with lab test info
        $completedTests = PatientTestRegistration::where('patient_id', $patient_id)
            ->where('status', 'completed')
            ->with('labTest')
            ->get();

        $pendingTests = PatientTestRegistration::where('patient_id', $patient_id)
            ->where('status', 'pending')
            ->with('labTest')
            ->get();

        // For first test to show result initially
        $firstTest = $completedTests->first() ?? $pendingTests->first();
        $firstTestResults = [];
        if($firstTest){
           $firstTestResults = PatientTestResult::where('test_registration_id', $firstTest->id)
        ->with('parameter')
        ->get();
        }

        return view('pages.laboratory.results.show', compact(
            'patient',
            'completedTests',
            'pendingTests',
            'firstTest',
            'firstTestResults'
        ));
    }

    /**
     * Load test result via AJAX
     */
    public function ajaxLoadTestResult($test_registration_id)
    {
        try {
            $test = PatientTestRegistration::with('labTest')->findOrFail($test_registration_id);

            $results = PatientTestResult::where('test_registration_id', $test_registration_id)
                ->with('parameter')
                ->get();

            return response()->json([
                'status' => 'success',
                'test' => $test,
                'results' => $results
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Test registration not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load test results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print result by reference number
     */
    public function printResultByRef($ref_no)
    {
        // Load all results for this ref_no
        $results = PatientTestResult::with('parameter', 'patient')
            ->where('ref_no', $ref_no)
            ->get();

        if ($results->isEmpty()) {
            abort(404, 'No results found for this reference number.');
        }

        $patient = $results->first()->patient ?? null;
        $testRegistration = PatientTestRegistration::where('ref_no', $ref_no)
            ->with('labTest')
            ->first();

        $testName = $testRegistration->labTest->name ?? '—';

        // Return view with results and auto print
        return view('pages.laboratory.reports.lab_report', compact('patient', 'results', 'testRegistration', 'testName'));
    }
}
