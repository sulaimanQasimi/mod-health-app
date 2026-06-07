<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\User;
use App\Models\VitalSignType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class VitalSignTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', VitalSignType::class);

        $query = VitalSignType::query()->withCount('vitalSigns');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('VitalSignTypes/Index', [
            'vitalSignTypes' => $this->paginationPayload($paginator, fn (VitalSignType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'vital_signs_count' => $type->vital_signs_count,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->vitalSignTypePermissions($request->user()),
            'urls' => [
                'index' => route('react.vital-sign-types.index'),
                'create' => route('react.vital-sign-types.create'),
                'show' => url('/react/vital-sign-types'),
                'edit' => url('/react/vital-sign-types'),
                'destroy' => url('/react/vital-sign-types'),
            ],
        ]);
    }

    public function show(Request $request, VitalSignType $vitalSignType): Response
    {
        $this->authorize('view', $vitalSignType);

        $vitalSignType->loadCount('vitalSigns');

        return Inertia::render('VitalSignTypes/Show', [
            'vitalSignType' => [
                'id' => $vitalSignType->id,
                'name' => $vitalSignType->name,
                'vital_signs_count' => $vitalSignType->vital_signs_count,
                'created_at' => $vitalSignType->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $vitalSignType->updated_at?->format('Y-m-d H:i:s'),
            ],
            'permissions' => [
                'edit' => $request->user()->can('update', $vitalSignType),
                'delete' => $request->user()->can('delete', $vitalSignType),
            ],
            'urls' => [
                'index' => route('react.vital-sign-types.index'),
                'edit' => route('react.vital-sign-types.edit', $vitalSignType),
                'destroy' => route('react.vital-sign-types.destroy', $vitalSignType),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', VitalSignType::class);

        return Inertia::render('VitalSignTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', VitalSignType::class);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:vital_sign_types,name',
        ]);

        VitalSignType::create($data);

        return redirect()
            ->route('react.vital-sign-types.index')
            ->with('success', localize('global.vital_sign_type_created_successfully'));
    }

    public function edit(Request $request, VitalSignType $vitalSignType): Response
    {
        $this->authorize('update', $vitalSignType);

        return Inertia::render('VitalSignTypes/Edit', [
            'vitalSignType' => [
                'id' => $vitalSignType->id,
                'name' => $vitalSignType->name,
            ],
            'urls' => $this->formUrls($vitalSignType),
        ]);
    }

    public function update(Request $request, VitalSignType $vitalSignType): RedirectResponse
    {
        $this->authorize('update', $vitalSignType);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vital_sign_types', 'name')->ignore($vitalSignType->id),
            ],
        ]);

        $vitalSignType->update($data);

        return redirect()
            ->route('react.vital-sign-types.index')
            ->with('success', localize('global.vital_sign_type_updated_successfully'));
    }

    public function destroy(Request $request, VitalSignType $vitalSignType): RedirectResponse
    {
        $this->authorize('delete', $vitalSignType);

        if ($vitalSignType->vitalSigns()->exists()) {
            return redirect()
                ->route('react.vital-sign-types.index')
                ->with('error', 'Cannot delete vital sign type with associated vital signs.');
        }

        $vitalSignType->delete();

        return redirect()
            ->route('react.vital-sign-types.index')
            ->with('success', localize('global.vital_sign_type_deleted_successfully'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?VitalSignType $vitalSignType = null): array
    {
        return [
            'index' => route('react.vital-sign-types.index'),
            'store' => route('react.vital-sign-types.store'),
            'update' => $vitalSignType ? route('react.vital-sign-types.update', $vitalSignType) : '',
            'back' => route('react.vital-sign-types.index'),
        ];
    }

    /**
     * @return array{create: bool, edit: bool, delete: bool}
     */
    private function vitalSignTypePermissions(User $user): array
    {
        $isAdmin = $user->hasRole(['super_admin', 'admin']);

        return [
            'create' => $isAdmin || $user->hasPermissionTo('create-vital-sign-types'),
            'edit' => $isAdmin || $user->hasPermissionTo('update-vital-sign-types'),
            'delete' => $isAdmin || $user->hasPermissionTo('delete-vital-sign-types'),
        ];
    }
}
