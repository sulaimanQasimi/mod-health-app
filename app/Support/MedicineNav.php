<?php

namespace App\Support;

final class MedicineNav
{
    /**
     * @return array<string, string>
     */
    public static function urls(): array
    {
        return [
            'medicines' => route('medicines.index'),
            'types' => route('medicine-types.index'),
            'formulas' => route('medicine-formulas.index'),
            'countries' => route('medicine-countries.index'),
            'gramMls' => route('medicine-gram-mls.index'),
        ];
    }
}
