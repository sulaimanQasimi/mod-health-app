<?php

namespace App\View\Components\Depot;

use App\Support\DepotUi;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TypeLabel extends Component
{
    public string $label;

    public function __construct(
        public string $type,
    ) {
        $this->label = DepotUi::typeLabel($type);
    }

    public function render(): View
    {
        return view('components.depot.type-label');
    }
}
