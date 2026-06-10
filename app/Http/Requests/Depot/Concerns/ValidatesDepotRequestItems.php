<?php

namespace App\Http\Requests\Depot\Concerns;

trait ValidatesDepotRequestItems
{
    protected function prepareForValidation(): void
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

    /**
     * @return array<string, mixed>
     */
    protected function depotRequestItemRules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['nullable', 'exists:medicines,id'],
            'items.*.tool_id' => ['nullable', 'exists:tools,id'],
            'items.*.unit_id' => ['nullable', 'exists:units,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.batch_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function validateDepotRequestItems($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                $hasMedicine = ! empty($item['medicine_id']);
                $hasTool = ! empty($item['tool_id']);

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
