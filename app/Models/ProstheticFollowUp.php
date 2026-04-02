<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticFollowUp extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'follow_up_type',
        'scheduled_at',
        'completed_at',
        'outcome',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }
}
