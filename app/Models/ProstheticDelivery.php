<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticDelivery extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'delivered_at',
        'received_by_name',
        'device_serial_notes',
        'instructions_explained',
        'warranty_until',
        'follow_up_scheduled_at',
        'handover_signed',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'warranty_until' => 'date',
        'follow_up_scheduled_at' => 'date',
        'handover_signed' => 'boolean',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }
}
