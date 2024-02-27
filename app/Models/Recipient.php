<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipient extends Model
{

    use SoftDeletes;

    protected $fillable=['name_dr','name_en','name_pa','type','sector_id','category','parent_id','created_by', 'updated_by', 'deleted_by'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;
        });

        self::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? 0;
        });

        self::deleting(function ($model) {
            $user = Auth::user();
            $model->deleted_by = $user->id ?? 0;
            $model->save();
        });
    }

    public function order_copies() {
        return $this->hasMany('App\Models\OrderCopy');
    }


    public function parent() {
    return $this->BelongsTo('App\Models\Recipient', 'parent_id', 'id');
    }

    public function children() {
    return $this->hasMany('App\Models\Recipient', 'parent_id', 'id');
    }

    public function recipientType()
    {
        return $this->belongsTo(RecipientType::class, 'category');
    }


    public function sector() {
        return $this->belongsTo('App\Models\Sector');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, function ($query) {
            $query->whereJsonContains('recipients', (string) $this->getKey());
        }, 'recipients');
    }

    public function scopeUserBasedRecipient($query, $user)
    {
        if ($user->can('executive')) {
            return $query;
        } else if ($user->can('specialist') || $user->can('directorate')) {
            return $query->WhereIn('id', getUserRecipientsIds());
        }
         else {
            return $query->where('sector_id', $user->sector_id);
        }
    }


}
