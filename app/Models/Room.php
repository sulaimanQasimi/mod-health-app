<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name','branch_id','floor_id','department_id'];

    public function beds()
    {
        return $this->hasMany(Bed::class)->where('is_occupied',false);
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

}
