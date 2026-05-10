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
        'meeting_provider',
        'meeting_status',
        'external_event_id',
        'external_meeting_id',
        'meeting_generated_at',
        'meeting_generation_error',
        'meeting_meta',
        'general_instructions',
        'patient_instructions',
        'sent_by_email_at',
        'sent_by_whatsapp_at',
    ];

    protected $casts = [
        'meeting_generated_at' => 'datetime',
        'meeting_meta' => 'array',
        'sent_by_email_at' => 'datetime',
        'sent_by_whatsapp_at' => 'datetime',
    ];

    public $timestamps = true;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
