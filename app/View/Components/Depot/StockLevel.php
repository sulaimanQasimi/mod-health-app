<?php

namespace App\View\Components\Depot;

use App\Support\DepotUi;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StockLevel extends Component
{
    public string $level;

    public string $badgeClass;

    public string $label;

    public string $barClass;

    public bool $showBar;

    public int $barPercent;

    public function __construct(
        public int $quantity,
        public ?string $status = null,
        public ?int $maxQuantity = null,
    ) {
        $this->level = $status ?: DepotUi::stockLevel($quantity);
        $this->badgeClass = DepotUi::stockLevelBadgeClass($this->level);
        $this->label = DepotUi::stockLevelLabel($this->level);
        $this->barClass = DepotUi::stockBarClass($this->level);
        $this->showBar = $maxQuantity !== null;
        $this->barPercent = $this->showBar
            ? min(100, max(0, (int) round(($quantity / max(1, $maxQuantity)) * 100)))
            : 0;
    }

    public function render(): View
    {
        return view('components.depot.stock-level');
    }
}
