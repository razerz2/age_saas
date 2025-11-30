<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'forms';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'name', 'description', 'specialty_id', 'doctor_id', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function sections()
    {
        return $this->hasMany(FormSection::class);
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }

    public function specialty()
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Retorna o formulário ativo para um agendamento
     * Prioridade: 1) Formulário do médico, 2) Formulário da especialidade
     */
    public static function getFormForAppointment(Appointment $appointment)
    {
        // Carrega relacionamentos necessários
        $appointment->load(['calendar.doctor', 'specialty']);

        // 1. Procurar formulário ativo do médico
        if ($appointment->calendar && $appointment->calendar->doctor) {
            $doctorForm = self::where('is_active', true)
                ->where('doctor_id', $appointment->calendar->doctor->id)
                ->first();

            if ($doctorForm) {
                return $doctorForm;
            }
        }

        // 2. Procurar formulário ativo da especialidade
        if ($appointment->specialty_id) {
            $specialtyForm = self::where('is_active', true)
                ->where('specialty_id', $appointment->specialty_id)
                ->first();

            if ($specialtyForm) {
                return $specialtyForm;
            }
        }

        return null;
    }
}
