<?php

namespace App\Services;

use App\Models\Platform\Tenant;
use App\Models\Platform\Subscription;
use App\Models\Platform\PlanAccessRule;
use App\Models\Platform\SubscriptionFeature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeatureAccessService
{
    /**
     * Verifica se o tenant atual tem acesso a uma funcionalidade específica
     *
     * @param string $featureName Nome da funcionalidade (ex: 'whatsapp_integration', 'google_calendar')
     * @param Tenant|null $tenant Tenant a verificar (se null, usa o tenant atual)
     * @return bool
     */
    public function hasFeature(string $featureName, ?Tenant $tenant = null): bool
    {
        $tenant = $tenant ?? Tenant::current();

        if (!$tenant) {
            Log::warning('FeatureAccessService: Nenhum tenant ativo');
            return false;
        }

        // Busca a assinatura ativa do tenant
        $subscription = $this->getActiveSubscription($tenant);

        if (!$subscription) {
            Log::info("FeatureAccessService: Tenant {$tenant->id} não possui assinatura ativa");
            return false;
        }

        $plan = $subscription->plan;

        if (!$plan) {
            Log::warning("FeatureAccessService: Plano não encontrado para subscription {$subscription->id}");
            return false;
        }

        // Verifica se o plano tem regra de acesso
        $accessRule = $plan->accessRule;

        if (!$accessRule) {
            Log::info("FeatureAccessService: Plano {$plan->id} não possui regra de acesso");
            return false;
        }

        // Busca a feature pelo nome (na base da plataforma)
        $platformConnection = config('multitenancy.landlord_database_connection_name', env('DB_CONNECTION', 'pgsql'));
        $feature = SubscriptionFeature::on($platformConnection)->where('name', $featureName)->first();

        if (!$feature) {
            Log::warning("FeatureAccessService: Feature '{$featureName}' não encontrada");
            return false;
        }

        // Verifica se a feature está permitida para o plano (na base da plataforma)
        $featureAccess = DB::connection($platformConnection)
            ->table('plan_access_rule_feature')
            ->where('plan_access_rule_id', $accessRule->id)
            ->where('feature_id', $feature->id)
            ->where('allowed', true)
            ->exists();

        if ($featureAccess) {
            Log::debug("FeatureAccessService: Tenant {$tenant->id} tem acesso à feature '{$featureName}'");
            return true;
        }

        Log::info("FeatureAccessService: Tenant {$tenant->id} NÃO tem acesso à feature '{$featureName}'");
        return false;
    }

    /**
     * Verifica se o tenant tem acesso a qualquer uma das funcionalidades fornecidas
     *
     * @param array $featureNames Array de nomes de funcionalidades
     * @param Tenant|null $tenant Tenant a verificar
     * @return bool
     */
    public function hasAnyFeature(array $featureNames, ?Tenant $tenant = null): bool
    {
        foreach ($featureNames as $featureName) {
            if ($this->hasFeature($featureName, $tenant)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se o tenant tem acesso a todas as funcionalidades fornecidas
     *
     * @param array $featureNames Array de nomes de funcionalidades
     * @param Tenant|null $tenant Tenant a verificar
     * @return bool
     */
    public function hasAllFeatures(array $featureNames, ?Tenant $tenant = null): bool
    {
        foreach ($featureNames as $featureName) {
            if (!$this->hasFeature($featureName, $tenant)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna todas as funcionalidades disponíveis para o tenant atual
     *
     * @param Tenant|null $tenant Tenant a verificar
     * @return array Array com os nomes das funcionalidades
     */
    public function getAvailableFeatures(?Tenant $tenant = null): array
    {
        $tenant = $tenant ?? Tenant::current();

        if (!$tenant) {
            return [];
        }

        $subscription = $this->getActiveSubscription($tenant);

        if (!$subscription || !$subscription->plan || !$subscription->plan->accessRule) {
            return [];
        }

        $accessRule = $subscription->plan->accessRule;

        // Busca features na base da plataforma
        $platformConnection = config('multitenancy.landlord_database_connection_name', env('DB_CONNECTION', 'pgsql'));
        
        $features = DB::connection($platformConnection)
            ->table('plan_access_rule_feature')
            ->join('subscription_features', 'plan_access_rule_feature.feature_id', '=', 'subscription_features.id')
            ->where('plan_access_rule_feature.plan_access_rule_id', $accessRule->id)
            ->where('plan_access_rule_feature.allowed', true)
            ->pluck('subscription_features.name')
            ->toArray();

        return $features;
    }

    /**
     * Retorna a assinatura ativa do tenant
     * Como subscriptions estão na base da plataforma, precisamos usar a conexão da plataforma
     *
     * @param Tenant $tenant
     * @return Subscription|null
     */
    protected function getActiveSubscription(Tenant $tenant): ?Subscription
    {
        // Subscriptions estão na base da plataforma (landlord), não do tenant
        // Precisamos usar a conexão da plataforma explicitamente
        $platformConnection = config('multitenancy.landlord_database_connection_name', env('DB_CONNECTION', 'pgsql'));
        
        return Subscription::on($platformConnection)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest('starts_at')
            ->first();
    }

    /**
     * Verifica limites do plano (ex: max_doctors, max_users)
     *
     * @param string $limitType Tipo do limite (ex: 'max_doctors', 'max_admin_users')
     * @param Tenant|null $tenant Tenant a verificar
     * @return int|null Retorna o limite ou null se não houver assinatura ativa
     */
    public function getPlanLimit(string $limitType, ?Tenant $tenant = null): ?int
    {
        $tenant = $tenant ?? Tenant::current();

        if (!$tenant) {
            return null;
        }

        $subscription = $this->getActiveSubscription($tenant);

        if (!$subscription || !$subscription->plan || !$subscription->plan->accessRule) {
            return null;
        }

        $accessRule = $subscription->plan->accessRule;

        return match ($limitType) {
            'max_admin_users' => $accessRule->max_admin_users,
            'max_common_users' => $accessRule->max_common_users,
            'max_doctors' => $accessRule->max_doctors,
            default => null,
        };
    }
}

