<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTypeSection extends Model
{
    use HasFactory;

    protected $fillable = ['section', 'section_id'];

    public function relatedSection()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function labTypes()
    {
        return $this->hasMany(LabType::class, 'section_id', 'id');
    }
}
