<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\PhysiotherapyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PhysiotherapyTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PhysiotherapyType::class);

        $query = PhysiotherapyType::query()
            ->with(['createdBy:id,name'])
            ->withCount('physiotherapyProcedures');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderByDesc('created_at'), $request);

        return Inertia::render('PhysiotherapyTypes/Index', [
            'physiotherapyTypes' => $this->paginationPayload($paginator, fn (PhysiotherapyType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'procedures_count' => $type->physiotherapy_procedures_count,
                'created_by_name' => $type->createdBy?->name,
                'created_at' => $type->created_at?->format('Y-m-d H:i'),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-physiotherapy-types',
                'edit-physiotherapy-types',
                'delete-physiotherapy-types',
            ),
            'urls' => [
                'index' => route('physiotherapy-types.index'),
                'create' => route('physiotherapy-types.create'),
                'edit' => url('/physiotherapy-types'),
                'destroy' => url('/physiotherapy-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', PhysiotherapyType::class);

        return Inertia::render('PhysiotherapyTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PhysiotherapyType::class);

        $data = $this->validateType($request);

        PhysiotherapyType::create($data);

        return redirect()
            ->route('physiotherapy-types.index')
            ->with('success', localize('global.physiotherapy_type_created_successfully'));
    }

    public function edit(Request $request, PhysiotherapyType $physiotherapyType): Response
    {
        $this->authorize('update', $physiotherapyType);

        return Inertia::render('PhysiotherapyTypes/Edit', [
            'physiotherapyType' => [
                'id' => $physiotherapyType->id,
                'name' => $physiotherapyType->name,
                'description' => $physiotherapyType->description ?? '',
            ],
            'urls' => $this->formUrls($physiotherapyType),
        ]);
    }

    public function update(Request $request, PhysiotherapyType $physiotherapyType): RedirectResponse
    {
        $this->authorize('update', $physiotherapyType);

        $data = $this->validateType($request, $physiotherapyType);

        $physiotherapyType->update($data);

        return redirect()
            ->route('physiotherapy-types.index')
            ->with('success', localize('global.physiotherapy_type_updated_successfully'));
    }

    public function destroy(Request $request, PhysiotherapyType $physiotherapyType): RedirectResponse
    {
        $this->authorize('delete', $physiotherapyType);

        if ($physiotherapyType->physiotherapyProcedures()->exists()) {
            return redirect()
                ->route('physiotherapy-types.index')
                ->with('error', localize('global.cannot_delete_physiotherapy_type_with_procedures'));
        }

        $physiotherapyType->delete();

        return redirect()
            ->route('physiotherapy-types.index')
            ->with('success', localize('global.physiotherapy_type_deleted_successfully'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateType(Request $request, ?PhysiotherapyType $physiotherapyType = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('physiotherapy_types', 'name')->ignore($physiotherapyType?->id),
            ],
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => localize('global.name_required'),
            'name.unique' => localize('global.name_already_exists'),
            'description.max' => localize('global.description_max_length'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?PhysiotherapyType $physiotherapyType = null): array
    {
        return [
            'index' => route('physiotherapy-types.index'),
            'store' => route('physiotherapy-types.store'),
            'update' => $physiotherapyType ? route('physiotherapy-types.update', $physiotherapyType) : '',
            'back' => route('physiotherapy-types.index'),
        ];
    }
}
