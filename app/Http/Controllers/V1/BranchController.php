<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Branch::class);

        $query = Branch::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->latest(), $request, 10);

        return Inertia::render('Branches/Index', [
            'branches' => $this->paginationPayload($paginator, fn (Branch $branch) => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-branches',
                'edit-branches',
                'delete-branches',
            ),
            'urls' => [
                'index' => route('branches.index'),
                'create' => route('branches.create'),
                'edit' => url('/branches'),
                'destroy' => url('/branches'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Branch::class);

        return Inertia::render('Branches/Create', [
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Branch::class);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'address' => 'required|string',
        ]);

        Branch::create($data);

        return redirect()
            ->route('branches.index')
            ->with('success', localize('global.branch_created_successfully.'));
    }

    public function edit(Request $request, Branch $branch): Response
    {
        $this->authorize('update', $branch);

        return Inertia::render('Branches/Edit', [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address ?? '',
            ],
            'urls' => $this->formUrls($branch),
        ]);
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('update', $branch);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'address' => 'required|string',
        ]);

        $branch->update($data);

        return redirect()
            ->route('branches.index')
            ->with('success', localize('global.branch_updated_successfully.'));
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorize('delete', $branch);

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', localize('global.branch_deleted_successfully.'));
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Branch $branch = null): array
    {
        return [
            'index' => route('branches.index'),
            'store' => route('branches.store'),
            'update' => $branch ? route('branches.update', $branch) : '',
            'back' => route('branches.index'),
        ];
    }
}
