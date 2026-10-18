<?php

namespace App\Http\Requests\Depot;

use App\Http\Requests\Depot\Concerns\ValidatesDepotRequestDestination;
use App\Http\Requests\Depot\Concerns\ValidatesDepotRequestItems;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepotRequestRequest extends FormRequest
{
    use ValidatesDepotRequestDestination;
    use ValidatesDepotRequestItems;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->depotRequestDestinationRules(),
            'notes' => ['nullable', 'string', 'max:2000'],
            ...$this->depotRequestItemRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateDepotRequestItems($validator);
    }
}
