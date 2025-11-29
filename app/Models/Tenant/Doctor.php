<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'doctors';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'user_id',
        'crm_number',
        'crm_state',
        'signature',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialties()
    {
        return $this->belongsToMany(MedicalSpecialty::class, 'doctor_specialty', 'doctor_id', 'specialty_id');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function appointmentTypes()
    {
        return $this->hasMany(AppointmentType::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    /**
     * Verifica se o médico possui appointments (atendimentos)
     * através dos calendários associados
     */
    public function hasAppointments(): bool
    {
        return $this->appointments()->exists();
    }

    /**
     * Relacionamento com appointments através de calendars
     */
    public function appointments()
    {
        return $this->hasManyThrough(Appointment::class, Calendar::class);
    }

    /**
     * Usuários que têm permissão para visualizar este médico
     */
    public function allowedUsers()
    {
        return $this->belongsToMany(User::class, 'user_doctor_permissions', 'doctor_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Relacionamento com permissões
     */
    public function permissions()
    {
        return $this->hasMany(UserDoctorPermission::class);
    }
}
