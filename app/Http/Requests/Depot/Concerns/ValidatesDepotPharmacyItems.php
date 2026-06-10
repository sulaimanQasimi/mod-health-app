<?php

namespace App\Http\Requests\Depot\Concerns;

trait ValidatesDepotPharmacyItems
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('items') && $this->filled('medicine_id')) {
            $this->merge([
                'items' => [[
                    'medicine_id' => $this->input('medicine_id'),
                    'quantity' => $this->input('quantity'),
                    'unit_id' => $this->input('unit_id'),
                    'batch_number' => $this->input('batch_number'),
                    'expiry_date' => $this->input('expiry_date'),
                ]],
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function depotPharmacyItemRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.batch_number' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ];
    }
}
