<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticAssessment extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'fit_outcome',
        'history_present_condition',
        'surgical_history',
        'comorbidities',
        'medications',
        'allergies',
        'skin_stump_notes',
        'functional_goals',
        'psychosocial_notes',
        'extra_clinical',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'extra_clinical' => 'array',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }
}
