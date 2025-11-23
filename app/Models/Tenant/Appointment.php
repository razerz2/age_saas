<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id','calendar_id','appointment_type','patient_id',
        'specialty_id','starts_at','ends_at',
        'status','notes'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function type()
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function specialty()
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    public function syncState()
    {
        return $this->hasOne(CalendarSyncState::class);
    }
}