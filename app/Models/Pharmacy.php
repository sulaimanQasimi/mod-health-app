<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Pharmacy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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

    // Many-to-many relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class, 'pharmacy_users', 'pharmacy_id', 'user_id')
                    ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
                    ->withTimestamps();
    }

    // Get active users only
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }

    // Get pharmacy managers/admins
    public function managers()
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    // Get pharmacy staff
    public function staff()
    {
        return $this->users()->wherePivot('role', 'staff');
    }

    // Check if user has access to this pharmacy
    public function hasUser($userId)
    {
        return $this->users()->where('user_id', $userId)->wherePivot('is_active', true)->exists();
    }

    // Add user to pharmacy
    public function addUser($userId, $role = 'staff', $permissions = null)
    {
        return $this->users()->attach($userId, [
            'role' => $role,
            'permissions' => $permissions,
            'is_active' => true,
            'joined_at' => now()
        ]);
    }

    // Remove user from pharmacy
    public function removeUser($userId)
    {
        return $this->users()->updateExistingPivot($userId, ['is_active' => false]);
    }

    // Update user role in pharmacy
    public function updateUserRole($userId, $role, $permissions = null)
    {
        return $this->users()->updateExistingPivot($userId, [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function outcomes()
    {
        return $this->hasMany(Outcome::class);
    }

    // Scope to get pharmacies accessible by a specific user
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('is_active', true);
        });
    }

    // Scope to get pharmacies where user has specific role
    public function scopeWhereUserRole($query, $userId, $role)
    {
        return $query->whereHas('users', function ($q) use ($userId, $role) {
            $q->where('user_id', $userId)->where('role', $role)->where('is_active', true);
        });
    }

    // Get pharmacy statistics for dashboard
    public function getStatistics()
    {
        return [
            'total_users' => $this->activeUsers()->count(),
            'managers_count' => $this->managers()->count(),
            'staff_count' => $this->staff()->count(),
            'total_incomes' => $this->incomes()->count(),
            'total_outcomes' => $this->outcomes()->count(),
            'recent_activities' => $this->getRecentActivities()
        ];
    }

    // Get recent activities (incomes and outcomes)
    public function getRecentActivities($limit = 10)
    {
        $incomes = $this->incomes()->latest()->limit($limit)->get();
        $outcomes = $this->outcomes()->latest()->limit($limit)->get();
        
        return $incomes->concat($outcomes)->sortByDesc('created_at')->take($limit);
    }

    // Check if user can perform specific action
    public function canUserPerform($userId, $action)
    {
        $user = $this->users()->where('user_id', $userId)->where('is_active', true)->first();
        
        if (!$user) {
            return false;
        }

        $role = $user->pivot->role;
        $permissions = $user->pivot->permissions ? json_decode($user->pivot->permissions, true) : [];

        // Manager can do everything
        if ($role === 'manager') {
            return true;
        }

        // Check specific permissions
        return in_array($action, $permissions);
    }

    // Get user's permissions in this pharmacy
    public function getUserPermissions($userId)
    {
        $user = $this->users()->where('user_id', $userId)->where('is_active', true)->first();
        
        if (!$user) {
            return [];
        }

        $permissions = $user->pivot->permissions ? json_decode($user->pivot->permissions, true) : [];
        
        // Add role-based permissions
        $rolePermissions = $this->getRolePermissions($user->pivot->role);
        
        return array_merge($permissions, $rolePermissions);
    }

    // Get default permissions for a role
    private function getRolePermissions($role)
    {
        $permissions = [
            'manager' => ['view', 'create', 'update', 'delete', 'manage_users', 'view_reports'],
            'staff' => ['view', 'create', 'update'],
            'viewer' => ['view']
        ];

        return $permissions[$role] ?? [];
    }
}
