<?php

namespace App\View\Components\Depot;

use App\Models\Depot;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class DepotForm extends Component
{
    /** @var Collection<int, mixed> */
    public Collection $selectedUsers;

    /** @var Collection<int, mixed> */
    public Collection $selectedRoles;

    /** @var list<string> */
    public array $roleOptions = ['staff', 'manager', 'procurement', 'viewer'];

    /**
     * @param  iterable<int, object>  $branches
     * @param  iterable<int, object>  $departments
     * @param  iterable<int, object>  $pharmacies
     * @param  iterable<int, object>  $depots
     * @param  iterable<int, object>  $users
     */
    public function __construct(
        public ?Depot $depot = null,
        public iterable $branches = [],
        public iterable $departments = [],
        public iterable $pharmacies = [],
        public iterable $depots = [],
        public iterable $users = [],
    ) {
        $this->selectedUsers = collect(old('user_ids', $depot ? $depot->activeUsers->pluck('id')->all() : []))->values();
        $this->selectedRoles = collect(old('roles', $depot ? $depot->activeUsers->pluck('pivot.role')->all() : []))->values();

        if ($this->selectedUsers->isEmpty()) {
            $this->selectedUsers = collect(['']);
            $this->selectedRoles = collect(['staff']);
        }
    }

    public function render(): View
    {
        return view('components.depot.depot-form');
    }
}
