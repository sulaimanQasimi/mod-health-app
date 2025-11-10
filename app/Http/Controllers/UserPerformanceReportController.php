<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Section;
use App\Models\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class UserPerformanceReportController extends Controller
{
    /**
     * Display the performance report page with user list.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $users = $this->getUsersWithRelations();
        
        return view('pages.patients.myPatients', compact('users'));
    }

    /**
     * Fetch performance data based on filters.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function fetch(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'userId' => 'nullable|integer|exists:users,id',
        ]);
        $validated['startDate']=verta($validated['endDate'])->format('Y-m-d');
        $validated['endDate']=verta($validated['endDate'])->format('Y-m-d');
        
        try {
            $results = DB::select('CALL sp_user_performance_dynamic(?, ?, ?)', [
                $validated['startDate'],
                $validated['endDate'],
                $validated['userId'] ?? null
            ]);

            $users = $this->getUsersWithRelations();

            // If request is AJAX, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'users' => $users
                ]);
            }

            return view('pages.patients.myPatients', compact('users', 'results'));
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
     * Get users with their related sections and departments.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUsersWithRelations()
    {
        return User::query()
            ->leftJoin('sections', 'users.section_id', '=', 'sections.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->select(
                'users.id',
                'users.name',
                'sections.name as section_name',
                'departments.name as department_name'
            )
            ->orderBy('users.name')
            ->get();
    }
}

