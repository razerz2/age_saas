<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'method',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o código está válido (não usado e não expirado)
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Marca o código como usado
     */
    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    /**
     * Limpa códigos expirados ou usados
     */
    public static function cleanup(): int
    {
        return static::where('expires_at', '<', now())
            ->orWhere('used', true)
            ->delete();
    }
}
