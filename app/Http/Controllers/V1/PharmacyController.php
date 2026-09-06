<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    private const PHARMACY_ROLES = ['manager', 'staff', 'procurement', 'viewer'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pharmacy::class);

        $query = Pharmacy::query()->with([
            'activeUsers:id,name,last_name,email',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->latest(), $request, 15);

        return Inertia::render('Pharmacies/Index', [
            'pharmacies' => $this->paginationPayload($paginator, fn (Pharmacy $pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'phone' => $pharmacy->phone,
                'address' => $pharmacy->address,
                'users_count' => $pharmacy->activeUsers->count(),
                'users' => $pharmacy->activeUsers->map(fn (User $user) => [
                    'id' => $user->id,
                    'full_name' => trim("{$user->name} {$user->last_name}"),
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                ])->values()->all(),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->pharmacyPermissions($request->user()),
            'urls' => [
                'index' => route('pharmacies.index'),
                'create' => route('pharmacies.create'),
                'show' => url('/pharmacies'),
                'edit' => url('/pharmacies'),
                'manageUsers' => url('/pharmacies'),
                'destroy' => url('/pharmacies'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Pharmacy::class);

        return Inertia::render('Pharmacies/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Pharmacy::class);

        $data = $this->validatePharmacy($request);

        DB::transaction(function () use ($data) {
            $pharmacy = Pharmacy::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ]);

            $this->syncPharmacyUsers($pharmacy, $data['user_ids'], $data['roles']);
        });

        return redirect()
            ->route('pharmacies.index')
            ->with('success', localize('global.pharmacy_created_successfully.'));
    }

    public function show(Request $request, Pharmacy $pharmacy): Response
    {
        $this->authorize('view', $pharmacy);

        $pharmacy->load([
            'activeUsers:id,name,last_name,email',
            'createdBy:id,name,last_name',
            'updatedBy:id,name,last_name',
        ]);

        $statistics = $pharmacy->getStatistics();

        return Inertia::render('Pharmacies/Show', [
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'phone' => $pharmacy->phone,
                'address' => $pharmacy->address,
                'created_at' => $pharmacy->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $pharmacy->updated_at?->format('Y-m-d H:i:s'),
                'created_by_name' => $pharmacy->createdBy
                    ? trim("{$pharmacy->createdBy->name} {$pharmacy->createdBy->last_name}")
                    : null,
                'users' => $pharmacy->activeUsers->map(fn (User $user) => [
                    'id' => $user->id,
                    'full_name' => trim("{$user->name} {$user->last_name}"),
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->joined_at,
                ])->values()->all(),
            ],
            'statistics' => [
                'total_users' => $statistics['total_users'],
                'managers_count' => $statistics['managers_count'],
                'staff_count' => $statistics['staff_count'],
                'total_outcomes' => $statistics['total_outcomes'],
            ],
            'permissions' => [
                'edit' => $request->user()->can('update', $pharmacy),
                'delete' => $request->user()->can('delete', $pharmacy),
                'manage_users' => $request->user()->can('manageUsers', $pharmacy),
            ],
            'urls' => [
                'index' => route('pharmacies.index'),
                'edit' => route('pharmacies.edit', $pharmacy),
                'destroy' => route('pharmacies.destroy', $pharmacy),
                'manageUsers' => route('pharmacies.manage-users', $pharmacy),
            ],
        ]);
    }

    public function edit(Request $request, Pharmacy $pharmacy): Response
    {
        $this->authorize('update', $pharmacy);

        $pharmacy->load('activeUsers:id,name,last_name,email');

        return Inertia::render('Pharmacies/Edit', [
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
                'phone' => $pharmacy->phone,
                'address' => $pharmacy->address,
                'assignments' => $pharmacy->activeUsers->map(fn (User $user) => [
                    'user_id' => (string) $user->id,
                    'role' => $user->pivot->role,
                ])->values()->all(),
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($pharmacy),
        ]);
    }

    public function update(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->authorize('update', $pharmacy);

        $data = $this->validatePharmacy($request);

        DB::transaction(function () use ($pharmacy, $data) {
            $pharmacy->update([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
            ]);

            $pharmacy->users()->detach();
            $this->syncPharmacyUsers($pharmacy, $data['user_ids'], $data['roles']);
        });

        return redirect()
            ->route('pharmacies.index')
            ->with('success', localize('global.pharmacy_updated_successfully.'));
    }

    public function destroy(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->authorize('delete', $pharmacy);

        $pharmacy->delete();

        return redirect()
            ->route('pharmacies.index')
            ->with('success', localize('global.pharmacy_deleted_successfully.'));
    }

    public function manageUsers(Request $request, Pharmacy $pharmacy): Response
    {
        $this->authorize('manageUsers', $pharmacy);

        $pharmacy->load('activeUsers:id,name,last_name,email');
        $assignedIds = $pharmacy->activeUsers->pluck('id')->all();

        return Inertia::render('Pharmacies/ManageUsers', [
            'pharmacy' => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ],
            'users' => $pharmacy->activeUsers->map(fn (User $user) => [
                'id' => $user->id,
                'full_name' => trim("{$user->name} {$user->last_name}"),
                'email' => $user->email,
                'role' => $user->pivot->role,
                'joined_at' => $user->pivot->joined_at,
            ])->values()->all(),
            'availableUsers' => User::query()
                ->when($assignedIds, fn ($query) => $query->whereNotIn('id', $assignedIds))
                ->orderBy('name')
                ->get(['id', 'name', 'last_name', 'email'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'full_name' => trim("{$user->name} {$user->last_name}"),
                    'email' => $user->email,
                ])
                ->values()
                ->all(),
            'roles' => self::PHARMACY_ROLES,
            'urls' => [
                'show' => route('pharmacies.show', $pharmacy),
                'index' => route('pharmacies.index'),
                'addUser' => route('pharmacies.users.store', $pharmacy),
                'removeUser' => route('pharmacies.users.remove', $pharmacy),
                'updateUser' => url("/pharmacies/{$pharmacy->id}/users"),
            ],
        ]);
    }

    public function addUser(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->authorize('manageUsers', $pharmacy);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(self::PHARMACY_ROLES)],
        ]);

        if ($pharmacy->hasUser($data['user_id'])) {
            return redirect()
                ->back()
                ->with('error', localize('global.user_already_assigned_to_pharmacy'));
        }

        $pharmacy->addUser($data['user_id'], $data['role']);

        return redirect()
            ->back()
            ->with('success', localize('global.user_added_to_pharmacy_successfully'));
    }

    public function removeUser(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $this->authorize('manageUsers', $pharmacy);

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $pharmacy->removeUser($data['user_id']);

        return redirect()
            ->back()
            ->with('success', localize('global.user_removed_from_pharmacy_successfully'));
    }

    public function updateUserRole(Request $request, Pharmacy $pharmacy, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $pharmacy);

        $data = $request->validate([
            'role' => ['required', Rule::in(self::PHARMACY_ROLES)],
        ]);

        if (! $pharmacy->hasUser($user->id)) {
            return redirect()
                ->back()
                ->with('error', localize('global.user_not_belong_to_pharmacy.'));
        }

        $pharmacy->updateUserRole($user->id, $data['role']);

        return redirect()
            ->back()
            ->with('success', localize('global.user_role_updated_successfully'));
    }

    /**
     * @return array{create: bool, edit: bool, delete: bool, view: bool, manage_users: bool}
     */
    private function pharmacyPermissions(User $user): array
    {
        $isAdmin = $user->hasRole(['super_admin', 'admin']);

        return [
            'view' => $isAdmin || $user->hasPermissionTo('pharmacy.show'),
            'create' => $isAdmin || $user->hasPermissionTo('pharmacy.create'),
            'edit' => $isAdmin || $user->hasPermissionTo('pharmacy.edit'),
            'delete' => $isAdmin || $user->hasPermissionTo('pharmacy.delete'),
            'manage_users' => $isAdmin || $user->hasPermissionTo('pharmacy.manage_users'),
        ];
    }

    /**
     * @return array{users: list<array{id: int, full_name: string, email: string}>}
     */
    private function buildFormData(): array
    {
        return [
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'last_name', 'email'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'full_name' => trim("{$user->name} {$user->last_name}"),
                    'email' => $user->email,
                ])
                ->values()
                ->all(),
            'roles' => self::PHARMACY_ROLES,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Pharmacy $pharmacy = null): array
    {
        return [
            'index' => route('pharmacies.index'),
            'store' => route('pharmacies.store'),
            'update' => $pharmacy ? route('pharmacies.update', $pharmacy) : '',
            'back' => route('pharmacies.index'),
        ];
    }

    /**
     * @return array{name: string, phone: string, address: string, user_ids: list<int>, roles: list<string>}
     */
    private function validatePharmacy(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'address' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|exists:users,id|distinct',
            'roles' => 'required|array|min:1',
            'roles.*' => ['required', Rule::in(self::PHARMACY_ROLES)],
        ]);

        if (count($data['user_ids']) !== count($data['roles'])) {
            abort(422, 'User assignments must include a role for each user.');
        }

        return $data;
    }

    /**
     * @param  list<int|string>  $userIds
     * @param  list<string>  $roles
     */
    private function syncPharmacyUsers(Pharmacy $pharmacy, array $userIds, array $roles): void
    {
        foreach ($userIds as $index => $userId) {
            $pharmacy->addUser((int) $userId, $roles[$index] ?? 'staff');
        }
    }
}
