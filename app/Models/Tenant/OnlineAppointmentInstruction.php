<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineAppointmentInstruction extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'online_appointment_instructions';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'appointment_id',
        'meeting_link',
        'meeting_app',
        'general_instructions',
        'patient_instructions',
        'sent_by_email_at',
        'sent_by_whatsapp_at',
    ];

    protected $casts = [
        'sent_by_email_at' => 'datetime',
        'sent_by_whatsapp_at' => 'datetime',
    ];

    public $timestamps = true;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}

