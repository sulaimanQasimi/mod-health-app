<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','branch_id','floor_id','department_id'];

    public function beds()
    {
        return $this->hasMany(Bed::class)->where('is_occupied',false);
    }

    /**
     * All beds in this room (for room management view), regardless of occupancy.
     */
    public function allBeds()
    {
        return $this->hasMany(Bed::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Rooms visible in hospitalization room-management for the authenticated user.
     *
     * @param  Builder<Room>  $query
     * @return Builder<Room>
     */
    public function scopeManageableForHospitalization($query, ?User $user = null)
    {
        $user = $user ?? Auth::user();
        if (! $user || ! $user->can('manageAny', static::class)) {
            return $query->whereRaw('0 = 1');
        }

        $query->where('branch_id', $user->branch_id);

        if (! Hospitalization::userBypassesDepartmentScope($user)) {
            if ($user->department_id === null) {
                return $query->whereRaw('0 = 1');
            }

            $query->where('department_id', $user->department_id);
        }

        return $query;
    }
}
