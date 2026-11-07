<?php

namespace App\Models\Concerns;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasInvoices
{
    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoiceable');
    }
}
