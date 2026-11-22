<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMedicineLookups;
use App\Models\MedicineCountry;

class MedicineCountryController extends Controller
{
    use ManagesMedicineLookups;

    protected function lookupModelClass(): string
    {
        return MedicineCountry::class;
    }

    protected function lookupLabelField(): string
    {
        return 'name';
    }

    protected function lookupRoutePrefix(): string
    {
        return 'medicine-countries';
    }

    protected function lookupPageTitle(): string
    {
        return 'Medicine countries';
    }

    protected function lookupNavKey(): string
    {
        return 'countries';
    }

    protected function lookupExtraFields(): array
    {
        return [];
    }
}
