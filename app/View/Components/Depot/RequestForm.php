<?php

namespace App\View\Components\Depot;

use App\Support\DepotRequestContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class RequestForm extends Component
{
    /** @var array<string, mixed> */
    public array $defaults;

    public string $destinationType;

    public bool $isPharmacyDestination;

    /** @var iterable<int, mixed> */
    public iterable $pharmacyChoices;

    public mixed $notesValue;

    /** @var array<string, mixed> */
    public array $initialContext;

    /** @var array<int|string, int|string|null> */
    public array $parentMap;

    /** @var list<array<string, mixed>> */
    public array $contextDepots;

    /** @var array<int|string, string> */
    public array $contextPharmacyNames;

    public ?string $contextUserName;

    /** @var list<array{id: int|string, name: string}> */
    public array $sourceOptions;

    /** @var list<array<string, mixed>> */
    public array $formItems;

    public int $nextIndex;

    /**
     * @param  array<string, mixed>|null  $defaults
     * @param  iterable<int, object>  $depots
     * @param  iterable<int, object>  $pharmacies
     * @param  iterable<int, object>  $medicines
     * @param  iterable<int, object>  $tools
     * @param  iterable<int, object>  $units
     * @param  array<int, array<string, mixed>>|null  $formItems
     * @param  array<int, array<string, mixed>>  $sourceDepotOptions
     * @param  array<string, mixed>  $formData
     * @param  iterable<int, object>|null  $userPharmacies
     */
    public function __construct(
        ?array $defaults = null,
        public iterable $depots = [],
        public iterable $pharmacies = [],
        public iterable $medicines = [],
        public iterable $tools = [],
        public iterable $units = [],
        ?array $formItems = null,
        public array $sourceDepotOptions = [],
        public string $viewContext = 'depot',
        public bool $lockRequestingDepot = false,
        public ?object $depotRequest = null,
        public array $formData = [],
        public ?iterable $userPharmacies = null,
    ) {
        $this->defaults = $defaults ?? [
            'destination_type' => 'depot',
            'requesting_depot_id' => '',
            'pharmacy_id' => '',
            'source_depot_id' => '',
        ];

        $this->formItems = $formItems
            ?? [['medicine_id' => '', 'tool_id' => '', 'unit_id' => '', 'quantity' => 1, 'batch_number' => '']];
        $this->nextIndex = count($this->formItems);

        $this->destinationType = old('destination_type', $this->defaults['destination_type'] ?? 'depot');
        $this->isPharmacyDestination = $this->destinationType === 'pharmacy';

        $depots = collect($this->depots);
        $pharmacies = collect($this->pharmacies);
        $userPharmacyList = collect($this->userPharmacies ?? []);

        $this->pharmacyChoices = ($this->viewContext === 'pharmacy' && $userPharmacyList->isNotEmpty())
            ? $userPharmacyList
            : $pharmacies;

        $this->notesValue = old('notes', $this->depotRequest->notes ?? '');

        $this->sourceOptions = count($this->sourceDepotOptions)
            ? array_values($this->sourceDepotOptions)
            : $depots->map(fn ($depot) => ['id' => $depot->id, 'name' => $depot->name])->values()->all();

        $this->parentMap = $depots
            ->mapWithKeys(fn ($depot) => [$depot->id => $depot->parent_depot_id])
            ->all();

        $contextDepots = collect($this->formData['activeDepots'] ?? [])->keyBy('id');
        $this->contextDepots = $contextDepots->values()->all();
        $this->contextPharmacyNames = collect($this->pharmacyChoices)
            ->mapWithKeys(fn ($pharmacy) => [$pharmacy->id => $pharmacy->name])
            ->all();
        $this->contextUserName = DepotRequestContext::userDisplayName(auth()->user());

        $this->initialContext = $this->buildInitialContext($contextDepots);
    }

    /**
     * @param  Collection<int|string, mixed>  $contextDepots
     * @return array<string, mixed>
     */
    protected function buildInitialContext(Collection $contextDepots): array
    {
        $selectedRequestingId = old('requesting_depot_id', $this->defaults['requesting_depot_id'] ?? '');
        $selectedPharmacyId = old('pharmacy_id', $this->defaults['pharmacy_id'] ?? '');

        $contextDepot = $this->isPharmacyDestination
            ? $contextDepots->first(fn ($depot) => (string) ($depot['pharmacy_id'] ?? '') === (string) $selectedPharmacyId)
            : $contextDepots->get((int) $selectedRequestingId);

        return [
            'branch_name' => data_get($contextDepot, 'branch_name'),
            'department_name' => data_get($contextDepot, 'department_name'),
            'request_user_name' => $this->contextUserName,
            'pharmacy_depot_label' => $this->isPharmacyDestination
                ? ($this->contextPharmacyNames[(int) $selectedPharmacyId] ?? data_get($contextDepot, 'name'))
                : data_get($contextDepot, 'name'),
        ];
    }

    public function render(): View
    {
        return view('components.depot.request-form');
    }
}
