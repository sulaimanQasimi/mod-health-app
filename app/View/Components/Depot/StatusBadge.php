<?php

namespace App\View\Components\Depot;

use App\Support\DepotUi;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusBadge extends Component
{
    public string $badgeClass;

    public string $label;

    /**
     * @param  string  $type  request|transaction
     * @param  bool  $badge  When false, only the localized label is rendered (no badge markup).
     */
    public function __construct(
        public string $status,
        public string $type = 'request',
        public bool $badge = true,
    ) {
        $this->badgeClass = DepotUi::statusBadgeClass($status);
        $this->label = DepotUi::requestStatusLabel($status);
    }

    public function render(): View
    {
        return view('components.depot.status-badge');
    }
}
