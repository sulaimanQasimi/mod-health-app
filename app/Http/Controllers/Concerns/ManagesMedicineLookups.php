<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Medicine;
use App\Support\InventoryNav;
use App\Support\MedicineNav;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

trait ManagesMedicineLookups
{
    abstract protected function lookupModelClass(): string;

    abstract protected function lookupLabelField(): string;

    abstract protected function lookupRoutePrefix(): string;

    abstract protected function lookupPageTitle(): string;

    abstract protected function lookupNavKey(): string;

    /**
     * @return array<string, array<int, string>>
     */
    abstract protected function lookupExtraFields(): array;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Medicine::class);

        $class = $this->lookupModelClass();
        $label = $this->lookupLabelField();
        $hasActive = $this->modelHasFillable('is_active');

        $query = $class::query();
        if ($request->filled('search')) {
            $query->where($label, 'like', '%'.$request->string('search').'%');
        }

        $rows = $query->orderBy($label)->paginate(20)->withQueryString();

        return Inertia::render('Medicines/Lookups/Index', [
            'title' => __($this->lookupPageTitle()),
            'titleKey' => $this->lookupPageTitle(),
            'navKey' => $this->lookupNavKey(),
            'labelField' => $label,
            'hasActive' => $hasActive,
            'extraFields' => array_keys($this->lookupExtraFields()),
            'rows' => $rows->through(function (Model $row) use ($label, $hasActive) {
                $payload = [
                    'id' => $row->id,
                    $label => $row->{$label},
                ];
                if ($hasActive) {
                    $payload['is_active'] = (bool) ($row->is_active ?? true);
                }
                foreach (array_keys($this->lookupExtraFields()) as $field) {
                    $payload[$field] = $row->{$field};
                }

                return $payload;
            }),
            'filters' => [
                'search' => $request->input('search', ''),
            ],
            'inventoryNav' => InventoryNav::urls(),
            'medicineNav' => MedicineNav::urls(),
            'urls' => [
                'index' => route($this->lookupRoutePrefix().'.index'),
                'store' => route($this->lookupRoutePrefix().'.store'),
                'update' => url('/'.$this->lookupRoutePrefix().'/__ID__'),
                'destroy' => url('/'.$this->lookupRoutePrefix().'/__ID__'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $this->lookupModelClass()::query()->create($this->validatedLookup($request));

        return back()->with('success', __('global.created_successfully'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $row = $this->lookupModelClass()::query()->findOrFail($id);
        $row->update($this->validatedLookup($request, $row->id));

        return back()->with('success', __('global.updated_successfully'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->authorize('viewAny', Medicine::class);
        $this->lookupModelClass()::query()->findOrFail($id)->delete();

        return back()->with('success', __('global.deleted_successfully'));
    }

    private function validatedLookup(Request $request, ?int $ignoreId = null): array
    {
        $label = $this->lookupLabelField();
        $unique = 'unique:'.$this->lookupTable().','.$label;
        if ($ignoreId) {
            $unique .= ','.$ignoreId;
        }

        $rules = array_merge([
            $label => ['required', 'string', 'max:191', $unique],
        ], $this->lookupExtraFields());

        if ($this->modelHasFillable('is_active')) {
            $rules['is_active'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);
        if (array_key_exists('is_active', $rules)) {
            $data['is_active'] = $data['is_active'] ?? true;
        }

        return $data;
    }

    private function lookupTable(): string
    {
        return (new ($this->lookupModelClass()))->getTable();
    }

    private function modelHasFillable(string $field): bool
    {
        return in_array($field, (new ($this->lookupModelClass()))->getFillable(), true);
    }
}
