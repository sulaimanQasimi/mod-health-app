<?php

namespace App\Http\Requests\Depot\Concerns;

trait ValidatesDepotRequestDestination
{
    /**
     * @return array<string, mixed>
     */
    protected function depotRequestDestinationRules(): array
    {
        return [
            'requesting_depot_id' => ['nullable', 'required_without:pharmacy_id', 'prohibited_if:pharmacy_id,*', 'exists:depots,id', 'different:source_depot_id'],
            'pharmacy_id' => ['nullable', 'required_without:requesting_depot_id', 'prohibited_if:requesting_depot_id,*', 'exists:pharmacies,id'],
            'source_depot_id' => ['required', 'exists:depots,id'],
        ];
    }

    protected function isPharmacyDestinationRequest(): bool
    {
        return $this->filled('pharmacy_id');
    }
}
