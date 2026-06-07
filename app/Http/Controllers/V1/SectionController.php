<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'department_id', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Section::class);

        $query = Section::query()->with('department:id,name');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request, 10);

        return Inertia::render('Sections/Index', [
            'sections' => $this->paginationPayload($paginator, fn (Section $section) => [
                'id' => $section->id,
                'name' => $section->name,
                'department_name' => $section->department?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-sections',
                'edit-sections',
                'delete-sections',
            ),
            'urls' => [
                'index' => route('react.sections.index'),
                'create' => route('react.sections.create'),
                'edit' => url('/react/sections'),
                'destroy' => url('/react/sections'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Section::class);

        return Inertia::render('Sections/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Section::class);

        Section::create($this->validateSection($request));

        return redirect()
            ->route('react.sections.index')
            ->with('success', localize('global.section_created_successfully.'));
    }

    public function edit(Request $request, Section $section): Response
    {
        $this->authorize('update', $section);

        return Inertia::render('Sections/Edit', [
            'section' => [
                'id' => $section->id,
                'name' => $section->name,
                'department_id' => (string) $section->department_id,
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($section),
        ]);
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('update', $section);

        $section->update($this->validateSection($request, $section));

        return redirect()
            ->route('react.sections.index')
            ->with('success', localize('global.section_updated_successfully.'));
    }

    public function destroy(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('delete', $section);

        $section->delete();

        return redirect()
            ->route('react.sections.index')
            ->with('success', localize('global.section_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Section $section = null): array
    {
        return [
            'index' => route('react.sections.index'),
            'store' => route('react.sections.store'),
            'update' => $section ? route('react.sections.update', $section) : '',
            'back' => route('react.sections.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSection(Request $request, ?Section $section = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'department_id' => 'required|exists:departments,id',
        ]);

        $data['branch_id'] = $request->user()->branch_id ?? $section?->branch_id;

        if (! $data['branch_id']) {
            abort(422, 'Branch ID is required. Please contact administrator.');
        }

        return $data;
    }
}
