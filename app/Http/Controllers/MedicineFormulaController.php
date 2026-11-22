<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMedicineLookups;
use App\Models\MedicineFormula;

class MedicineFormulaController extends Controller
{
    use ManagesMedicineLookups;

    protected function lookupModelClass(): string
    {
        return MedicineFormula::class;
    }

    protected function lookupLabelField(): string
    {
        return 'name';
    }

    protected function lookupRoutePrefix(): string
    {
        return 'medicine-formulas';
    }

    protected function lookupPageTitle(): string
    {
        return 'Medicine formulas';
    }

    protected function lookupNavKey(): string
    {
        return 'formulas';
    }

    protected function lookupExtraFields(): array
    {
        return [];
    }
}
