<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'subscriptions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'due_day',
        'status',
        'auto_renew',
        'payment_method',

        // 🔹 Campos de sincronização com Asaas
        'asaas_subscription_id',
        'asaas_synced',
        'asaas_sync_status',
        'asaas_last_sync_at',
        'asaas_last_error',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renew' => 'boolean',
        'asaas_last_sync_at' => 'datetime',
    ];

    // 🔗 Relações
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoices::class, 'subscription_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    // 🧠 Helpers
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'active' => 'Ativa',
            'past_due' => 'Atrasada',
            'canceled' => 'Cancelada',
            'trialing' => 'Em teste',
            default => ucfirst($this->status),
        };
    }


    public function getIsExpiredAttribute()
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function getHasPendingInvoiceAttribute()
    {
        return $this->invoices()->whereIn('status', ['pending', 'overdue'])->exists();
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'PIX' => 'PIX',
            'BOLETO' => 'Boleto Bancário',
            'CREDIT_CARD' => 'Cartão de Crédito',
            'DEBIT_CARD' => 'Cartão de Débito',
            default => 'Desconhecido',
        };
    }
}
