<?php

namespace App\View\Components\Depot;

use App\Support\DepotUi;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WorkflowStepper extends Component
{
    public bool $isTerminal;

    public string $statusClass;

    public string $statusLabel;

    /** @var list<array<string, mixed>> */
    public array $stepItems;

    /**
     * @param  list<string>  $steps
     */
    public function __construct(
        public array $steps = [],
        public int $currentRank = 0,
        public string $status = '',
    ) {
        $this->isTerminal = in_array($status, ['rejected', 'cancelled'], true);
        $this->statusClass = DepotUi::statusBadgeClass($status);
        $this->statusLabel = DepotUi::requestStatusLabel($status);

        $lastIndex = count($steps) - 1;
        $items = [];

        foreach (array_values($steps) as $index => $step) {
            $isComplete = ! $this->isTerminal && $index < $currentRank;
            $isCurrent = ! $this->isTerminal && $index === $currentRank;

            $items[] = [
                'index' => $index,
                'number' => $index + 1,
                'label' => DepotUi::requestStatusLabel($step),
                'isComplete' => $isComplete,
                'isCurrent' => $isCurrent,
                'isLast' => $index === $lastIndex,
                'stateClass' => $isComplete ? 'is-complete' : ($isCurrent ? 'is-current' : 'is-upcoming'),
            ];
        }

        $this->stepItems = $items;
    }

    public function render(): View
    {
        return view('components.depot.workflow-stepper');
    }
}
