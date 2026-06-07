<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PatientController as LegacyPatientController;
use App\Models\Department;
use App\Models\District;
use App\Models\MiliteryType;
use App\Models\Patient;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    private const INDEX_FILTER_KEYS = [
        'name',
        'father_name',
        'last_name',
        'phone',
        'card_search',
        'militery_type_id',
        'province_id',
        'gender',
        'job_category',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Patient::class);

        $user = $request->user();

        $query = Patient::query()
            ->where('branch_id', $user->branch_id)
            ->with([
                'militeryType:id,name',
                'province:id,name_dr',
                'district:id,name_dr',
                'creator:id,name,last_name',
            ]);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('father_name')) {
            $query->where('father_name', 'like', '%'.$request->father_name.'%');
        }

        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', '%'.$request->last_name.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        if ($request->filled('card_search')) {
            $query->where('id_card', 'like', '%'.$request->card_search.'%');
        }

        if ($request->filled('militery_type_id')) {
            $query->where('militery_type_id', $request->militery_type_id);
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('job_category')) {
            $query->where('job_category', $request->job_category);
        }

        $paginator = $query->latest()->paginate(15)->withQueryString();

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Patients/Index', [
            'patients' => [
                'data' => collect($paginator->items())
                    ->map(fn (Patient $patient) => $this->transformPatientForIndex($patient))
                    ->values()
                    ->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $filters,
            'filterOptions' => [
                'militeryTypes' => MiliteryType::query()->orderBy('name')->get(['id', 'name']),
                'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
            ],
            'permissions' => [
                'create' => $user->can('create', Patient::class),
                'edit' => $user->hasRole(['super_admin', 'admin'])
                    || $user->hasPermissionTo('edit-patients'),
            ],
            'urls' => [
                'index' => route('react.patients.index'),
                'create' => route('react.patients.create'),
                'show' => url('/patients/show'),
                'edit' => url('/patients/edit'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Patient::class);

        $user = $request->user();

        $departments = $user->category_id
            ? Department::query()->where('category_id', $user->category_id)->orderBy('name')->get(['id', 'name'])
            : Department::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Patients/Create', [
            'formData' => [
                'branchId' => $user->branch_id,
                'clinicType' => $user->clinic_type,
                'registrationDate' => verta()->format('Y-m-d'),
                'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
                'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
                'relations' => Relation::query()->orderBy('name')->get(['id', 'name']),
                'militeryTypes' => MiliteryType::query()->orderBy('name')->get(['id', 'name']),
                'departments' => $departments,
            ],
            'urls' => [
                'store' => route('react.patients.store'),
                'districts' => url('/react/patients/districts'),
                'doctorsByDepartment' => url('/react/patients/doctors-by-department'),
                'back' => route('react.patients.index'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $proxy = $request->duplicate();
        $proxy->headers->set('X-Requested-With', 'XMLHttpRequest');
        $proxy->headers->set('Accept', 'application/json');

        return app(LegacyPatientController::class)->store($proxy);
    }

    public function districts(int $provinceId): JsonResponse
    {
        $this->authorize('create', Patient::class);

        $districts = District::query()
            ->where('province_id', $provinceId)
            ->orderBy('name_dr')
            ->get(['id', 'name_dr']);

        return response()->json([
            'success' => true,
            'districts' => $districts,
        ]);
    }

    public function doctorsByDepartment(int $departmentId, Request $request): JsonResponse
    {
        $this->authorize('create', Patient::class);

        return app(LegacyPatientController::class)->getDoctorsByDepartment($departmentId, $request);
    }

    public function report()
    {
        return Inertia::render('Placeholder', [
            'title' => 'global.reports',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPatientForIndex(Patient $patient): array
    {
        $provinceName = $patient->province?->name_dr;
        $districtName = $patient->district?->name_dr;

        if ($provinceName && $districtName) {
            $location = "{$provinceName} / {$districtName}";
        } elseif ($provinceName) {
            $location = $provinceName;
        } elseif ($districtName) {
            $location = $districtName;
        } else {
            $location = '-';
        }

        $creator = $patient->creator;
        $createdBy = $creator
            ? trim("{$creator->name} {$creator->last_name}")
            : null;

        return [
            'id' => $patient->id,
            'id_card' => $patient->id_card,
            'name' => $patient->name,
            'last_name' => $patient->last_name,
            'father_name' => $patient->father_name,
            'location' => $location,
            'age' => $patient->age,
            'militery_type' => $patient->militeryType?->name,
            'phone' => $patient->phone,
            'created_by' => $createdBy,
        ];
    }
}
