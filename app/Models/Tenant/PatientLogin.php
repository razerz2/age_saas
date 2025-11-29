<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientLogin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'tenant';
    protected $table = 'patient_logins';

    protected $fillable = [
        'patient_id',
        'email',
        'password',
        'last_login_at',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    /**
     * Hash da senha automaticamente
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value) && strlen($value) < 60) {
            $this->attributes['password'] = bcrypt($value);
        }
    }
}
