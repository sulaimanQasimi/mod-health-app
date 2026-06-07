<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\ICUProcedureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProcedureTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ICUProcedureType::class);

        $query = ICUProcedureType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('ProcedureTypes/Index', [
            'procedureTypes' => $this->paginationPayload($paginator, fn (ICUProcedureType $type) => [
                'id' => $type->id,
                'name' => $type->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-procedure-types',
                'edit-procedure-types',
                'delete-procedure-types',
            ),
            'urls' => [
                'index' => route('react.procedure-types.index'),
                'create' => route('react.procedure-types.create'),
                'edit' => url('/react/procedure-types'),
                'destroy' => url('/react/procedure-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', ICUProcedureType::class);

        return Inertia::render('ProcedureTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ICUProcedureType::class);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:i_c_u_procedure_types,name',
        ]);

        ICUProcedureType::create($data);

        return redirect()
            ->route('react.procedure-types.index')
            ->with('success', localize('global.icu_procedure_type_created_successfully.'));
    }

    public function edit(Request $request, ICUProcedureType $icuProcedureType): Response
    {
        $this->authorize('update', $icuProcedureType);

        return Inertia::render('ProcedureTypes/Edit', [
            'procedureType' => [
                'id' => $icuProcedureType->id,
                'name' => $icuProcedureType->name,
            ],
            'urls' => $this->formUrls($icuProcedureType),
        ]);
    }

    public function update(Request $request, ICUProcedureType $icuProcedureType): RedirectResponse
    {
        $this->authorize('update', $icuProcedureType);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('i_c_u_procedure_types', 'name')->ignore($icuProcedureType->id),
            ],
        ]);

        $icuProcedureType->update($data);

        return redirect()
            ->route('react.procedure-types.index')
            ->with('success', localize('global.icu_procedure_type_updated_successfully.'));
    }

    public function destroy(Request $request, ICUProcedureType $icuProcedureType): RedirectResponse
    {
        $this->authorize('delete', $icuProcedureType);

        $icuProcedureType->delete();

        return redirect()
            ->route('react.procedure-types.index')
            ->with('success', localize('global.icu_procedure_type_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?ICUProcedureType $icuProcedureType = null): array
    {
        return [
            'index' => route('react.procedure-types.index'),
            'store' => route('react.procedure-types.store'),
            'update' => $icuProcedureType ? route('react.procedure-types.update', $icuProcedureType) : '',
            'back' => route('react.procedure-types.index'),
        ];
    }
}
