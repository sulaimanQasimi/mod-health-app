<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodUnitTest extends Model
{

    /**
     * Explicit table name (basename pluralization can be ambiguous for compound names).
     */
    protected $table = 'blood_unit_tests';

    public const RESULT_VALUES = ['pending', 'negative', 'positive', 'inconclusive'];
    public const OVERALL_STATUSES = ['pending', 'passed', 'failed'];

    protected $fillable = [
        'blood_unit_id',
        'abo_result',
        'rh_result',
        'dct_result',
        'ict_result',
        'hbs_result',
        'hcv_result',
        'hiv_result',
        'vdrl_result',
        'overall_status',
        'tested_at',
        'tested_by',
        'remarks',
    ];

    protected $casts = [
        'tested_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(BloodUnit::class, 'blood_unit_id');
    }

    public function testedBy()
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}

