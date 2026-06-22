<?php

namespace App\Http\Controllers\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GenralReport extends Controller
{

    public function index(Request $request)
    {
        return Inertia::render('Reports/GeneralReport');
    }

    public function number_of_patients_base_on_department(Request $request)
    {

        $data = Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('appointments.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('appointments.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('appointments.branch_id', $request->branch_id))
            ->selectRaw('appointments.department_id, departments.name as department_name, COUNT(*) as count')
            ->groupBy('appointments.department_id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->map(fn ($row) => [
                'department_id' => $row->department_id,
                'department_name' => $row->department_name,
                'count' => (int) $row->count,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'General report fetched successfully',
            'data' => $data,
        ]);
    }
}