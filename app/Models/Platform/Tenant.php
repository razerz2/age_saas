<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasFactory;

    protected $connection = 'pgsql';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'legal_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'subdomain',
        'plan_id',

        // Credenciais do admin
        'admin_login_url',
        'admin_email',
        'admin_password',

        // Dados do banco do tenant
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',

        // Status / assinaturas
        'status',
        'trial_ends_at',
        'suspended_at',
        'canceled_at',
        'asaas_customer_id',
        'asaas_synced',
        'asaas_sync_status',
        'asaas_last_sync_at',
        'asaas_last_error'
    ];

    protected $casts = [
        'trial_ends_at'       => 'datetime',
        'suspended_at'        => 'datetime',
        'canceled_at'         => 'datetime',
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
    /**
     * Relacao legada.
     * `tenants.plan_id` e mantido apenas para compatibilidade temporaria.
     * Regras comerciais e de acesso devem usar assinatura ativa.
     */
    public function plan()
    {
        return $this->belongsTo(\App\Models\Platform\Plan::class, 'plan_id');
    }

    public function localizacao()
    {
        return $this->hasOne(TenantLocalizacao::class, 'tenant_id', 'id');
    }

    public function admin()
    {
        return $this->hasOne(TenantAdmin::class, 'tenant_id', 'id');
    }

    /**
     * Relacionamento com assinaturas (na base da plataforma)
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    /**
     * Relacionamento com a assinatura ativa do tenant (quando existir).
     */
    public function activeSubscriptionRelation()
    {
        return $this->hasOne(Subscription::class, 'tenant_id')
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query
                    ->where(function ($activeSubscriptionQuery) {
                        $activeSubscriptionQuery
                            ->where(function ($trialFlagQuery) {
                                $trialFlagQuery->whereNull('is_trial')
                                    ->orWhere('is_trial', false);
                            })
                            ->where(function ($periodQuery) {
                                $periodQuery->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            });
                    })
                    ->orWhere(function ($trialQuery) {
                        $trialQuery->where('is_trial', true)
                            ->whereNotNull('trial_ends_at')
                            ->where('trial_ends_at', '>', now());
                    });
            })
            ->latest('starts_at');
    }

    /**
     * Retorna a assinatura ativa do tenant
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query
                    ->where(function ($activeSubscriptionQuery) {
                        $activeSubscriptionQuery
                            ->where(function ($trialFlagQuery) {
                                $trialFlagQuery->whereNull('is_trial')
                                    ->orWhere('is_trial', false);
                            })
                            ->where(function ($periodQuery) {
                                $periodQuery->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            });
                    })
                    ->orWhere(function ($trialQuery) {
                        $trialQuery->where('is_trial', true)
                            ->whereNotNull('trial_ends_at')
                            ->where('trial_ends_at', '>', now());
                    });
            })
            ->latest('starts_at')
            ->first();
    }

    /**
     * Base para bloqueio futuro (NÃO aplicando no login ainda).
     */
    protected function resolvedActiveSubscription(): ?Subscription
    {
        if ($this->relationLoaded('activeSubscriptionRelation')) {
            return $this->getRelation('activeSubscriptionRelation');
        }

        return $this->activeSubscription();
    }

    /**
     * Fonte oficial do plano comercial vigente do tenant.
     * Retorna apenas plano vinculado a assinatura ativa.
     */
    public function currentSubscriptionPlan(): ?Plan
    {
        $subscription = $this->resolvedActiveSubscription();

        if (! $subscription) {
            return null;
        }

        if ($subscription->relationLoaded('plan')) {
            $plan = $subscription->getRelation('plan');

            return $plan instanceof Plan ? $plan : null;
        }

        return $subscription->plan()->first();
    }

    /**
     * Alias semantico para uso no dominio comercial.
     */
    public function commercialPlan(): ?Plan
    {
        return $this->currentSubscriptionPlan();
    }

    /**
     * Prefill operacional para tela de regularizacao.
     * Prioriza o plano comercial vigente e usa `tenants.plan_id` apenas como
     * fallback de compatibilidade durante a transicao do legado.
     */
    public function preferredRegularizationPlanId(): ?string
    {
        $commercialPlanId = $this->currentSubscriptionPlan()?->id;

        if ($commercialPlanId) {
            return (string) $commercialPlanId;
        }

        return $this->plan_id ? (string) $this->plan_id : null;
    }

    public function isEligibleForAccess(): bool
    {
        return $this->currentSubscriptionPlan() !== null;
    }

    public function activeTrialSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('is_trial', true)
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->latest('trial_ends_at')
            ->first();
    }

    public function expiredTrialSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('is_trial', true)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->latest('trial_ends_at')
            ->first();
    }

    public function trialDaysRemaining(): ?int
    {
        $trialSubscription = $this->activeTrialSubscription();

        if (! $trialSubscription || ! $trialSubscription->trial_ends_at) {
            return null;
        }

        $remaining = now()->startOfDay()->diffInDays($trialSubscription->trial_ends_at->copy()->startOfDay(), false);

        return max(0, $remaining);
    }

    public function commercialAccessBlockedMessage(): string
    {
        if (! $this->isEligibleForAccess() && $this->expiredTrialSubscription()) {
            return 'Seu período de teste expirou. Contrate um plano para continuar usando o sistema.';
        }

        return 'Seu ambiente foi criado, mas ainda não está liberado para uso. É necessário definir um plano e uma assinatura ativos.';
    }

    public function commercialAccessStatusKey(): string
    {
        if ($this->isEligibleForAccess()) {
            return 'eligible';
        }

        if ($this->expiredTrialSubscription()) {
            return 'trial_expired';
        }

        $subscription = $this->resolvedActiveSubscription();

        if (! $subscription) {
            return 'no_subscription';
        }

        return 'subscription_without_plan';
    }

    public function commercialAccessStatusLabel(): string
    {
        return match ($this->commercialAccessStatusKey()) {
            'eligible' => 'Apta para acesso',
            'trial_expired' => 'Trial expirado',
            'no_subscription' => 'Sem assinatura',
            'subscription_without_plan' => 'Assinatura sem plano',
            default => 'Bloqueada comercialmente',
        };
    }

    public function commercialAccessStatusBadgeClass(): string
    {
        return match ($this->commercialAccessStatusKey()) {
            'eligible' => 'bg-success',
            'trial_expired' => 'bg-danger',
            'no_subscription' => 'bg-secondary',
            'subscription_without_plan' => 'bg-warning text-dark',
            default => 'bg-danger',
        };
    }

    public function commercialAccessSummaryLabel(): string
    {
        return $this->isEligibleForAccess()
            ? 'Apta para acesso'
            : 'Bloqueada comercialmente';
    }

    public function commercialAccessSummaryBadgeClass(): string
    {
        return $this->isEligibleForAccess() ? 'bg-success' : 'bg-danger';
    }

    public function subscriptionGrantsAccess(?Subscription $subscription): bool
    {
        if (! $subscription) {
            return false;
        }

        $activeSubscription = $this->resolvedActiveSubscription();

        if (! $activeSubscription || $activeSubscription->id !== $subscription->id) {
            return false;
        }

        return $this->isEligibleForAccess();
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
