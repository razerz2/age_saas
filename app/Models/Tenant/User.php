<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'tenant';
    protected $table = 'users';

    protected $fillable = [
        'tenant_id',  // <-- ADICIONADO AQUI
        'name',
        'name_full',
        'telefone',
        'email',
        'password',
        'is_doctor',
        'status',
        'modules',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_doctor' => 'boolean',
        'modules' => 'array',
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        if (!empty($value) && strlen($value) < 60) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_full ?? $this->name;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Relação correta com o Tenant da plataforma
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Platform\Tenant::class, 'tenant_id', 'id');
    }
}
