<?php

namespace App\Rules;

use App\Models\Disease;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NephrologyDisease implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! Disease::query()->where('id', $value)->exists()) {
            $fail(localize('global.invalid_nephrology_disease'));
        }
    }
}
