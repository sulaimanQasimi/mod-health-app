<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RelationController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Relation::class);

        $query = Relation::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('Relations/Index', [
            'relations' => $this->paginationPayload($paginator, fn (Relation $relation) => [
                'id' => $relation->id,
                'name' => $relation->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-relations',
                'edit-relations',
                'delete-relations',
            ),
            'urls' => [
                'index' => route('react.relations.index'),
                'create' => route('react.relations.create'),
                'edit' => url('/react/relations'),
                'destroy' => url('/react/relations'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Relation::class);

        return Inertia::render('Relations/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Relation::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        Relation::create($data);

        return redirect()
            ->route('react.relations.index')
            ->with('success', localize('global.relation_created_successfully.'));
    }

    public function edit(Request $request, Relation $relation): Response
    {
        $this->authorize('update', $relation);

        return Inertia::render('Relations/Edit', [
            'relation' => [
                'id' => $relation->id,
                'name' => $relation->name,
            ],
            'urls' => $this->formUrls($relation),
        ]);
    }

    public function update(Request $request, Relation $relation): RedirectResponse
    {
        $this->authorize('update', $relation);

        $data = $request->validate([
            'name' => 'required|string|max:191',
        ]);

        $relation->update($data);

        return redirect()
            ->route('react.relations.index')
            ->with('success', localize('global.relation_updated_successfully.'));
    }

    public function destroy(Request $request, Relation $relation): RedirectResponse
    {
        $this->authorize('delete', $relation);

        $relation->delete();

        return redirect()
            ->route('react.relations.index')
            ->with('success', localize('global.relation_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Relation $relation = null): array
    {
        return [
            'index' => route('react.relations.index'),
            'store' => route('react.relations.store'),
            'update' => $relation ? route('react.relations.update', $relation) : '',
            'back' => route('react.relations.index'),
        ];
    }
}
