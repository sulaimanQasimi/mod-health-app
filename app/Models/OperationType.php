<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperationType extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['name','branch_id','department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
