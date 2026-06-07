<?php

namespace App\Http\Controllers\V1\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait PaginatesInertiaIndex
{
    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function paginateQuery(
        Builder $query,
        Request $request,
        int $defaultPerPage = 15,
        array $allowedPerPage = [10, 15, 20, 25, 50, 100],
    ): LengthAwarePaginator {
        $perPage = (int) $request->input('per_page', $defaultPerPage);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = $defaultPerPage;
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * @return array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}
     */
    protected function paginationPayload(LengthAwarePaginator $paginator, callable $transform): array
    {
        return [
            'data' => collect($paginator->items())->map($transform)->values()->all(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, string>
     */
    protected function collectFilters(Request $request, array $keys): array
    {
        $filters = [];

        foreach ($keys as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return $filters;
    }

    /**
     * @return array{create: bool, edit: bool, delete: bool}
     */
    protected function settingsPermissions(
        \App\Models\User $user,
        string $createPermission,
        string $editPermission,
        ?string $deletePermission = null,
    ): array {
        $isAdmin = $user->hasRole(['super_admin', 'admin']);

        return [
            'create' => $isAdmin || $user->hasPermissionTo($createPermission),
            'edit' => $isAdmin || $user->hasPermissionTo($editPermission),
            'delete' => $isAdmin || $user->hasPermissionTo($deletePermission ?? $editPermission),
        ];
    }
}
