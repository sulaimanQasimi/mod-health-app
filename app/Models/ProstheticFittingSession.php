<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticFittingSession extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'prosthetic_work_order_id',
        'session_date',
        'outcome',
        'comfort_score',
        'issues_identified',
        'modifications_required',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(ProstheticWorkOrder::class, 'prosthetic_work_order_id');
    }
}
