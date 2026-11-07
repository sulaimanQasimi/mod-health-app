<?php

namespace App\Models\Concerns;

use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasBillableInvoiceItems
{
    public function invoiceItems(): MorphMany
    {
        return $this->morphMany(InvoiceItem::class, 'billable');
    }
}
