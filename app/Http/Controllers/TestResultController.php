<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\Patient;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\PatientTestResultAttachment;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
     * Apply clinic type filter based on user's clinic
     */
    private function applyClinicTypeFilter($query, $user)
    {
        if (!$user->clinic_type) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            $q->whereHasMorph('testable', [\App\Models\Appointment::class], function($appointmentQ) use ($user) {
                $appointmentQ->where('clinic_type', $user->clinic_type);
            })
            ->orWhereHasMorph('testable', [\App\Models\Hospitalization::class], function($hospitalizationQ) use ($user) {
                $hospitalizationQ->whereHas('appointment', function($appointmentQ) use ($user) {
                    $appointmentQ->where('clinic_type', $user->clinic_type);
                });
            })
            ->orWhere(function($subQ) {
                $subQ->where('testable_type', '!=', 'App\Models\Appointment')
                     ->where('testable_type', '!=', 'App\Models\Hospitalization');
            })
            ->orWhereNull('testable_type');
        });
    }

    /**
     * Apply access control based on user role and route
     */
    private function applyAccessControl($query, $user, $routeName)
    {
        if ($user->hasRole('admin') || $user->can('view-all-sections')) {
            return $query;
        }

        if ($routeName === 'laboratory.results.pending') {
            return $query->whereNull('assigned_to');
        } elseif ($routeName === 'laboratory.results.in-progress') {
            return $query->where('assigned_to', $user->id);
        } elseif ($routeName === 'laboratory.results.completed') {
            return $query->where(function($q) use ($user) {
                $q->where('completed_by', $user->id)
                  ->orWhere('assigned_to', $user->id);
            });
        }

        return $query;
    }

    /**
     * Convert Persian/Jalali date to DateTime for DB comparison. Handles Persian numerals from datepicker_dari.
     * Returns Verta::parse()->datetime() on success; Y-m-d string if input is Gregorian; null otherwise.
     */
    private function convertPersianDate($persianDate)
    {
        try {
            return Verta::parse($persianDate)->datetime();
        } catch (\Exception $e) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $persianDate)) {
                return $persianDate;
            }
            return null;
        }
    }

    /**
     * Apply date range filters to the PatientTestRegistration query.
     * Uses registration_date on patient_test_registrations (all testable types have it via the registration),
     * instead of testable.date which does not exist on hospitalizations or under_reviews.
     */
    private function applyDateFilters($query, $request)
    {
        $dateField = 'registration_date';

        // Date from filter
        if ($request->filled('date_from_gregorian')) {
            $query->whereDate($dateField, '>=', $request->date_from_gregorian);
        } elseif ($request->filled('date_from')) {
            $dateFrom = $this->convertPersianDate($request->date_from);
            if ($dateFrom !== null) {
                $query->whereDate($dateField, '>=', $dateFrom);
            }
        }

        // Date to filter
        if ($request->filled('date_to_gregorian')) {
            $query->whereDate($dateField, '<=', $request->date_to_gregorian);
        } elseif ($request->filled('date_to')) {
            $dateTo = $this->convertPersianDate($request->date_to);
            if ($dateTo !== null) {
                $query->whereDate($dateField, '<=', $dateTo);
            }
        }

        return $query;
    }

    /**
     * Create empty result entries for parameters
     */
    private function createEmptyResults($test)
    {
        $results = collect();
        
        if ($test->labType && $test->labType->directLabTestParameters) {
            foreach ($test->labType->directLabTestParameters as $parameter) {
                $results->push(new \App\Models\PatientTestResult([
                    'lab_parameter_id' => $parameter->id,
                    'parameter' => $parameter,
                    'unit' => $parameter->unit,
                    'normal_range' => $parameter->normal_range,
                    'result' => null
                ]));
            }
        }
        
        return $results;
    }

    /**
     * Display patient list for test results
     */
    public function patientList(Request $request)
    {
        // Start query builder – use withCount for parameters to avoid loading full collections (memory)
        $query = PatientTestRegistration::with([
            'testable.patient',
            'labType.category',
            'labType' => fn ($q) => $q->withCount('directLabTestParameters'),
            'doctor',
            'branch',
            'assignedTo',
            'assignedSection'
        ]);

        // Apply filters
        $user = auth()->user();
        $query = $this->applyClinicTypeFilter($query, $user);
        $query = $this->applyAccessControl($query, $user, $request->route()->getName());

        // Apply search filter (patient name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('testable', function($q) use ($search) {
                $q->whereHas('patient', function($patientQ) use ($search) {
                    $patientQ->where('name', 'like', '%' . $search . '%')
                             ->orWhere('last_name', 'like', '%' . $search . '%');
                });
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


        // Apply date range filter
        $query = $this->applyDateFilters($query, $request);

        // Paginate to avoid loading all registrations into memory (fixes "Allowed memory size exhausted")
        $perPage = (int) $request->get('per_page', 50);
        $perPage = min(max($perPage, 15), 100);
        $paginator = $query->latest()->paginate($perPage)->appends($request->query());

        // Group current page results by patient for the view
        $patients = $paginator->getCollection()->groupBy(function ($registration) {
            return $registration->testable->patient->id ?? 'unknown';
        });

        return view('pages.laboratory.results.patients', compact('patients', 'paginator'));
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
            'notes' => 'nullable|string',
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

            // Update notes field
            $test->notes = $request->notes;
            $test->save();

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
            'labType.category',
            'labType.directLabTestParameters',
            'doctor',
            'branch',
            'assignedTo',
            'assignedSection'
        ])->findOrFail($registration_id);

        // Check assignment access
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->can('view-all-sections')) {
            // If test is assigned to someone else, deny access
            if ($registration->assigned_to && $registration->assigned_to != $user->id) {
                abort(403, 'This test is assigned to another user.');
            }
            
            // If test is completed by someone else and not assigned to current user, deny access
            if ($registration->status === 'completed' && 
                $registration->completed_by != $user->id && 
                $registration->assigned_to != $user->id) {
                abort(403, 'You do not have access to this completed test.');
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
            
            // If no results exist, create empty result entries
            if ($firstTestResults->isEmpty()) {
                $firstTestResults = $this->createEmptyResults($firstTest);
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


            $results = PatientTestResult::where('test_registration_id', $test_registration_id)
                ->with('parameter')
                ->get();

            // If no results exist, create empty result entries
            if ($results->isEmpty()) {
                $results = $this->createEmptyResults($test);
            }

            return response()->json([
                'success' => true,
                'status' => 'success',
                'data' => [
                    'test' => $test,
                    'results' => $results->map(function($result) {
                        return [
                            'id' => $result->id,
                            'lab_parameter_id' => $result->lab_parameter_id,
                            'result' => $result->result,
                            'text_result' => $result->text_result,
                            'parameter' => $result->parameter ? [
                                'id' => $result->parameter->id,
                                'parameter_name' => $result->parameter->parameter_name,
                            ] : null,
                        ];
                    })
                ]
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
            'labType.category',
            'doctor',
            'branch',
            'assignedTo',
            'assignedSection'
        ])->whereNotNull('category_id');

        // Apply search filter (patient name, phone, or reference number)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->whereHas('testable', function($testableQuery) use ($search) {
                    $testableQuery->whereHas('patient', function($patientQuery) use ($search) {
                        $patientQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('last_name', 'like', '%' . $search . '%')
                                    ->orWhere('phone', 'like', '%' . $search . '%');
                    });
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
            'labType.category',
            'labType.directLabTestParameters',
            'doctor',
            'branch',
            'assignedTo',
            'assignedSection'
        ])->where('category_id', $category_id)->get();

        if ($testRegistrations->isEmpty()) {
            abort(404, 'No test registrations found for this category.');
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

    /**
     * Accept and assign a test to the current user
     */
    public function acceptTest($registration_id)
    {
        try {
            $registration = PatientTestRegistration::findOrFail($registration_id);
            
            // Check if test is already assigned
            if ($registration->assigned_to) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.test_already_assigned')
                ], 400);
            }

            // Check if test is pending
            if ($registration->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.only_pending_tests_can_be_accepted')
                ], 400);
            }

            // Assign test to current user
            $registration->assignToUser(auth()->id());

            return response()->json([
                'success' => true,
                'message' => localize('global.test_accepted_successfully'),
                'data' => $registration->load(['assignedTo', 'assignedSection'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.error_accepting_test') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load all parameters for multiple test registrations
     */
    public function loadAllParameters(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:patient_test_registrations,id',
        ]);

        try {
            $registrationIds = $request->registration_ids;
            $tests = [];

            foreach ($registrationIds as $registrationId) {
                $registration = PatientTestRegistration::with([
                    'labType.directLabTestParameters',
                    'results'
                ])->find($registrationId);

                if (!$registration) {
                    continue;
                }

                $parameters = [];
                $textResult = null;
                $labType = $registration->labType;
                $isParametered = false;

                if ($labType && $labType->directLabTestParameters && $labType->directLabTestParameters->count() > 0) {
                    $isParametered = true;
                    foreach ($labType->directLabTestParameters as $parameter) {
                        // Get existing result if any
                        $existingResult = $registration->results()
                            ->where('lab_parameter_id', $parameter->id)
                            ->first();

                        $parameters[] = [
                            'id' => $parameter->id,
                            'parameter_name' => $parameter->parameter_name,
                            'unit' => $parameter->unit,
                            'normal_range' => $parameter->normal_range,
                            'result' => $existingResult ? $existingResult->result : null,
                        ];
                    }
                } else {
                    // Text-based test
                    $existingTextResult = $registration->results()
                        ->whereNull('lab_parameter_id')
                        ->first();
                    $textResult = $existingTextResult ? $existingTextResult->text_result : null;
                }

                $tests[$registrationId] = [
                    'registration_id' => $registrationId,
                    'ref_no' => $registration->ref_no,
                    'lab_type_name' => $labType ? $labType->name : 'Unknown',
                    'parameters' => $parameters,
                    'is_parametered' => $isParametered,
                    'text_result' => $textResult,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tests' => $tests,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.error_loading_parameters') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all parameters for multiple test registrations with same group_id
     */
    public function saveAllParameters(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:patient_test_registrations,id',
            'results' => 'nullable|array',
            'text_results' => 'nullable|array',
            'patient_id' => 'nullable|exists:patients,id',
        ]);

        try {
            DB::beginTransaction();

            $registrationIds = $request->registration_ids;
            $results = $request->results;
            $patientId = $request->patient_id;

            // Generate a unique group_id (using timestamp + random number)
            $groupId = 'GRP_' . time() . '_' . rand(1000, 9999);

            // Get all registrations
            $registrations = PatientTestRegistration::whereIn('id', $registrationIds)->get();

            // Update all registrations with the same category_id (using it as group_id)
            // If category_id doesn't exist, we'll use a new approach - store group_id in metadata
            $categoryId = $registrations->first()->category_id ?? null;
            
            // If no category_id exists, create one or use the first registration's category_id
            if (!$categoryId) {
                // Get the max category_id and increment it, or use a new one
                $maxCategoryId = PatientTestRegistration::max('category_id') ?? 0;
                $categoryId = $maxCategoryId + 1;
            }

            // Update all registrations with the same category_id
            PatientTestRegistration::whereIn('id', $registrationIds)
                ->update(['category_id' => $categoryId]);

            $results = $request->results ?? [];
            $textResults = $request->text_results ?? [];

            // Process results for each registration
            foreach ($registrationIds as $registrationId) {
                $registration = PatientTestRegistration::with('labType.directLabTestParameters')
                    ->find($registrationId);

                if (!$registration) {
                    continue;
                }

                // Handle text-based results
                if (isset($textResults[$registrationId]) && !empty(trim($textResults[$registrationId]))) {
                    $textResult = trim($textResults[$registrationId]);
                    
                    $existingTextResult = PatientTestResult::where('ref_no', $registration->ref_no)
                        ->where('test_registration_id', $registrationId)
                        ->whereNull('lab_parameter_id')
                        ->first();

                    if ($existingTextResult) {
                        $existingTextResult->update(['text_result' => $textResult]);
                    } else {
                        PatientTestResult::create([
                            'patient_id' => $patientId ?? $registration->testable?->patient_id ?? 1,
                            'ref_no' => $registration->ref_no,
                            'lab_parameter_id' => null,
                            'text_result' => $textResult,
                            'test_registration_id' => $registrationId,
                        ]);
                    }
                    
                    // Mark text-based test as completed
                    $registration->status = 'completed';
                    $registration->completed_at = now();
                    $registration->completed_by = auth()->id();
                    $registration->save();
                    continue;
                }

                // Handle parametered results
                $registrationResults = $results[$registrationId] ?? [];
                $hasSavedResults = false;

                if (!empty($registrationResults)) {
                    // Save each parameter result
                    foreach ($registrationResults as $parameterId => $resultValue) {
                        if (empty($resultValue) || trim($resultValue) === '') {
                            continue;
                        }

                        $hasSavedResults = true;
                        $existingResult = PatientTestResult::where('ref_no', $registration->ref_no)
                            ->where('lab_parameter_id', $parameterId)
                            ->where('test_registration_id', $registrationId)
                            ->first();

                        if ($existingResult) {
                            // Update existing result
                            $existingResult->update(['result' => $resultValue]);
                        } else {
                            // Get parameter details
                            $parameter = \App\Models\LabTestParameter::find($parameterId);
                            
                            // Create new result entry
                            PatientTestResult::create([
                                'patient_id' => $patientId ?? $registration->testable?->patient_id ?? 1,
                                'ref_no' => $registration->ref_no,
                                'lab_parameter_id' => $parameterId,
                                'result' => $resultValue,
                                'unit' => $parameter->unit ?? null,
                                'normal_range' => $parameter->normal_range ?? null,
                                'test_registration_id' => $registrationId,
                            ]);
                        }
                    }

                    // Mark as completed if results were saved
                    if ($hasSavedResults) {
                        $registration->status = 'completed';
                        $registration->completed_at = now();
                        $registration->completed_by = auth()->id();
                        $registration->save();
                    } else {
                        // Mark as in progress if not already
                        if ($registration->status === 'pending') {
                            $registration->status = 'in_progress';
                            $registration->save();
                        }
                    }
                } else {
                    // If no results provided but test exists, check if it should be marked as in progress
                    if ($registration->status === 'pending') {
                        $registration->status = 'in_progress';
                        $registration->save();
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => localize('global.all_parameters_saved_successfully'),
                'group_id' => $categoryId,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => localize('global.error_saving_parameters') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload attachments for a test result
     */
    public function uploadAttachments(Request $request, $testResultId)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240', // 10MB max
            'description' => 'nullable|string|max:500',
            'registration_id' => 'nullable|exists:patient_test_registrations,id',
        ]);

        try {
            // If testResultId is 0 or empty, try to create a test result from registration_id
            if (!$testResultId || $testResultId == 0) {
                if ($request->has('registration_id')) {
                    $registration = PatientTestRegistration::findOrFail($request->registration_id);
                    
                    // Create a text-based result if it doesn't exist
                    $testResult = PatientTestResult::firstOrCreate(
                        [
                            'test_registration_id' => $registration->id,
                            'ref_no' => $registration->ref_no,
                            'lab_parameter_id' => null,
                        ],
                        [
                            'patient_id' => $registration->testable->patient_id ?? 1,
                            'text_result' => null,
                        ]
                    );
                    $testResultId = $testResult->id;
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => localize('global.test_result_not_found')
                    ], 404);
                }
            } else {
                $testResult = PatientTestResult::findOrFail($testResultId);
            }
            
            $uploadedFiles = [];
            
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('test_result_attachments', $filename, 'public');
                    
                    $attachment = PatientTestResultAttachment::create([
                        'patient_test_result_id' => $testResult->id,
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'description' => $request->description,
                    ]);
                    
                    $uploadedFiles[] = [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'file_url' => $attachment->file_url,
                        'file_size' => $attachment->formatted_file_size,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => localize('global.files_uploaded_successfully'),
                'files' => $uploadedFiles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => localize('global.error_uploading_files') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an attachment
     */
    public function deleteAttachment($attachmentId)
    {
        try {
            $attachment = PatientTestResultAttachment::findOrFail($attachmentId);
            
            // Delete the physical file
            $attachment->deleteFile();
            
            // Delete the database record
            $attachment->delete();

            return response()->json([
                'status' => 'success',
                'message' => localize('global.file_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => localize('global.error_deleting_file') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attachments for a test result
     */
    public function getAttachments($testResultId)
    {
        try {
            $testResult = PatientTestResult::with('attachments')->findOrFail($testResultId);
            
            $attachments = $testResult->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->file_url,
                    'file_size' => $attachment->formatted_file_size,
                    'file_type' => $attachment->file_type,
                    'description' => $attachment->description,
                    'created_at' => $attachment->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'status' => 'success',
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => localize('global.error_loading_attachments') . ': ' . $e->getMessage()
            ], 500);
        }
    }
}
