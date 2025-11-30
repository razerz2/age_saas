<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleCalendarToken extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'google_calendar_tokens';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'doctor_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'access_token' => 'array',
        'expires_at' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com o médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Verifica se o token está expirado
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Verifica se o token está válido (não expirado)
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }
}

