<?php

namespace App\View\Components\Depot;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RequestContext extends Component
{
    /** @var array<string, mixed> */
    public array $contextData;

    /** @var list<array{label: string, value: mixed}> */
    public array $fields;

    /**
     * @param  array<string, mixed>|object  $context
     */
    public function __construct(
        public array|object $context = [],
    ) {
        $this->contextData = is_array($context) ? $context : (array) $context;

        $this->fields = [
            ['label' => localize('global.depot.branch'), 'value' => $this->contextData['branch_name'] ?? null],
            ['label' => localize('global.depot.requesting_department'), 'value' => $this->contextData['department_name'] ?? null],
            ['label' => localize('global.depot.request_user'), 'value' => $this->contextData['request_user_name'] ?? null],
            ['label' => localize('global.depot.pharmacy_depot'), 'value' => $this->contextData['pharmacy_depot_label'] ?? null],
        ];
    }

    public function render(): View
    {
        return view('components.depot.request-context');
    }
}
