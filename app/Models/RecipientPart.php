<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipientPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_id',
        'name',
        'code',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }

    public function displayName(): string
    {
        return $this->name.' ('.$this->code.')';
    }
}
