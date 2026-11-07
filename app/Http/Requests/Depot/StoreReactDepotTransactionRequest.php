<?php

namespace App\Http\Requests\Depot;

use Illuminate\Foundation\Http\FormRequest;

class StoreReactDepotTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'depot_id' => ['prohibited'],
            'medicine_id' => ['nullable', 'required_without:tool_id', 'prohibits:tool_id', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'required_without:medicine_id', 'prohibits:medicine_id', 'exists:tools,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'batch_number' => ['nullable', 'string', 'max:255', 'not_regex:/[;,|]/'],
            'type' => ['required', 'in:stock_in,stock_out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_price' => ['nullable', 'required_if:type,stock_in', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'required_if:type,stock_in', 'numeric', 'min:0'],
            'transaction_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
