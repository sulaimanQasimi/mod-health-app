<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DepotRequestSourceResolver
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function resolve(array $data, ?User $user = null, ?int $hintedSourceDepotId = null): int
    {
        if (! empty($data['pharmacy_id'])) {
            if ($hintedSourceDepotId) {
                $this->assertValidPharmacySourceDepot($hintedSourceDepotId);

                return $hintedSourceDepotId;
            }

            return $this->resolveForPharmacy((int) $data['pharmacy_id']);
        }

        if (empty($data['requesting_depot_id'])) {
            throw ValidationException::withMessages([
                'requesting_depot_id' => 'Requesting depot is required.',
            ]);
        }

        $requestingDepotId = (int) $data['requesting_depot_id'];
        $requestingDepot = Depot::query()->find($requestingDepotId);

        if (! $requestingDepot || ! $requestingDepot->is_active) {
            throw ValidationException::withMessages([
                'requesting_depot_id' => 'Requesting depot must be active.',
            ]);
        }

        if ($hintedSourceDepotId && $hintedSourceDepotId !== $requestingDepotId) {
            $this->assertValidSourceDepot($hintedSourceDepotId, $requestingDepotId, $user);

            return $hintedSourceDepotId;
        }

        if ($requestingDepot->parent_depot_id) {
            $parent = Depot::query()->find($requestingDepot->parent_depot_id);

            if ($parent && $parent->is_active && $parent->id !== $requestingDepotId) {
                return (int) $parent->id;
            }
        }

        throw ValidationException::withMessages([
            'source_depot_id' => 'A valid source depot could not be determined for this request.',
        ]);
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function sourceOptionsFor(int $requestingDepotId, ?User $user = null): array
    {
        $requestingDepot = Depot::query()->find($requestingDepotId);

        $query = Depot::query()
            ->where('is_active', true)
            ->whereKeyNot($requestingDepotId)
            ->orderBy('name');

        if ($user && ! $user->hasRole(['super_admin', 'admin'])) {
            $allowedIds = $user->activeDepots->pluck('id')->map(fn ($id) => (int) $id)->all();

            if ($requestingDepot?->parent_depot_id) {
                $allowedIds[] = (int) $requestingDepot->parent_depot_id;
            }

            $allowedIds = array_values(array_unique(array_filter($allowedIds)));
            $query->whereIn('id', $allowedIds ?: [0]);
        }

        return $query->get(['id', 'name'])->map(fn (Depot $depot) => [
            'id' => (int) $depot->id,
            'name' => $depot->name,
        ])->values()->all();
    }

    public function defaultSourceDepotId(int $requestingDepotId, ?User $user = null): ?int
    {
        $options = $this->sourceOptionsFor($requestingDepotId, $user);

        if ($options === []) {
            return null;
        }

        $requestingDepot = Depot::query()->find($requestingDepotId);
        $parentId = $requestingDepot?->parent_depot_id ? (int) $requestingDepot->parent_depot_id : null;

        if ($parentId && collect($options)->contains(fn (array $option) => $option['id'] === $parentId)) {
            return $parentId;
        }

        return $options[0]['id'];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{id: int, name: string}|null
     */
    public function preview(array $data, ?User $user = null, ?int $hintedSourceDepotId = null): ?array
    {
        try {
            $id = $this->resolve($data, $user, $hintedSourceDepotId);
            $depot = Depot::query()->find($id);

            return $depot ? ['id' => (int) $depot->id, 'name' => $depot->name] : null;
        } catch (ValidationException) {
            return null;
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function sourceOptionsForPharmacyRequest(?User $user = null): array
    {
        return Depot::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Depot $depot) => [
                'id' => (int) $depot->id,
                'name' => $depot->name,
            ])
            ->values()
            ->all();
    }

    private function assertValidPharmacySourceDepot(int $sourceDepotId): void
    {
        $sourceDepot = Depot::query()->find($sourceDepotId);

        if (! $sourceDepot || ! $sourceDepot->is_active) {
            throw ValidationException::withMessages([
                'source_depot_id' => 'Source depot must be active.',
            ]);
        }
    }

    private function resolveForPharmacy(int $pharmacyId): int
    {
        $sourceDepotId = Depot::query()
            ->where('pharmacy_id', $pharmacyId)
            ->where('is_active', true)
            ->orderByDesc('is_base')
            ->value('id');

        if (! $sourceDepotId) {
            throw ValidationException::withMessages([
                'pharmacy_id' => 'No active source depot is linked to this pharmacy.',
            ]);
        }

        return (int) $sourceDepotId;
    }

    private function assertValidSourceDepot(int $sourceDepotId, int $requestingDepotId, ?User $user): void
    {
        if ($sourceDepotId === $requestingDepotId) {
            throw ValidationException::withMessages([
                'source_depot_id' => 'Source depot must differ from requesting depot.',
            ]);
        }

        $sourceDepot = Depot::query()->find($sourceDepotId);

        if (! $sourceDepot || ! $sourceDepot->is_active) {
            throw ValidationException::withMessages([
                'source_depot_id' => 'Source depot must be active.',
            ]);
        }

        if ($user && ! $user->hasRole(['super_admin', 'admin'])) {
            $requestingDepot = Depot::query()->find($requestingDepotId);
            $isParentSource = $requestingDepot
                && (int) $requestingDepot->parent_depot_id === $sourceDepotId;

            abort_unless($isParentSource || $user->hasDepotAccess($sourceDepotId), 403);
        }
    }
}
