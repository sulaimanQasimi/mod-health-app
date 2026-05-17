<?php

namespace App\Http\Requests\Depot;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepotToDepotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_depot_id' => ['required', 'exists:depots,id', 'different:to_depot_id'],
            'to_depot_id' => ['required', 'exists:depots,id'],
            'medicine_id' => ['required', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'exists:tools,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
