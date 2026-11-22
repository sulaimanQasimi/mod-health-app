<?php

namespace App\Http\Requests\DepotTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepotTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_depot_id' => ['required', 'integer', 'exists:depots,id'],
            'pharmacy_id' => ['nullable', 'integer', 'exists:pharmacies,id', 'required_without:destination_depot_id'],
            'destination_depot_id' => ['nullable', 'integer', 'exists:depots,id', 'different:source_depot_id', 'required_without:pharmacy_id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'items.*.batch_number' => ['nullable', 'string', 'max:100'],
            'items.*.packs' => ['nullable', 'integer', 'min:0'],
            'items.*.units' => ['nullable', 'integer', 'min:0'],
            'items.*.units_per_pack' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
