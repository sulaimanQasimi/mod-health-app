<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Branch;
use App\Models\Department;
use App\Models\OperationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', OperationType::class);

        $query = OperationType::query()->with(['branch:id,name', 'department:id,name']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('OperationTypes/Index', [
            'operationTypes' => $this->paginationPayload($paginator, fn (OperationType $operationType) => [
                'id' => $operationType->id,
                'name' => $operationType->name,
                'branch_id' => $operationType->branch_id,
                'branch_name' => $operationType->branch?->name,
                'department_id' => $operationType->department_id,
                'department_name' => $operationType->department?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-operation-types',
                'edit-operation-types',
                'delete-operation-types',
            ),
            'urls' => [
                'index' => route('react.operation-types.index'),
                'create' => route('react.operation-types.create'),
                'edit' => url('/react/operation-types'),
                'destroy' => url('/react/operation-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', OperationType::class);

        return Inertia::render('OperationTypes/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', OperationType::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        OperationType::create($data);

        return redirect()
            ->route('react.operation-types.index')
            ->with('success', localize('global.operation_type_created_successfully.'));
    }

    public function edit(Request $request, OperationType $operationType): Response
    {
        $this->authorize('update', $operationType);

        return Inertia::render('OperationTypes/Edit', [
            'operationType' => [
                'id' => $operationType->id,
                'name' => $operationType->name,
                'branch_id' => (string) $operationType->branch_id,
                'department_id' => (string) $operationType->department_id,
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($operationType),
        ]);
    }

    public function update(Request $request, OperationType $operationType): RedirectResponse
    {
        $this->authorize('update', $operationType);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $operationType->update($data);

        return redirect()
            ->route('react.operation-types.index')
            ->with('success', localize('global.operation_type_updated_successfully.'));
    }

    public function destroy(Request $request, OperationType $operationType): RedirectResponse
    {
        $this->authorize('delete', $operationType);

        $operationType->delete();

        return redirect()
            ->route('react.operation-types.index')
            ->with('success', localize('global.operation_type_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?OperationType $operationType = null): array
    {
        return [
            'index' => route('react.operation-types.index'),
            'store' => route('react.operation-types.store'),
            'update' => $operationType ? route('react.operation-types.update', $operationType) : '',
            'back' => route('react.operation-types.index'),
        ];
    }
}
