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
    public function patientList(Request $request)
    {
        // Start query builder
        $query = PatientTestRegistration::with([
            'testable.patient',
            'labType.section',
            'labType.category',
            'labType.directLabTestParameters',
            'doctor',
            'branch'
        ]);

        // Apply section-based access control
        $user = auth()->user();

        // Check if user has admin role or permission to see all sections
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            // Filter by user's section_id
            if ($user->section_id) {
                $query->whereHas('labType.section', function($q) use ($user) {
                    $q->where('section_id', $user->section_id);
                });
            }
        }

        // Apply search filter (patient name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('testable.patient', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter based on route or request parameter
        $statusFilter = null;
        if ($request->route()->getName() === 'laboratory.results.pending') {
            $statusFilter = 'pending';
        } elseif ($request->route()->getName() === 'laboratory.results.in-progress') {
            $statusFilter = 'in_progress';
        } elseif ($request->route()->getName() === 'laboratory.results.completed') {
            $statusFilter = 'completed';
        } elseif ($request->filled('status')) {
            $statusFilter = $request->status;
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply lab type section filter
        if ($request->filled('lab_type_section_id')) {
            $query->whereHas('labType', function($q) use ($request) {
                $q->where('section_id', $request->lab_type_section_id);
            });
        }

        // Apply date range filter with Persian date support
        if ($request->filled('date_from_gregorian')) {
            $dateFrom = $request->date_from_gregorian;
            $query->whereHas('testable', function($q) use ($dateFrom) {
                $q->whereDate('date', '>=', $dateFrom);
            });
        } elseif ($request->filled('date_from')) {
            // Convert Persian date to Gregorian
            try {
                $persianDate = $request->date_from;
                $dateFrom = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $persianDate)->toCarbon()->format('Y-m-d');
                $query->whereHas('testable', function($q) use ($dateFrom) {
                    $q->whereDate('date', '>=', $dateFrom);
                });
            } catch (\Exception $e) {
                // If conversion fails, try as Gregorian date
                $query->whereHas('testable', function($q) use ($request) {
                    $q->whereDate('date', '>=', $request->date_from);
                });
            }
        }

        if ($request->filled('date_to_gregorian')) {
            $dateTo = $request->date_to_gregorian;
            $query->whereHas('testable', function($q) use ($dateTo) {
                $q->whereDate('date', '<=', $dateTo);
            });
        } elseif ($request->filled('date_to')) {
            // Convert Persian date to Gregorian
            try {
                $persianDate = $request->date_to;
                $dateTo = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $persianDate)->toCarbon()->format('Y-m-d');
                $query->whereHas('testable', function($q) use ($dateTo) {
                    $q->whereDate('date', '<=', $dateTo);
                });
            } catch (\Exception $e) {
                // If conversion fails, try as Gregorian date
                $query->whereHas('testable', function($q) use ($request) {
                    $q->whereDate('date', '<=', $request->date_to);
                });
            }
        }

        // Get filtered results and group by patient
        $patients = $query->latest()
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
            'results' => 'nullable|array',
            'text_result' => 'nullable|string',
        ]);

        try {
            // Check if test is already completed
            $test = PatientTestRegistration::with('labType.directLabTestParameters')->find($request->test_registration_id);
            if ($test->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => localize('global.cannot_update_completed_test'),
                    'redirect' => route('laboratory.reports.print', $test->ref_no)
                ], 403);
            }

            // Check section access
            $user = auth()->user();
            if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
                if ($user->section_id && $test->labType) {
                    $labTypeSection = $test->labType->section;
                    if ($labTypeSection && $labTypeSection->section_id != $user->section_id) {
                        return response()->json([
                            'status' => 'error',
                            'message' => localize('global.no_access_to_update_test')
                        ], 403);
                    }
                }
            }

            // Check if this is a non-parametered test
            $labType = $test->labType;
            
            if ((!$labType || ($labType->directLabTestParameters && $labType->directLabTestParameters->count() == 0)) && $request->has('text_result')) {
                // Handle non-parametered test with text_result
                $existingResult = PatientTestResult::where('ref_no', $request->ref_no)
                    ->where('test_registration_id', $request->test_registration_id)
                    ->whereNull('lab_parameter_id')
                    ->first();
                
                if ($existingResult) {
                    $existingResult->update(['text_result' => $request->text_result]);
                } else {
                    PatientTestResult::create([
                        'patient_id' => $request->patient_id ?? 1,
                        'ref_no' => $request->ref_no,
                        'lab_parameter_id' => null,
                        'text_result' => $request->text_result,
                        'test_registration_id' => $request->test_registration_id,
                    ]);
                }
            } else if ($request->has('results') && $request->results) {
                // Handle parametered test with individual results
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
            }

            // Check if test should be marked as completed
            $allResults = PatientTestResult::where('ref_no', $request->ref_no)
                ->where('test_registration_id', $request->test_registration_id)
                ->get();

            $allFilled = false;
            
            if ($request->has('text_result') && $request->text_result) {
                // For text-based tests, mark as completed if text_result is provided
                $allFilled = true;
            } else if ($request->has('results') && $request->results) {
                // For parametered tests, check if all parameters have results
                $allFilled = $allResults->every(fn($r) => $r->result !== null && $r->result !== '');
            }

            if ($allFilled) {
                // Update test registration status to completed
                $test = PatientTestRegistration::find($request->test_registration_id);
                $test->status = 'completed';
                $test->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => localize('global.results_updated_successfully'),
                'completed' => $allFilled
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => localize('global.error_updating_results') . ': ' . $e->getMessage(),
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
            'labType.section',
            'labType.category',
            'labType.directLabTestParameters',
            'doctor',
            'branch'
        ])->findOrFail($registration_id);

        // Check section access
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            if ($user->section_id && $registration->labType) {
                $labTypeSection = $registration->labType->section;
                if ($labTypeSection && $labTypeSection->section_id != $user->section_id) {
                    abort(403, 'You do not have access to this test registration.');
                }
            }
        }

        // Check if test is completed - redirect to print page
        if ($registration->status === 'completed') {
            return redirect()->route('laboratory.reports.print', $registration->ref_no);
        }

        $patient = $registration->testable->patient ?? null;
        
        if (!$patient) {
            abort(404, 'Patient not found for this registration');
        }

        // Get all registrations for this patient to show in the side panels
        $patientId = $patient->id;
        $completedTests = PatientTestRegistration::where('patient_id', $patientId)
            ->where('status', 'completed')
            ->with(['labType', 'testable.patient'])
            ->get();

        $pendingTests = PatientTestRegistration::where('patient_id', $patientId)
            ->where('status', 'pending')
            ->with(['labType', 'testable.patient'])
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
            if($firstTestResults->isEmpty() && $firstTest->labType && $firstTest->labType->directLabTestParameters) {
                foreach($firstTest->labType->directLabTestParameters as $parameter) {
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
            $test = PatientTestRegistration::with(['labType.directLabTestParameters', 'testable.patient'])->findOrFail($test_registration_id);

            // Check section access
            $user = auth()->user();
            if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
                if ($user->section_id && $test->labType) {
                    $labTypeSection = $test->labType->section;
                    if ($labTypeSection && $labTypeSection->section_id != $user->section_id) {
                        return response()->json([
                            'status' => 'error',
                            'message' => localize('global.no_access_to_test_registration')
                        ], 403);
                    }
                }
            }

            $results = PatientTestResult::where('test_registration_id', $test_registration_id)
                ->with('parameter')
                ->get();

            // If no results exist, create empty result entries for all parameters
            if($results->isEmpty() && $test->labType && $test->labType->directLabTestParameters) {
                foreach($test->labType->directLabTestParameters as $parameter) {
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
                'message' => localize('global.test_registration_not_found')
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => localize('global.failed_to_load_test_results') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print result by reference number
     */
    public function printResultByRef($ref_no)
    {
        // Load test registration with all necessary relationships
        $testRegistration = PatientTestRegistration::where('ref_no', $ref_no)
            ->with(['labType.directLabTestParameters', 'testable.patient'])
            ->first();

        if (!$testRegistration) {
            abort(404, 'No test registration found for this reference number.');
        }

        // Check section access
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            if ($user->section_id && $testRegistration->labType) {
                $labTypeSection = $testRegistration->labType->section;
                if ($labTypeSection && $labTypeSection->section_id != $user->section_id) {
                    abort(403, 'You do not have access to print this test report.');
                }
            }
        }

        $patient = $testRegistration->testable->patient ?? null;
        $testName = $testRegistration->labType->name ?? '—';

        // Load all results for this ref_no
        $results = PatientTestResult::with('parameter', 'patient')
            ->where('ref_no', $ref_no)
            ->get();

        // If no results exist but we have parameters, create empty result entries
        if ($results->isEmpty() && $testRegistration->labType && $testRegistration->labType->directLabTestParameters) {
            foreach ($testRegistration->labType->directLabTestParameters as $parameter) {
                $results->push(new \App\Models\PatientTestResult([
                    'lab_parameter_id' => $parameter->id,
                    'parameter' => $parameter,
                    'unit' => $parameter->unit,
                    'normal_range' => $parameter->normal_range,
                    'result' => null
                ]));
            }
        }

        // Return view with results and auto print
        return view('pages.laboratory.reports.lab_report', compact('patient', 'results', 'testRegistration', 'testName'));
    }

    /**
     * Display grouped test results by category_id
     */
    public function groupedTests(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:normal,urgent,stat',
            'doctor' => 'nullable|exists:users,id',
            'date_from' => 'nullable|string|regex:/^\d{4}\/\d{2}\/\d{2}$/',
            'date_to' => 'nullable|string|regex:/^\d{4}\/\d{2}\/\d{2}$/',
            'date_from_gregorian' => 'nullable|date',
            'date_to_gregorian' => 'nullable|date|after_or_equal:date_from_gregorian',
        ]);

        // Query test registrations with category_id
        $query = PatientTestRegistration::with([
            'testable.patient',
            'labType.section',
            'labType.category',
            'doctor',
            'branch'
        ])->whereNotNull('category_id');

        // Apply section-based access control
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            if ($user->section_id) {
                $query->whereHas('labType.section', function($q) use ($user) {
                    $q->where('section_id', $user->section_id);
                });
            }
        }

        // Apply search filter (patient name, phone, or reference number)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->whereHas('testable.patient', function($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%');
                })
                ->orWhere('ref_no', 'like', '%' . $search . '%');
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Apply doctor filter
        if ($request->filled('doctor')) {
            $query->where('doctor_id', $request->doctor);
        }

        // Apply date range filter with Persian date support
        if ($request->filled('date_from_gregorian')) {
            $dateFrom = $request->date_from_gregorian;
            $query->whereDate('registration_date', '>=', $dateFrom);
        } elseif ($request->filled('date_from')) {
            // Convert Persian date to Gregorian
            try {
                $persianDate = $request->date_from;
                $dateFrom = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $persianDate)->toCarbon()->format('Y-m-d');
                $query->whereDate('registration_date', '>=', $dateFrom);
            } catch (\Exception $e) {
                // If conversion fails, try as Gregorian date
                $query->whereDate('registration_date', '>=', $request->date_from);
            }
        }

        if ($request->filled('date_to_gregorian')) {
            $dateTo = $request->date_to_gregorian;
            $query->whereDate('registration_date', '<=', $dateTo);
        } elseif ($request->filled('date_to')) {
            // Convert Persian date to Gregorian
            try {
                $persianDate = $request->date_to;
                $dateTo = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', $persianDate)->toCarbon()->format('Y-m-d');
                $query->whereDate('registration_date', '<=', $dateTo);
            } catch (\Exception $e) {
                // If conversion fails, try as Gregorian date
                $query->whereDate('registration_date', '<=', $request->date_to);
            }
        }

        // Get paginated results
        $perPage = $request->get('per_page', 15); // Default 15 items per page
        $paginatedTests = $query->latest('registration_date')->paginate($perPage);
        
        // Group the paginated results by category_id
        $groupedTests = collect($paginatedTests->items())->groupBy('category_id');
        
        // Create a new paginator with grouped results
        $groupedTestsPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedTests,
            $paginatedTests->total(),
            $paginatedTests->perPage(),
            $paginatedTests->currentPage(),
            [
                'path' => $paginatedTests->path(),
                'pageName' => 'page',
            ]
        );
        
        // Preserve query parameters for pagination links
        $groupedTestsPaginated->appends($request->query());

        // Add some statistics for the view
        $totalTests = $paginatedTests->total();
        $totalGroups = $groupedTests->count();
        
        // Additional statistics (calculated from all results, not just current page)
        $allTests = $query->get();
        $completedTests = $allTests->where('status', 'completed')->count();
        $pendingTests = $allTests->where('status', 'pending')->count();
        $inProgressTests = $allTests->where('status', 'in_progress')->count();
        $cancelledTests = $allTests->where('status', 'cancelled')->count();

        return view('pages.laboratory.results.grouped', compact(
            'groupedTests', 
            'groupedTestsPaginated',
            'totalTests', 
            'totalGroups',
            'completedTests',
            'pendingTests', 
            'inProgressTests',
            'cancelledTests'
        ));
    }

    /**
     * Print grouped test results by category_id
     */
    public function printGroupedTests($category_id)
    {
        // Load all test registrations with matching category_id
        $testRegistrations = PatientTestRegistration::with([
            'testable.patient',
            'labType.section',
            'labType.category',
            'labType.directLabTestParameters',
            'doctor',
            'branch'
        ])->where('category_id', $category_id)->get();

        if ($testRegistrations->isEmpty()) {
            abort(404, 'No test registrations found for this category.');
        }

        // Apply section-based access control
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            if ($user->section_id) {
                $testRegistrations = $testRegistrations->filter(function($registration) use ($user) {
                    if ($registration->labType && $registration->labType->section) {
                        return $registration->labType->section->section_id == $user->section_id;
                    }
                    return false;
                });
            }
        }

        // After the filter, check if any registrations remain
        if ($testRegistrations->isEmpty()) {
            abort(403, 'You do not have access to this test group or no tests found.');
        }

        // Get patient from first registration
        $firstRegistration = $testRegistrations->first();
        $patient = $firstRegistration && $firstRegistration->testable ? $firstRegistration->testable->patient : null;
        
        // Group tests by their lab type category (different from category_id)
        $testsByLabCategory = $testRegistrations->groupBy(function($test) {
            return $test->labType ? $test->labType->category_id : 'uncategorized';
        });

        // Load all results for all tests in this group
        $allResults = collect();
        foreach ($testRegistrations as $registration) {
            try {
                $results = PatientTestResult::with('parameter')
                    ->where('test_registration_id', $registration->id)
                    ->get();
                
                // If no results exist but we have parameters, create empty result entries
                if ($results->isEmpty() && $registration->labType && $registration->labType->directLabTestParameters) {
                    foreach ($registration->labType->directLabTestParameters as $parameter) {
                        $results->push(new \App\Models\PatientTestResult([
                            'lab_parameter_id' => $parameter->id,
                            'parameter' => $parameter,
                            'unit' => $parameter->unit,
                            'normal_range' => $parameter->normal_range,
                            'result' => null,
                            'test_registration_id' => $registration->id
                        ]));
                    }
                }
                
                $allResults = $allResults->merge($results);
            } catch (\Exception $e) {
                // Log error but continue processing
                \Log::warning("Failed to load results for registration {$registration->id}: " . $e->getMessage());
            }
        }

        // Group results by test registration for display
        $groupedResults = $allResults->groupBy('test_registration_id');

        return view('pages.laboratory.reports.grouped_report', compact(
            'patient', 
            'testRegistrations', 
            'groupedResults',
            'category_id',
            'testsByLabCategory'
        ));
    }

    /**
     * Show scan page for laboratory tests
     */
    public function scanCode()
    {
        return view('pages.laboratory.scan');
    }

    /**
     * Handle scanned reference number
     */
    public function scanRefCode(Request $request)
    {
        // Get the scanned reference number
        $ref_no = $request->input('ref_no');

        // Find the test registration based on the reference number
        $registration = PatientTestRegistration::where('ref_no', $ref_no)
            ->where('branch_id', auth()->user()->branch_id)
            ->with(['labType', 'testable.patient'])
            ->first();

        if (!$registration) {
            // Handle the case when the test is not found
            return redirect()->back()->with('error', localize('global.test_not_found'));
        }

        // Check the status of the test
        if ($registration->status === 'completed') {
            // If completed, redirect to print page
            return redirect()->route('laboratory.reports.print', $ref_no);
        } else {
            // If not completed, redirect to results entry page
            return redirect()->route('laboratory.results.show', $registration->id);
        }
    }
}
