<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Category;
use App\Models\Department;
use App\Models\LabType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LabTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'category_id', 'department_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorizeLabTypeAccess($request);

        $query = $this->scopedQuery($request)
            ->with(['category:id,name', 'department:id,name'])
            ->withCount('directLabTestParameters');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('LabTypes/Index', [
            'labTypes' => $this->paginationPayload($paginator, fn (LabType $labType) => [
                'id' => $labType->id,
                'name' => $labType->name,
                'category_name' => $labType->category?->name,
                'department_name' => $labType->department?->name,
                'parameters_count' => $labType->direct_lab_test_parameters_count,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => $this->filterOptions($request),
            'permissions' => $this->labTypePermissions($request->user()),
            'urls' => [
                'index' => route('react.lab-types.index'),
                'create' => route('react.lab-types.create'),
                'show' => url('/react/lab-types'),
                'edit' => url('/react/lab-types'),
                'destroy' => url('/react/lab-types'),
            ],
        ]);
    }

    public function show(Request $request, LabType $labType): Response
    {
        $this->authorizeLabTypeAccess($request);
        $this->ensureLabTypeBranch($labType);

        $labType->load(['category:id,name', 'department:id,name'])->loadCount('directLabTestParameters', 'patientTestRegistrations');

        return Inertia::render('LabTypes/Show', [
            'labType' => [
                'id' => $labType->id,
                'name' => $labType->name,
                'category_name' => $labType->category?->name,
                'department_name' => $labType->department?->name,
                'parameters_count' => $labType->direct_lab_test_parameters_count,
                'registrations_count' => $labType->patient_test_registrations_count,
                'created_at' => $labType->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $labType->updated_at?->format('Y-m-d H:i:s'),
            ],
            'permissions' => [
                'edit' => $this->canManageLabTypes($request->user()),
                'delete' => $this->canManageLabTypes($request->user()),
            ],
            'urls' => [
                'index' => route('react.lab-types.index'),
                'edit' => route('react.lab-types.edit', $labType),
                'destroy' => route('react.lab-types.destroy', $labType),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeLabTypeManage($request);

        return Inertia::render('LabTypes/Create', [
            'formData' => $this->buildFormData($request),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeLabTypeManage($request);

        $data = $this->validateLabType($request);
        LabType::create($data);

        return redirect()
            ->route('react.lab-types.index')
            ->with('success', localize('global.lab_type_created_successfully'));
    }

    public function edit(Request $request, LabType $labType): Response
    {
        $this->authorizeLabTypeManage($request);
        $this->ensureLabTypeBranch($labType);

        return Inertia::render('LabTypes/Edit', [
            'labType' => [
                'id' => $labType->id,
                'name' => $labType->name,
                'category_id' => $labType->category_id ? (string) $labType->category_id : '',
                'department_id' => $labType->department_id ? (string) $labType->department_id : '',
            ],
            'formData' => $this->buildFormData($request),
            'urls' => $this->formUrls($labType),
        ]);
    }

    public function update(Request $request, LabType $labType): RedirectResponse
    {
        $this->authorizeLabTypeManage($request);
        $this->ensureLabTypeBranch($labType);

        $labType->update($this->validateLabType($request, $labType));

        return redirect()
            ->route('react.lab-types.index')
            ->with('success', localize('global.lab_type_updated_successfully'));
    }

    public function destroy(Request $request, LabType $labType): RedirectResponse
    {
        $this->authorizeLabTypeManage($request);
        $this->ensureLabTypeBranch($labType);

        if ($labType->patientTestRegistrations()->exists()) {
            return redirect()
                ->route('react.lab-types.index')
                ->with('error', localize('global.lab_type_cannot_delete_with_registrations'));
        }

        $labType->delete();

        return redirect()
            ->route('react.lab-types.index')
            ->with('success', localize('global.lab_type_deleted_successfully'));
    }

    private function scopedQuery(Request $request)
    {
        $query = LabType::query();

        if ($branchId = $this->branchId($request)) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    private function branchId(Request $request): ?int
    {
        $branchId = $request->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    private function ensureLabTypeBranch(LabType $labType): void
    {
        $branchId = auth()->user()?->branch_id;
        if ($branchId && (int) $labType->branch_id !== (int) $branchId) {
            abort(404);
        }
    }

    private function authorizeLabTypeAccess(Request $request): void
    {
        abort_unless(
            $this->canManageLabTypes($request->user()) || $request->user()->can('register-patient-tests'),
            403,
        );
    }

    private function authorizeLabTypeManage(Request $request): void
    {
        abort_unless($this->canManageLabTypes($request->user()), 403);
    }

    private function canManageLabTypes(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || $user->can('manage-lab-tests');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(Request $request): array
    {
        $branchId = $this->branchId($request);

        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filterOptions(Request $request): array
    {
        $branchId = $this->branchId($request);

        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?LabType $labType = null): array
    {
        return [
            'index' => route('react.lab-types.index'),
            'store' => route('react.lab-types.store'),
            'update' => $labType ? route('react.lab-types.update', $labType) : '',
            'back' => route('react.lab-types.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateLabType(Request $request, ?LabType $labType = null): array
    {
        $branchId = $this->branchId($request) ?? $labType?->branch_id;
        if (! $branchId) {
            abort(422, 'Branch ID is required. Please contact administrator.');
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lab_types', 'name')
                    ->where(fn ($query) => $query->where('branch_id', $branchId))
                    ->ignore($labType?->id),
            ],
            'category_id' => 'required|exists:categories,id',
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->where('branch_id', $branchId)),
            ],
        ]);

        $data['branch_id'] = $branchId;

        return $data;
    }

    /**
     * @return array{create: bool, edit: bool, delete: bool, view: bool}
     */
    private function labTypePermissions(User $user): array
    {
        $canManage = $this->canManageLabTypes($user);

        return [
            'view' => $canManage || $user->can('register-patient-tests'),
            'create' => $canManage,
            'edit' => $canManage,
            'delete' => $canManage,
        ];
    }
}
