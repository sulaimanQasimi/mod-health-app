<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'category_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Department::class);

        $query = Department::query()->with('category:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('Departments/Index', [
            'departments' => $this->paginationPayload($paginator, fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
                'room_number' => $department->room_number,
                'category_name' => $department->category?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-departments',
                'edit-departments',
                'delete-departments',
            ),
            'urls' => [
                'index' => route('react.departments.index'),
                'create' => route('react.departments.create'),
                'show' => url('/react/departments'),
                'edit' => url('/react/departments'),
                'destroy' => url('/react/departments'),
            ],
        ]);
    }

    public function show(Request $request, Department $department): Response
    {
        $this->authorize('view', $department);

        $department->load('category:id,name');

        return Inertia::render('Departments/Show', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'room_number' => $department->room_number,
                'category_name' => $department->category?->name,
                'created_at' => $department->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $department->updated_at?->format('Y-m-d H:i:s'),
            ],
            'permissions' => [
                'edit' => $request->user()->can('update', $department),
                'delete' => $request->user()->can('delete', $department),
            ],
            'urls' => [
                'index' => route('react.departments.index'),
                'edit' => route('react.departments.edit', $department),
                'destroy' => route('react.departments.destroy', $department),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Department::class);

        return Inertia::render('Departments/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $data = $this->validateDepartment($request);
        Department::create($data);

        return redirect()
            ->route('react.departments.index')
            ->with('success', localize('global.department_created_successfully.'));
    }

    public function edit(Request $request, Department $department): Response
    {
        $this->authorize('update', $department);

        return Inertia::render('Departments/Edit', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'room_number' => $department->room_number ?? '',
                'category_id' => $department->category_id ? (string) $department->category_id : '',
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($department),
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $department->update($this->validateDepartment($request, $department));

        return redirect()
            ->route('react.departments.index')
            ->with('success', localize('global.department_updated_successfully.'));
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()
            ->route('react.departments.index')
            ->with('success', localize('global.department_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Department $department = null): array
    {
        return [
            'index' => route('react.departments.index'),
            'store' => route('react.departments.store'),
            'update' => $department ? route('react.departments.update', $department) : '',
            'back' => route('react.departments.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDepartment(Request $request, ?Department $department = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'room_number' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data['branch_id'] = $request->user()->branch_id ?? $department?->branch_id;

        if (! $data['branch_id']) {
            abort(422, 'Branch ID is required. Please contact administrator.');
        }

        return $data;
    }
}
