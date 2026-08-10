<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OphthalmologyRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'examiner_id',
        'branch_id',
        'registration_date',
        'ref_no',
        'status',
        'chief_complaint',
        'medical_history',
        'visual_examination',
        'refraction',
        'slit_lamp_examination',
        'fundus_examination',
        'diagnostic_tests',
        'diagnosis',
        'diagnosis_items',
        'treatment_plan',
        'follow_up_date',
        'notes',
        'fundus_image_path',
        'attachments',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'follow_up_date' => 'date',
        'medical_history' => 'array',
        'visual_examination' => 'array',
        'refraction' => 'array',
        'slit_lamp_examination' => 'array',
        'fundus_examination' => 'array',
        'diagnostic_tests' => 'array',
        'diagnosis_items' => 'array',
        'attachments' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $registration) {
            $registration->created_by = Auth::id();
            $registration->ref_no ??= 'OPH-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        });

        static::updating(function (self $registration) {
            $registration->updated_by = Auth::id();
        });

        static::deleting(function (self $registration) {
            $registration->deleted_by = Auth::id();
            $registration->saveQuietly();
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function examiner()
    {
        return $this->belongsTo(Doctor::class, 'examiner_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
