<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentWaitlistEntry extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'WAITING';
    public const STATUS_OFFERED = 'OFFERED';
    public const STATUS_ACCEPTED = 'ACCEPTED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_CANCELED = 'CANCELED';
    public const STATUS_SKIPPED = 'SKIPPED';

    protected $connection = 'tenant';
    protected $table = 'appointment_waitlist_entries';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'tenant_id',
        'doctor_id',
        'patient_id',
        'starts_at',
        'ends_at',
        'status',
        'offer_token',
        'offered_at',
        'offer_expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'offered_at' => 'datetime',
        'offer_expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function scopeWaiting(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WAITING);
    }

    public function scopeOffered(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OFFERED);
    }

    public function isOfferValid(): bool
    {
        return $this->status === self::STATUS_OFFERED
            && $this->offer_expires_at !== null
            && $this->offer_expires_at->gt(now());
    }
}

