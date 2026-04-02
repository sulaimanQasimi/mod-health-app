<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProstheticAttachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'category',
        'path',
        'original_name',
        'created_by',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
