<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class PreTenant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pre_tenants';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'fantasy_name',
        'email',
        'document',
        'phone',
        'plan_id',
        'status',
        'asaas_customer_id',
        'asaas_payment_id',
        'payment_status',
        'subdomain_suggested',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'zipcode',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    /**
     * Relacionamento com plano
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Relacionamento com país
     */
    public function pais()
    {
        return $this->belongsTo(Pais::class, 'country_id', 'id_pais');
    }

    /**
     * Relacionamento com estado
     */
    public function estado()
    {
        return $this->belongsTo(Estado::class, 'state_id', 'id_estado');
    }

    /**
     * Relacionamento com cidade
     */
    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'city_id', 'id_cidade');
    }

    /**
     * Relacionamento com logs
     */
    public function logs()
    {
        return $this->hasMany(PreTenantLog::class, 'pre_tenant_id');
    }

    /**
     * Verifica se o pré-cadastro está pago
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Marca como pago
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'payment_status' => 'confirmed',
        ]);
    }

    /**
     * Marca como cancelado
     */
    public function markAsCanceled(): void
    {
        $this->update([
            'status' => 'canceled',
        ]);
    }

    /**
     * Verifica se o pré-cadastro pode ser excluído
     * Não pode excluir se já foi processado e criou um tenant
     */
    public function canBeDeleted(): bool
    {
        // Se não está pago, pode excluir
        if (!$this->isPaid()) {
            return true;
        }

        // Se está pago, verificar se criou tenant
        $tenantCreatedLog = $this->logs()
            ->where('event', 'tenant_created')
            ->first();

        if ($tenantCreatedLog) {
            $payload = $tenantCreatedLog->payload;
            if (is_array($payload) && isset($payload['tenant_id'])) {
                return false; // Já criou tenant, não pode excluir
            }
        }

        return true; // Pode excluir
    }
}
