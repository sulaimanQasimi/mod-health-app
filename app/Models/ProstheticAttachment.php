<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->created_by = $model->created_by ?? Auth::id();
        });

        self::deleting(function ($model) {
            if ($model->path && Storage::disk('public')->exists($model->path)) {
                Storage::disk('public')->delete($model->path);
            }
        });
    }

    /**
     * Get the full URL for the attachment file.
     */
    public function getFileUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return Storage::disk('public')->url($this->path);
    }

    /**
     * Delete the physical file.
     */
    public function deleteFile(): void
    {
        if ($this->path && Storage::disk('public')->exists($this->path)) {
            Storage::disk('public')->delete($this->path);
        }
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
