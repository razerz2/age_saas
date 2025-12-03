<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'appointments';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'calendar_id', 'doctor_id', 'appointment_type', 'patient_id',
        'specialty_id', 'starts_at', 'ends_at',
        'status', 'notes', 'recurring_appointment_id', 'google_event_id', 'apple_event_id', 'appointment_mode'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'appointment_mode' => 'string',
    ];

    public $timestamps = true;

    /**
     * Mutator para garantir que doctor_id seja definido automaticamente quando calendar_id é definido
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($appointment) {
            // Se calendar_id está sendo definido e doctor_id não está, buscar do calendar
            if ($appointment->calendar_id && !$appointment->doctor_id) {
                $calendar = Calendar::find($appointment->calendar_id);
                if ($calendar && $calendar->doctor_id) {
                    $appointment->doctor_id = $calendar->doctor_id;
                }
            }
            // Se calendar_id mudou, atualizar doctor_id também
            if ($appointment->isDirty('calendar_id') && $appointment->calendar_id) {
                $calendar = Calendar::find($appointment->calendar_id);
                if ($calendar && $calendar->doctor_id) {
                    $appointment->doctor_id = $calendar->doctor_id;
                }
            }
        });
    }

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
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

    public function recurringAppointment()
    {
        return $this->belongsTo(RecurringAppointment::class);
    }

    public function syncState()
    {
        return $this->hasOne(CalendarSyncState::class);
    }

    public function onlineInstructions()
    {
        return $this->hasOne(OnlineAppointmentInstruction::class);
    }

    /**
     * Retorna o status traduzido para português
     */
    public function getStatusTranslatedAttribute(): string
    {
        $translations = [
            'scheduled' => 'Agendado',
            'rescheduled' => 'Reagendado',
            'canceled' => 'Cancelado',
            'attended' => 'Atendido',
            'no_show' => 'Não Compareceu',
            'confirmed' => 'Confirmado',
            'arrived' => 'Chegou',
            'in_service' => 'Em Atendimento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
        ];

        return $translations[$this->status] ?? ($this->status ?? 'N/A');
    }

    /**
     * Retorna lista de status disponíveis
     */
    public static function statuses(): array
    {
        return [
            'scheduled',
            'confirmed',
            'arrived',
            'in_service',
            'completed',
            'cancelled',
        ];
    }

    /**
     * Scope para filtrar agendamentos de um dia específico
     */
    public function scopeForDay($query, $date)
    {
        return $query->whereDate('starts_at', $date);
    }
}