<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'depot_id' => ['required', 'integer', 'exists:depots,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'exists:medicines,id'],
            'items.*.batch_number' => ['required', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.packs' => ['nullable', 'integer', 'min:0'],
            'items.*.units' => ['nullable', 'integer', 'min:0'],
            'items.*.units_per_pack' => ['nullable', 'integer', 'min:1'],
            'items.*.purchase_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_sale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.pack_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
