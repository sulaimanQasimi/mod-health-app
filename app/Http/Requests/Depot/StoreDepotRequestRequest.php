<?php

namespace App\Http\Requests\Depot;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requesting_depot_id' => ['required', 'exists:depots,id', 'different:source_depot_id'],
            'source_depot_id' => ['required', 'exists:depots,id'],
            'medicine_id' => ['nullable', 'required_without:tool_id', 'prohibits:tool_id', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'required_without:medicine_id', 'prohibits:medicine_id', 'exists:tools,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
