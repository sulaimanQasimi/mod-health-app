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
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.packs' => ['nullable', 'integer', 'min:0'],
            'items.*.units' => ['nullable', 'integer', 'min:0'],
            'items.*.units_per_pack' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_sale_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.pack_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Batch codes are always generated on the server.
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (! is_array($items)) {
            return;
        }

        $this->merge([
            'items' => array_map(static function ($row) {
                if (! is_array($row)) {
                    return $row;
                }
                unset($row['batch_number']);

                return $row;
            }, $items),
        ]);
    }
}
