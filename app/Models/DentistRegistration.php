<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DentistRegistration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'appointment_id',
        'dentist_id',
        'registration_date',
        'ref_no',
        'status',
        'notes',
        'branch_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    public static function boot()
    {
        parent::boot();
        
        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $user->id ?? 0;
            
            // Generate reference number if not provided
            if (empty($model->ref_no)) {
                $ref = DB::table('ref_numbers')->lockForUpdate()->first();
                $newRefNo = $ref->last_ref_no + 1;
                DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);
                $model->ref_no = $newRefNo;
            }
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

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function dentist()
    {
        return $this->belongsTo(Doctor::class, 'dentist_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function examinations()
    {
        return $this->hasMany(DentalExamination::class);
    }

    public function treatments()
    {
        return $this->hasMany(DentalTreatment::class);
    }

    public function xrays()
    {
        return $this->hasMany(DentalXray::class);
    }

    public function dentalNotes()
    {
        return $this->hasMany(DentalNote::class);
    }

    public function dentalCharts()
    {
        return $this->hasMany(DentalChart::class);
    }

    public function patient()
    {
        return $this->hasOneThrough(Patient::class, Appointment::class, 'id', 'id', 'appointment_id', 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function markCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
