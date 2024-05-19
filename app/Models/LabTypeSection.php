<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTypeSection extends Model
{
    use HasFactory;

    protected $fillable = ['section'];

    public function labTypes()
    {
        return $this->hasMany(LabType::class, 'section_id', 'id');
    }
}
