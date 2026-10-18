<?php

namespace App\Http\Requests\Depot\Concerns;

trait ValidatesDepotRequestItems
{
    protected function prepareForValidation(): void
    {
        if ($this->input('destination_type') === 'pharmacy') {
            $this->merge([
                'requesting_depot_id' => null,
                'pharmacy_id' => $this->input('pharmacy_id') ?: null,
            ]);
        } elseif ($this->input('destination_type') === 'depot') {
            $this->merge([
                'pharmacy_id' => null,
                'requesting_depot_id' => $this->input('requesting_depot_id') ?: null,
            ]);
        }

        $this->prepareLegacyDepotRequestItems();
    }

    /**
     * @return array<string, mixed>
     */
    protected function depotRequestItemRules(): array
    {
        return [
            'destination_type' => ['nullable', 'in:depot,pharmacy'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['nullable', 'exists:medicines,id'],
            'items.*.tool_id' => ['nullable', 'exists:tools,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.batch_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareLegacyDepotRequestItems(): void
    {
        if (! $this->has('items') && ($this->filled('medicine_id') || $this->filled('tool_id'))) {
            $this->merge([
                'items' => [[
                    'medicine_id' => $this->input('medicine_id'),
                    'tool_id' => $this->input('tool_id'),
                    'unit_id' => $this->input('unit_id'),
                    'quantity' => $this->input('quantity'),
                    'batch_number' => $this->input('batch_number'),
                ]],
            ]);
        }
    }

    protected function validateDepotRequestItems($validator): void
    {
        $isPharmacyRequest = method_exists($this, 'isPharmacyDestinationRequest')
            && $this->isPharmacyDestinationRequest();

        $validator->after(function ($validator) use ($isPharmacyRequest) {
            foreach ($this->input('items', []) as $index => $item) {
                $hasMedicine = ! empty($item['medicine_id']);
                $hasTool = ! empty($item['tool_id']);

                if ($isPharmacyRequest) {
                    if (! $hasMedicine) {
                        $validator->errors()->add(
                            "items.{$index}.medicine_id",
                            'Each pharmacy request line must specify a medicine.'
                        );
                    }

                    if ($hasTool) {
                        $validator->errors()->add(
                            "items.{$index}.tool_id",
                            'Tools cannot be included in pharmacy requests.'
                        );
                    }

                    continue;
                }

                if ($hasMedicine === $hasTool) {
                    $validator->errors()->add(
                        "items.{$index}.medicine_id",
                        'Each line must specify either a medicine or a tool.'
                    );
                }
            }
        });
    }
}
