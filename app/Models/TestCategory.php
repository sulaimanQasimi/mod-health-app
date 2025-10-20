<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Test Category Model
 * 
 * Represents categories for laboratory tests (e.g., Hematology, Biochemistry)
 */
class TestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the lab tests for this category
     */
    public function labTests()
    {
        return $this->hasMany(LabTest::class, 'category_id');
    }
}
