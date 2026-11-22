<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesMedicineLookups;
use App\Models\MedicineType;

class MedicineTypeController extends Controller
{
    use ManagesMedicineLookups;

    protected function lookupModelClass(): string
    {
        return MedicineType::class;
    }

    protected function lookupLabelField(): string
    {
        return 'type';
    }

    protected function lookupRoutePrefix(): string
    {
        return 'medicine-types';
    }

    protected function lookupPageTitle(): string
    {
        return 'Medicine types';
    }

    protected function lookupNavKey(): string
    {
        return 'types';
    }

    protected function lookupExtraFields(): array
    {
        return [];
    }
}
