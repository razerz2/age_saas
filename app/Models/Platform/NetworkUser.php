<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NetworkUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'pgsql';
    protected $table = 'network_users';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'clinic_network_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (!$user->id) {
                $user->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Hash da senha ao definir
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value) && !str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$') && !str_starts_with($value, '$2b$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Relacionamento com a rede
     */
    public function network()
    {
        return $this->belongsTo(ClinicNetwork::class, 'clinic_network_id');
    }

    /**
     * Verifica se é admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se pode ver financeiro
     */
    public function canViewFinance(): bool
    {
        return in_array($this->role, ['admin', 'finance']);
    }
}

