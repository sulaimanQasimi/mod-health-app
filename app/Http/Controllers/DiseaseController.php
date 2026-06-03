<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Disease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class DiseaseController extends Controller
{
    public function index()
    {
        return view('pages.diseases.index', [
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return redirect()->route('diseases.index');
    }

    public function edit(Disease $disease)
    {
        return redirect()->route('diseases.index');
    }

    public function show(Disease $disease)
    {
        return redirect()->route('diseases.index');
    }

    public function apiShow(Disease $disease): JsonResponse
    {
        $disease->load(['department', 'category']);

        return response()->json([
            'success' => true,
            'data' => $this->formatDisease($disease),
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $diseases = $this->diseaseQuery($request)->paginate(10);

        return response()->json([
            'success' => true,
            'data' => collect($diseases->items())->map(fn (Disease $d) => $this->formatDisease($d)),
            'meta' => [
                'current_page' => $diseases->currentPage(),
                'last_page' => $diseases->lastPage(),
                'per_page' => $diseases->perPage(),
                'total' => $diseases->total(),
                'from' => $diseases->firstItem(),
                'to' => $diseases->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateDisease($request);

        $disease = Disease::create($validated);
        $disease->load(['department', 'category']);

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_created_successfully.'),
            'data' => $this->formatDisease($disease),
        ], 201);
    }

    public function update(Request $request, Disease $disease): JsonResponse
    {
        $validated = $this->validateDisease($request, $disease->id);

        $disease->update($validated);
        $disease->load(['department', 'category']);

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_updated_successfully.'),
            'data' => $this->formatDisease($disease),
        ]);
    }

    public function destroy(Disease $disease): JsonResponse
    {
        $disease->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_deleted_successfully.'),
        ]);
    }

    private function diseaseQuery(Request $request)
    {
        $query = Disease::with(['department', 'category']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('department', fn ($d) => $d->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('disease_category_id')) {
            $query->where('disease_category_id', $request->disease_category_id);
        }

        return $query->orderBy('name');
    }

    private function validateDisease(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('diseases')
                    ->where(fn ($query) => $query->where('department_id', $request->department_id))
                    ->ignore($ignoreId),
            ],
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'disease_category_id' => 'nullable|exists:disease_categories,id',
        ]);
    }

    private function formatDisease(Disease $disease): array
    {
        return [
            'id' => $disease->id,
            'name' => $disease->name,
            'description' => $disease->description,
            'department_id' => $disease->department_id,
            'department_name' => $disease->department?->name,
            'disease_category_id' => $disease->disease_category_id,
            'category_name' => $disease->category?->name,
        ];
    }
}
