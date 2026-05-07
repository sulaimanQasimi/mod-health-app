<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'address', 'is_active', 'is_base', 'parent_depot_id', 'created_by', 'updated_by', 'deleted_by'];

    public function parentDepot()
    {
        return $this->belongsTo(Depot::class, 'parent_depot_id');
    }
    public function transactions()
    {
        return $this->hasMany(DepotTransaction::class, 'depot_id');
    }
    public function all_incomes()
    {
        return $this->hasMany(DepotTransaction::class, 'depot_id')->where('transaction_type', 'in');
    }
    public function all_outcomes()
    {
        return $this->hasMany(DepotTransaction::class, 'depot_id')->where('transaction_type', 'out');
    }
    public function all_transfers()
    {
        return $this->hasMany(DepotTransaction::class, 'depot_id')->where('transaction_type', 'transfer');
    }
    public function childrenDepots()
    {
        return $this->hasMany(Depot::class, 'parent_depot_id');
    }


    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
}
