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
        // Get unique patients with their test registrations
        $patients = PatientTestRegistration::with([
            'testable.patient',
            'labTest',
            'doctor',
            'branch'
        ])
        ->latest()
        ->get()
        ->groupBy(function($registration) {
            return $registration->testable->patient->id ?? 'unknown';
        });

        return view('pages.laboratory.results.patients', compact('patients'));
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
            // Update or create results based on ref_no and lab_parameter_id
            foreach ($request->results as $parameterId => $resultValue) {
                $existingResult = PatientTestResult::where('ref_no', $request->ref_no)
                    ->where('lab_parameter_id', $parameterId)
                    ->first();
                
                if ($existingResult) {
                    // Update existing result
                    $existingResult->update(['result' => $resultValue]);
                } else {
                    // Create new result entry
                    PatientTestResult::create([
                        'patient_id' => $request->patient_id ?? 1, // Fallback if not provided
                        'ref_no' => $request->ref_no,
                        'lab_parameter_id' => $parameterId,
                        'result' => $resultValue,
                        'test_registration_id' => $request->test_registration_id,
                    ]);
                }
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
     * Show test results for a specific registration
     */
    public function showTestResults($registration_id)
    {
        $registration = PatientTestRegistration::with([
            'testable.patient',
            'labTest.parameters',
            'doctor',
            'branch'
        ])->findOrFail($registration_id);

        $patient = $registration->testable->patient ?? null;
        
        if (!$patient) {
            abort(404, 'Patient not found for this registration');
        }

        // Get all registrations for this patient to show in the side panels
        $patientId = $patient->id;
        $completedTests = PatientTestRegistration::where('patient_id', $patientId)
            ->where('status', 'completed')
            ->with(['labTest', 'testable.patient'])
            ->get();

        $pendingTests = PatientTestRegistration::where('patient_id', $patientId)
            ->where('status', 'pending')
            ->with(['labTest', 'testable.patient'])
            ->get();

        // Use the specific registration as the first test
        $firstTest = $registration;
        $firstTestResults = collect();
        
        if($firstTest){
            // Load existing results if any
            $firstTestResults = PatientTestResult::where('test_registration_id', $firstTest->id)
                ->with('parameter')
                ->get();
            
            // If no results exist, create empty result entries for all parameters
            if($firstTestResults->isEmpty() && $firstTest->labTest && $firstTest->labTest->parameters) {
                foreach($firstTest->labTest->parameters as $parameter) {
                    $firstTestResults->push(new \App\Models\PatientTestResult([
                        'lab_parameter_id' => $parameter->id,
                        'parameter' => $parameter,
                        'unit' => $parameter->unit,
                        'normal_range' => $parameter->normal_range,
                        'result' => null
                    ]));
                }
            }
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
            $test = PatientTestRegistration::with(['labTest', 'testable.patient'])->findOrFail($test_registration_id);

            $results = PatientTestResult::where('test_registration_id', $test_registration_id)
                ->with('parameter')
                ->get();

            // If no results exist, create empty result entries for all parameters
            if($results->isEmpty() && $test->labTest) {
                $parameters = \App\Models\LabTestParameter::where('test_id', $test->labTest->id)->get();
                foreach($parameters as $parameter) {
                    $results->push(new \App\Models\PatientTestResult([
                        'lab_parameter_id' => $parameter->id,
                        'parameter' => $parameter,
                        'unit' => $parameter->unit,
                        'normal_range' => $parameter->normal_range,
                        'result' => null
                    ]));
                }
            }

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
            ->with(['labTest', 'testable.patient'])
            ->first();

        $testName = $testRegistration->labTest->name ?? '—';

        // Return view with results and auto print
        return view('pages.laboratory.reports.lab_report', compact('patient', 'results', 'testRegistration', 'testName'));
    }
}
