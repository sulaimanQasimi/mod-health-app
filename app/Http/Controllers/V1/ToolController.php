<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Tool;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    use ManagesDepotAccess;
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'is_active', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.view');

        $query = Tool::query()->with('unit:id,name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request, 15);

        return Inertia::render('Tools/Index', [
            'tools' => $this->paginationPayload($paginator, fn (Tool $tool) => [
                'id' => $tool->id,
                'name' => $tool->name,
                'code' => $tool->code,
                'unit_name' => $tool->unit?->name,
                'description' => $tool->description,
                'is_active' => (bool) $tool->is_active,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->depotCrudPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('tools.index'),
                'create' => route('tools.create'),
                'edit' => url('/tools'),
                'destroy' => url('/tools'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorizeDepotPermission('depot.create');

        return Inertia::render('Tools/Create', [
            'units' => $this->unitOptions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.create');

        Tool::create($this->validateTool($request));

        return redirect()
            ->route('tools.index')
            ->with('success', localize('global.depot.tool_created_successfully.'));
    }

    public function edit(Tool $tool): Response
    {
        $this->authorizeDepotPermission('depot.update');

        return Inertia::render('Tools/Edit', [
            'tool' => [
                'id' => $tool->id,
                'name' => $tool->name,
                'code' => $tool->code,
                'unit_id' => $tool->unit_id ? (string) $tool->unit_id : '',
                'description' => $tool->description ?? '',
                'is_active' => (bool) $tool->is_active,
            ],
            'units' => $this->unitOptions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => $this->formUrls($tool),
        ]);
    }

    public function update(Request $request, Tool $tool): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.update');

        $tool->update($this->validateTool($request, $tool));

        return redirect()
            ->route('tools.index')
            ->with('success', localize('global.depot.tool_updated_successfully.'));
    }

    public function destroy(Tool $tool): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.delete');

        $tool->delete();

        return redirect()
            ->route('tools.index')
            ->with('success', localize('global.depot.tool_deleted_successfully.'));
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function unitOptions(): array
    {
        return Unit::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Unit $unit) => [
                'id' => (int) $unit->id,
                'name' => $unit->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Tool $tool = null): array
    {
        return [
            'index' => route('tools.index'),
            'store' => route('tools.store'),
            'update' => $tool ? route('tools.update', $tool) : '',
            'back' => route('tools.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTool(Request $request, ?Tool $tool = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('tools', 'code')->ignore($tool?->id)->whereNull('deleted_at'),
            ],
            'unit_id' => ['nullable', 'exists:units,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['unit_id'] = $validated['unit_id'] ?: null;

        return $validated;
    }
}
