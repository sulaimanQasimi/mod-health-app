<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class DoctorPerformanceReportController extends Controller
{
    /**
     * Display the performance report page with doctor list.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $doctors = $this->getDoctorsWithRelations();
        
        return view('pages.patients.myPatients', compact('doctors'));
    }

    /**
     * Fetch performance data based on filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function fetch(Request $request): View|JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'startDate' => 'required|string',
            'endDate' => 'required|string',
            'doctorId' => 'nullable|integer|exists:doctors,id',
        ]);
        
        // Convert Persian dates to Gregorian
        try {
            $validated['startDate'] = Verta::parse($validated['startDate'])->format('Y-m-d');
            $validated['endDate'] = Verta::parse($validated['endDate'])->format('Y-m-d');
            
            // Validate date range after conversion
            if ($validated['startDate'] > $validated['endDate']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'End date must be after or equal to start date.',
                        'errors' => ['endDate' => 'End date must be after or equal to start date.']
                    ], 422);
                }
                return back()->withErrors(['endDate' => 'End date must be after or equal to start date.']);
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Please use Persian date format.',
                    'error' => $e->getMessage(),
                    'errors' => ['startDate' => 'Invalid date format. Please use Persian date format.']
                ], 422);
            }
            return back()->withErrors(['startDate' => 'Invalid date format. Please use Persian date format.']);
        }
        
        try {
            $results = DB::select('CALL sp_doctor_performance_dynamic(?, ?, ?)', [
                $validated['startDate'],
                $validated['endDate'],
                $validated['doctorId'] ?? null
            ]);

            $doctors = $this->getDoctorsWithRelations();

            // If request is AJAX, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'doctors' => $doctors
                ]);
            }

            return view('pages.patients.myPatients', compact('doctors', 'results'));
        } catch (\Exception $e) {
            // If request is AJAX, return error JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while fetching performance data.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'An error occurred while fetching performance data.']);
        }
    }

    /**
     * Get doctors with their related departments.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getDoctorsWithRelations()
    {
        return Doctor::query()
            ->leftJoin('departments', 'doctors.department_id', '=', 'departments.id')
            ->select(
                'doctors.id',
                'doctors.name',
                'doctors.specialization',
                'departments.name as department_name'
            )
            ->where('doctors.active_status', true)
            ->orderBy('doctors.name')
            ->get();
    }
}
