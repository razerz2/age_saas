<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiTenantToken extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'api_tenant_tokens';

    protected $fillable = [
        'tenant_id',
        'name',
        'token_hash',
        'token_encrypted',
        'active',
        'expires_at',
        'permissions',
        'created_by',
        'last_used_at',
        'last_ip',
    ];

    protected $casts = [
        'permissions' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Descriptografa e retorna o token
     */
    public function getDecryptedToken(): ?string
    {
        if (!$this->token_encrypted) {
            return null;
        }

        try {
            return decrypt($this->token_encrypted);
        } catch (\Exception $e) {
            \Log::error('Erro ao descriptografar token', [
                'token_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
