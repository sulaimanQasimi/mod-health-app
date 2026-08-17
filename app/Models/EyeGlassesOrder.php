<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EyeGlassesOrder extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'requested',
        'processing',
        'paid',
        'delivered',
        'cancelled',
    ];

    public const FRAME_TYPES = ['full_rim', 'semi_rimless', 'rimless', 'sports'];

    public const LENS_TYPES = ['single_vision', 'bifocal', 'progressive', 'reading'];

    public const LENS_MATERIALS = ['plastic', 'polycarbonate', 'high_index', 'glass'];

    public const PAYMENT_METHODS = ['cash', 'card', 'free', 'other'];

    protected $fillable = [
        'appointment_id',
        'ophthalmology_registration_id',
        'examiner_id',
        'branch_id',
        'ref_no',
        'status',
        'request_date',
        'frame_type',
        'lens_type',
        'lens_material',
        'tint',
        'quantity',
        'prescription',
        'notes',
        'processed_at',
        'processed_by',
        'process_notes',
        'amount',
        'paid_amount',
        'paid_at',
        'paid_by',
        'payment_method',
        'payment_notes',
        'delivered_at',
        'delivered_by',
        'received_by',
        'delivery_notes',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'request_date' => 'date',
        'prescription' => 'array',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $order) {
            $order->created_by = Auth::id();
            $order->ref_no ??= 'EGL-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        });

        static::updating(function (self $order) {
            $order->updated_by = Auth::id();
        });

        static::deleting(function (self $order) {
            $order->deleted_by = Auth::id();
            $order->saveQuietly();
        });
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function ophthalmologyRegistration(): BelongsTo
    {
        return $this->belongsTo(OphthalmologyRegistration::class);
    }

    public function examiner(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'examiner_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function deliveredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['delivered', 'cancelled'], true)
            || (bool) $this->appointment?->is_completed;
    }

    public static function prescriptionFromRefraction(?array $refraction): array
    {
        $eye = function (string $side) use ($refraction): array {
            return [
                'sphere' => data_get($refraction, "{$side}.sphere"),
                'cylinder' => data_get($refraction, "{$side}.cylinder"),
                'axis' => data_get($refraction, "{$side}.axis"),
                'add' => data_get($refraction, "{$side}.add"),
                'prism_horizontal' => data_get($refraction, "{$side}.prism_horizontal"),
                'prism_vertical' => data_get($refraction, "{$side}.prism_vertical"),
            ];
        };

        return [
            'od' => $eye('od'),
            'os' => $eye('os'),
            'ipd' => data_get($refraction, 'ipd'),
        ];
    }
}
