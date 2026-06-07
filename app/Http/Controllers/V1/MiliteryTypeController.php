<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\MiliteryType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MiliteryTypeController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MiliteryType::class);

        $query = MiliteryType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('MiliteryTypes/Index', [
            'militeryTypes' => $this->paginationPayload($paginator, fn (MiliteryType $militeryType) => [
                'id' => $militeryType->id,
                'name' => $militeryType->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-militery-types',
                'edit-militery-types',
                'delete-militery-types',
            ),
            'urls' => [
                'index' => route('react.militery-types.index'),
                'create' => route('react.militery-types.create'),
                'edit' => url('/react/militery-types'),
                'destroy' => url('/react/militery-types'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', MiliteryType::class);

        return Inertia::render('MiliteryTypes/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MiliteryType::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        MiliteryType::create($data);

        return redirect()
            ->route('react.militery-types.index')
            ->with('success', localize('global.militery_type_created_successfully.'));
    }

    public function edit(Request $request, MiliteryType $militeryType): Response
    {
        $this->authorize('update', $militeryType);

        return Inertia::render('MiliteryTypes/Edit', [
            'militeryType' => [
                'id' => $militeryType->id,
                'name' => $militeryType->name,
            ],
            'urls' => $this->formUrls($militeryType),
        ]);
    }

    public function update(Request $request, MiliteryType $militeryType): RedirectResponse
    {
        $this->authorize('update', $militeryType);

        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $militeryType->update($data);

        return redirect()
            ->route('react.militery-types.index')
            ->with('success', localize('global.militery_type_updated_successfully.'));
    }

    public function destroy(Request $request, MiliteryType $militeryType): RedirectResponse
    {
        $this->authorize('delete', $militeryType);

        $militeryType->delete();

        return redirect()
            ->route('react.militery-types.index')
            ->with('success', localize('global.militery_type_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?MiliteryType $militeryType = null): array
    {
        return [
            'index' => route('react.militery-types.index'),
            'store' => route('react.militery-types.store'),
            'update' => $militeryType ? route('react.militery-types.update', $militeryType) : '',
            'back' => route('react.militery-types.index'),
        ];
    }
}
