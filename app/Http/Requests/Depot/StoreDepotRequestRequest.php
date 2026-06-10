<?php

namespace App\Http\Requests\Depot;

use App\Http\Requests\Depot\Concerns\ValidatesDepotRequestItems;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequestRequest extends FormRequest
{
    use ValidatesDepotRequestItems;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requesting_depot_id' => ['required', 'exists:depots,id', 'different:source_depot_id'],
            'source_depot_id' => ['required', 'exists:depots,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...$this->depotRequestItemRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateDepotRequestItems($validator);
    }
}
