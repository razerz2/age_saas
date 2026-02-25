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
        'confirmation_expires_at', 'confirmed_at', 'canceled_at', 'expired_at', 'cancellation_reason', 'confirmation_token',
        'status', 'notes', 'recurring_appointment_id', 'google_event_id', 'apple_event_id', 'appointment_mode', 'origin'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'confirmation_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'canceled_at' => 'datetime',
        'expired_at' => 'datetime',
        'appointment_mode' => 'string',
    ];

    public $timestamps = true;

    /**
     * Mutator para garantir que doctor_id seja sempre sincronizado com calendar_id
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($appointment) {
            // Sempre garantir que doctor_id está sincronizado com calendar_id
            if ($appointment->calendar_id) {
                // Se calendar_id mudou ou doctor_id não está definido, buscar do calendar
                if ($appointment->isDirty('calendar_id') || !$appointment->doctor_id) {
                    $calendar = Calendar::find($appointment->calendar_id);
                    if ($calendar && $calendar->doctor_id) {
                        $appointment->doctor_id = $calendar->doctor_id;
                    }
                }
            }
            
            // Se doctor_id foi definido diretamente mas calendar_id não está, validar consistência
            if ($appointment->doctor_id && $appointment->calendar_id) {
                $calendar = Calendar::find($appointment->calendar_id);
                if ($calendar && $calendar->doctor_id !== $appointment->doctor_id) {
                    // Se houver inconsistência, sincronizar doctor_id com o calendar
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

    public function financialCharge()
    {
        return $this->hasOne(FinancialCharge::class, 'appointment_id');
    }

    /**
     * Retorna o status traduzido para português
     */
    public function getStatusTranslatedAttribute(): string
    {
        $translations = [
            'scheduled' => 'Agendado',
            'rescheduled' => 'Reagendado',
            'pending_confirmation' => 'Aguardando confirmação',
            'canceled' => 'Cancelado',
            'expired' => 'Expirado',
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
            'rescheduled',
            'pending_confirmation',
            'confirmed',
            'expired',
            'arrived',
            'in_service',
            'completed',
            'canceled',
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

    /**
     * Indica se o agendamento ocupa slot de agenda.
     */
    public function occupiesSlot(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled', 'pending_confirmation'], true);
    }

    /**
     * Indica se o agendamento estÃ¡ em hold (aguardando confirmaÃ§Ã£o).
     */
    public function isHold(): bool
    {
        return $this->status === 'pending_confirmation';
    }

    /**
     * Indica se o agendamento jÃ¡ foi confirmado (ocupa slot final).
     */
    public function isConfirmed(): bool
    {
        return in_array($this->status, ['scheduled', 'rescheduled'], true);
    }
}
