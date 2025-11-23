<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'legal_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'subdomain',

        // Dados do banco do tenant
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',

        // Status / assinaturas
        'status',
        'trial_ends_at',
        'asaas_customer_id',
        'asaas_synced',
        'asaas_sync_status',
        'asaas_last_sync_at',
        'asaas_last_error'
    ];

    protected $casts = [
        'trial_ends_at'       => 'datetime',
        'asaas_synced'        => 'boolean',
        'asaas_last_sync_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($tenant) {
            if (! $tenant->id) {
                $tenant->id = (string) Str::uuid();
            }
        });
    }

    /**
     * =====================================================
     *  MÉTODOS EXIGIDOS PELO SwitchTenantDatabaseTask
     * =====================================================
     */
    public function getDatabaseName(): string
    {
        return (string) $this->db_name;
    }

    public function getDatabaseHost(): string
    {
        return (string) $this->db_host;
    }

    public function getDatabasePort(): string
    {
        return (string) $this->db_port;
    }

    public function getDatabaseUsername(): string
    {
        return (string) $this->db_username;
    }

    public function getDatabasePassword(): string
    {
        return (string) $this->db_password;
    }


    /**
     * =====================================================
     *  RELACIONAMENTOS
     * =====================================================
     */
    public function localizacao()
    {
        return $this->hasOne(TenantLocalizacao::class, 'tenant_id', 'id');
    }

    public function initializeTenant(array $attributes)
    {
        // Corrige quando o Spatie restaura tenant errado usando integer
        if (isset($attributes['id']) && is_numeric($attributes['id'])) {
            \Log::critical("⚠️ Spatie restaurou tenant com ID NUMÉRICO!!!", [
                'id' => $attributes['id'],
                'attributes' => $attributes
            ]);

            // Impede criação de tenant inválido
            return false;
        }

        return parent::initializeTenant($attributes);
    }
}
