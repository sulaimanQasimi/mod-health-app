<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Department;
use App\Models\Disease;
use App\Models\DiseaseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DiseaseController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'disease_category_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Disease::class);

        $query = $this->buildIndexQuery($request);
        $paginator = $this->paginateQuery($query, $request);

        $categories = DiseaseCategory::query()
            ->withCount('diseases')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Diseases/Index', [
            'diseases' => $this->paginationPayload($paginator, fn (Disease $disease) => [
                'id' => $disease->id,
                'name' => $disease->name,
                'description' => $disease->description,
                'department_id' => $disease->department_id,
                'department_name' => $disease->department?->name,
                'disease_category_id' => $disease->disease_category_id,
                'disease_category_name' => $disease->category?->name,
            ]),
            'categories' => $categories->map(fn (DiseaseCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'diseases_count' => $category->diseases_count,
            ])->values()->all(),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'diseaseCategories' => $categories->map(fn (DiseaseCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ])->values()->all(),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-diseases',
                'edit-diseases',
                'delete-diseases',
            ),
            'urls' => [
                'index' => route('diseases.index'),
                'create' => route('diseases.create'),
                'edit' => url('/diseases'),
                'destroy' => url('/diseases'),
                'storeCategory' => route('diseases.categories.store'),
                'updateCategory' => url('/diseases/categories'),
                'destroyCategory' => url('/diseases/categories'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Disease::class);

        return Inertia::render('Diseases/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Disease::class);

        Disease::create($this->validateDisease($request));

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_created_successfully.'));
    }

    public function edit(Request $request, Disease $disease): Response
    {
        $this->authorize('update', $disease);

        return Inertia::render('Diseases/Edit', [
            'disease' => [
                'id' => $disease->id,
                'name' => $disease->name,
                'description' => $disease->description ?? '',
                'department_id' => (string) $disease->department_id,
                'disease_category_id' => $disease->disease_category_id
                    ? (string) $disease->disease_category_id
                    : '',
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($disease),
        ]);
    }

    public function update(Request $request, Disease $disease): RedirectResponse
    {
        $this->authorize('update', $disease);

        $disease->update($this->validateDisease($request, $disease->id));

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_updated_successfully.'));
    }

    public function destroy(Request $request, Disease $disease): RedirectResponse
    {
        $this->authorize('delete', $disease);

        $disease->delete();

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_deleted_successfully.'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $this->authorize('create', Disease::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:disease_categories,name'],
        ]);

        DiseaseCategory::create($data);

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_category_created_successfully.'));
    }

    public function updateCategory(Request $request, DiseaseCategory $diseaseCategory): RedirectResponse
    {
        $this->authorize('update', new Disease());

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('disease_categories', 'name')->ignore($diseaseCategory->id),
            ],
        ]);

        $diseaseCategory->update($data);

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_category_updated_successfully.'));
    }

    public function destroyCategory(Request $request, DiseaseCategory $diseaseCategory): RedirectResponse
    {
        $this->authorize('delete', new Disease());

        if ($diseaseCategory->diseases()->exists()) {
            return redirect()
                ->route('diseases.index')
                ->with('error', localize('global.disease_category_has_diseases.'));
        }

        $diseaseCategory->delete();

        return redirect()
            ->route('diseases.index')
            ->with('success', localize('global.disease_category_deleted_successfully.'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Disease>
     */
    private function buildIndexQuery(Request $request)
    {
        $query = Disease::query()->with(['department:id,name', 'category:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('disease_category_id')) {
            $query->where('disease_category_id', $request->disease_category_id);
        }

        return $query->orderBy('name');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDisease(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('diseases', 'name')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'disease_category_id' => 'nullable|exists:disease_categories,id',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'diseaseCategories' => DiseaseCategory::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Disease $disease = null): array
    {
        return [
            'index' => route('diseases.index'),
            'store' => route('diseases.store'),
            'update' => $disease ? route('diseases.update', $disease) : '',
            'back' => route('diseases.index'),
        ];
    }
}
