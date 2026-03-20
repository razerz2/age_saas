<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $connection = 'pgsql';
    protected $table = 'subscriptions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'due_day',
        'billing_anchor_date',
        'recovery_started_at',
        'status',
        'auto_renew',
        'payment_method',
        'is_trial',
        'trial_ends_at',

        'asaas_subscription_id',
        'asaas_synced',
        'asaas_sync_status',
        'asaas_last_sync_at',
        'asaas_last_error',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'billing_anchor_date' => 'date',
        'recovery_started_at' => 'datetime',
        'auto_renew' => 'boolean',
        'is_trial' => 'boolean',
        'trial_ends_at' => 'datetime',
        'asaas_last_sync_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $subscription) {
            $plan = null;

            if ($subscription->relationLoaded('plan')) {
                $plan = $subscription->plan;
            } elseif (!empty($subscription->plan_id)) {
                $plan = Plan::query()->find($subscription->plan_id);
            }

            if ($plan?->isTest()) {
                // Hardening comercial: plano de teste nao pode ficar pendente/cancelado.
                $subscription->status = 'active';
                $subscription->due_day = (int) ($subscription->due_day ?: 1);
                $subscription->payment_method = $subscription->payment_method ?: 'PIX';
            }
        });
    }

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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusLabel(): string
    {
        if ($this->is_trial && in_array($this->status, ['active', 'trialing'], true)) {
            return 'Em teste';
        }

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
            'BOLETO' => 'Boleto Bancario',
            'CREDIT_CARD' => 'Cartao de Credito',
            'DEBIT_CARD' => 'Cartao de Debito',
            null => 'Nao aplicavel',
            default => 'Desconhecido',
        };
    }

    public function isTestPlan(): bool
    {
        $this->loadMissing('plan');

        return (bool) $this->plan?->isTest();
    }

    public function isTrialActive(): bool
    {
        if (! $this->is_trial || ! in_array($this->status, ['active', 'trialing'], true)) {
            return false;
        }

        if (! $this->trial_ends_at) {
            return false;
        }

        return now()->lte($this->trial_ends_at);
    }

    public function isTrialExpired(): bool
    {
        if (! $this->is_trial || ! $this->trial_ends_at) {
            return false;
        }

        return now()->gt($this->trial_ends_at);
    }

    public function daysRemainingInTrial(): ?int
    {
        if (! $this->is_trial || ! $this->trial_ends_at) {
            return null;
        }

        if ($this->isTrialExpired()) {
            return 0;
        }

        $remaining = now()->startOfDay()->diffInDays($this->trial_ends_at->copy()->startOfDay(), false);

        return max(0, $remaining);
    }

    public function usesFinancialFlow(): bool
    {
        return ! $this->isTestPlan() && ! $this->is_trial;
    }
}
