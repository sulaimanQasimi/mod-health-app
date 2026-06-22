<?php

namespace App\Http\Controllers\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GenralReport extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();
        $branches = $user?->branch_id
            ? Branch::query()->where('id', $user->branch_id)->orderBy('name')->get(['id', 'name'])
            : Branch::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reports/GeneralReport', [
            'filters' => [
                'branch_id' => $request->string('branch_id')->toString(),
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'hasSearch' => $request->boolean('search'),
            'filterOptions' => [
                'branches' => $branches,
            ],
            'urls' => [
                'current' => route('react.reports.general.index'),
            ],
        ]);
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
    public function number_of_patients_base_on_patient_militery_types(Request $request)
    {
        $rows = Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('appointments.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('appointments.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('appointments.branch_id', $request->branch_id))
            ->selectRaw('appointments.department_id, departments.name as department_name, patients.militery_type_id, militery_types.name as militery_type_name, COUNT(*) as count')
            ->groupBy('appointments.department_id', 'departments.name', 'patients.militery_type_id', 'militery_types.name')
            ->orderBy('departments.name')
            ->orderBy('militery_types.name')
            ->get();

        $data = $rows
            ->groupBy('department_id')
            ->map(function ($departmentRows) {
                $first = $departmentRows->first();

                return [
                    'department_id' => $first->department_id,
                    'department_name' => $first->department_name,
                    'count' => (int) $departmentRows->sum('count'),
                    'militery_types' => $departmentRows->map(fn ($row) => [
                        'militery_type_id' => $row->militery_type_id,
                        'militery_type_name' => $row->militery_type_name,
                        'count' => (int) $row->count,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'General report fetched successfully',
            'data' => $data,
        ]);
    }
}