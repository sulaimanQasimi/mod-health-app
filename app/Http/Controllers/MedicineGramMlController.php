<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMedicineLookups;
use App\Models\MedicineGramMl;

class MedicineGramMlController extends Controller
{
    use ManagesMedicineLookups;

    protected function lookupModelClass(): string
    {
        return MedicineGramMl::class;
    }

    protected function lookupLabelField(): string
    {
        return 'name';
    }

    protected function lookupRoutePrefix(): string
    {
        return 'medicine-gram-mls';
    }

    protected function lookupPageTitle(): string
    {
        return 'global.medicine_gram_ml';
    }

    protected function lookupNavKey(): string
    {
        return 'gramMls';
    }

    protected function lookupExtraFields(): array
    {
        return [
            'gram' => ['nullable', 'numeric', 'min:0'],
            'ml' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
