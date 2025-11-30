<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringAppointment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'recurring_appointments';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'appointment_type_id',
        'start_date',
        'end_type',
        'total_sessions',
        'end_date',
        'active',
        'google_recurring_event_ids',
        'appointment_mode',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'total_sessions' => 'integer',
        'google_recurring_event_ids' => 'array',
        'appointment_mode' => 'string',
    ];

    public $timestamps = true;

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointmentType()
    {
        return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
    }

    public function rules()
    {
        return $this->hasMany(RecurringAppointmentRule::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'recurring_appointment_id');
    }

    /**
     * Verifica se a recorrência ainda está ativa e dentro dos limites
     */
    public function isActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $today = Carbon::today();

        // Verifica se já passou da data inicial
        if ($this->start_date->gt($today)) {
            return false;
        }

        // Verifica limites
        if ($this->end_type === 'date' && $this->end_date && $this->end_date->lt($today)) {
            return false;
        }

        if ($this->end_type === 'total_sessions' && $this->total_sessions) {
            $generatedCount = $this->appointments()->whereIn('status', ['scheduled', 'rescheduled', 'attended'])->count();
            if ($generatedCount >= $this->total_sessions) {
                return false;
            }
        }

        return true;
    }

    /**
     * Conta quantas sessões já foram geradas
     */
    public function getGeneratedSessionsCount(): int
    {
        return $this->appointments()->whereIn('status', ['scheduled', 'rescheduled', 'attended'])->count();
    }

    /**
     * Armazena o ID do evento recorrente do Google Calendar para uma regra específica
     */
    public function setGoogleRecurringEventId(string $ruleId, string $googleEventId): void
    {
        $eventIds = $this->google_recurring_event_ids ?? [];
        $eventIds[$ruleId] = $googleEventId;
        $this->google_recurring_event_ids = $eventIds;
        $this->save();
    }

    /**
     * Obtém o ID do evento recorrente do Google Calendar para uma regra específica
     */
    public function getGoogleRecurringEventId(string $ruleId): ?string
    {
        $eventIds = $this->google_recurring_event_ids ?? [];
        return $eventIds[$ruleId] ?? null;
    }

    /**
     * Obtém todos os IDs dos eventos recorrentes do Google Calendar
     */
    public function getGoogleRecurringEventIds(): array
    {
        return $this->google_recurring_event_ids ?? [];
    }
}

