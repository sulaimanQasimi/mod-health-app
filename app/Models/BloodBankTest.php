<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BloodBankTest extends Model
{
    public const RESULT_VALUES = ['positive', 'negative'];

    protected $fillable = [
        'blood_bank_id',
        'test_name',
        'result',
        'notes',
        'filled_test_by',
    ];

    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    public function filledTestBy()
    {
        return $this->belongsTo(User::class, 'filled_test_by');
    }

    public function isFilled(): bool
    {
        return $this->result !== null;
    }
}
