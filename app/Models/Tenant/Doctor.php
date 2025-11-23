<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'user_id',
        'crm_number',
        'crm_state',
        'signature',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function specialties()
    {
        return $this->belongsToMany(MedicalSpecialty::class, 'doctor_specialty');
    }

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function businessHours()
    {
        return $this->hasMany(BusinessHour::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
