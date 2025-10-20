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
     * Show the form for creating a new patient test registration
     */
    public function create()
    {
        $categories = TestCategory::all();
        return view('pages.laboratory.registrations.create', compact('categories'));
    }

    /**
     * Get test list for display
     */
    public function getTestList()
    {
        $tests = PatientTestRegistration::with(['patient', 'labTest'])
            ->orderBy('id', 'desc')
            ->get();

        return view('pages.laboratory.registrations.index', compact('tests'));
    }

    /**
     * Store a newly created patient test registration
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'test_parameter_ids' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Lock ref_numbers row and increment ref_no
            $ref = DB::table('ref_numbers')->lockForUpdate()->first();
            $newRefNo = $ref->last_ref_no + 1;
            DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);

            // Insert parent registration
            $registration = PatientTestRegistration::create([
                'patient_id'        => $data['patient_id'],
                'registration_date' => now(),
                'ref_no'            => $newRefNo,
                'lab_test_id'       => $data['lab_test_id'],
                'status'            => 'pending',
            ]);

            // Insert child test results for each parameter
            foreach ($data['test_parameter_ids'] as $paramId) {
                $param = LabTestParameter::find($paramId);
                if (!$param) continue;

                PatientTestResult::create([
                    'patient_id'          => $data['patient_id'],
                    'ref_no'              => $newRefNo,
                    'lab_parameter_id'    => $param->id,
                    'unit'                => $param->unit ?? null,
                    'normal_range'        => $param->normal_range ?? null,
                    'result'              => null,
                    'test_registration_id'=> $registration->id,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Patient test registered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get tests by category (AJAX)
     */
    public function getTests($categoryId)
    {
        try {
            $tests = LabTest::where('category_id', $categoryId)->get();
            return response()->json($tests);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get parameters by test (AJAX)
     */
    public function getParameters($testId)
    {
        try {
            $parameters = LabTestParameter::where('test_id', $testId)
                ->select('id', 'parameter_name', 'normal_range')
                ->get();

            return response()->json($parameters);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search patients (AJAX)
     */
    public function searchPatients(Request $request)
    {
        $term = $request->get('term', '');

        $patients = Patient::where('name', 'LIKE', "%{$term}%")
            ->orWhere('id', 'LIKE', "%{$term}%")
            ->select('id', 'name', 'age')
            ->limit(10)
            ->get();

        $results = [];
        foreach ($patients as $p) {
            $results[] = [
                'id' => $p->id,
                'text' => $p->name . ' (' . $p->age . ' yrs)',
            ];
        }

        return response()->json(['results' => $results]);
    }
}
