<?php

namespace App\Http\Requests\Depot;

use App\Http\Requests\Depot\Concerns\ValidatesDepotPharmacyItems;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepotToPharmacyRequest extends FormRequest
{
    use ValidatesDepotPharmacyItems;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_depot_id' => ['required', 'exists:depots,id'],
            'pharmacy_id' => ['required', 'exists:pharmacies,id'],
            'transaction_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...$this->depotPharmacyItemRules(),
        ];
    }
}
