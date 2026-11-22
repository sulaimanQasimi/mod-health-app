<?php

namespace App\View\Components\Depot;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavTabs extends Component
{
    /** @var array<string, string> */
    public array $urls;

    /** @var list<array<string, mixed>> */
    public array $visibleTabs;

    /**
     * @param  array<string, string>  $urls
     * @param  array<string, bool>|null  $permissions
     */
    public function __construct(
        public string $active = 'index',
        array $urls = [],
        public ?array $permissions = null,
    ) {
        $this->urls = array_merge([
            'index' => route('depots.index'),
            'requests' => route('depots.requests.index'),
            'reports' => route('depots.reports.index'),
            'tools' => route('tools.index'),
        ], $urls);

        $this->visibleTabs = $this->buildVisibleTabs();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function buildVisibleTabs(): array
    {
        $tabs = [
            [
                'key' => 'index',
                'label' => localize('global.depot.list'),
                'icon' => 'bx-store',
            ],
            [
                'key' => 'requests',
                'label' => localize('global.depot.requests'),
                'icon' => 'bx-git-pull-request',
            ],
            [
                'key' => 'reports',
                'label' => localize('global.depot.reports'),
                'icon' => 'bx-bar-chart-alt-2',
            ],
            [
                'key' => 'tools',
                'label' => localize('global.depot.tools'),
                'icon' => 'bx-wrench',
            ],
        ];

        $visible = array_filter($tabs, function (array $tab) {
            if ($this->permissions === null) {
                return true;
            }

            return ($this->permissions[$tab['key']] ?? true) !== false;
        });

        return array_values(array_map(function (array $tab) {
            $tab['active'] = $this->active === $tab['key'];
            $tab['url'] = $this->urls[$tab['key']] ?? '#';

            return $tab;
        }, $visible));
    }

    public function render(): View
    {
        return view('components.depot.nav-tabs');
    }
}
