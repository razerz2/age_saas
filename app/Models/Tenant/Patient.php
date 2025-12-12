<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'patients';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'full_name', 'cpf', 'birth_date', 'gender_id', 'email', 'phone', 'is_active'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function login()
    {
        return $this->hasOne(PatientLogin::class, 'patient_id', 'id');
    }

    /**
     * Relacionamento com gênero
     */
    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * Relacionamento com endereço
     */
    public function address()
    {
        return $this->hasOne(PatientAddress::class);
    }
}