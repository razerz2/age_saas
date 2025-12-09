<?php

namespace App\Traits;

use App\Services\FeatureAccessService;
use App\Models\Platform\Tenant;

trait HasFeatureAccess
{
    /**
     * Verifica se o tenant atual tem acesso a uma funcionalidade
     *
     * @param string $featureName
     * @return bool
     */
    protected function hasFeature(string $featureName): bool
    {
        return app(FeatureAccessService::class)->hasFeature($featureName);
    }

    /**
     * Verifica se o tenant tem acesso a qualquer uma das funcionalidades
     *
     * @param array $featureNames
     * @return bool
     */
    protected function hasAnyFeature(array $featureNames): bool
    {
        return app(FeatureAccessService::class)->hasAnyFeature($featureNames);
    }

    /**
     * Verifica se o tenant tem acesso a todas as funcionalidades
     *
     * @param array $featureNames
     * @return bool
     */
    protected function hasAllFeatures(array $featureNames): bool
    {
        return app(FeatureAccessService::class)->hasAllFeatures($featureNames);
    }

    /**
     * Retorna todas as funcionalidades disponíveis para o tenant atual
     *
     * @return array
     */
    protected function getAvailableFeatures(): array
    {
        return app(FeatureAccessService::class)->getAvailableFeatures();
    }

    /**
     * Retorna o limite do plano para um tipo específico
     *
     * @param string $limitType
     * @return int|null
     */
    protected function getPlanLimit(string $limitType): ?int
    {
        return app(FeatureAccessService::class)->getPlanLimit($limitType);
    }

    /**
     * Aborta a requisição se o tenant não tiver acesso à funcionalidade
     *
     * @param string $featureName
     * @param string|null $message
     * @return void
     */
    protected function requireFeature(string $featureName, ?string $message = null): void
    {
        if (!$this->hasFeature($featureName)) {
            $message = $message ?? "Acesso negado: a funcionalidade '{$featureName}' não está disponível no seu plano";
            abort(403, $message);
        }
    }

    /**
     * Aborta a requisição se o tenant não tiver acesso a todas as funcionalidades
     *
     * @param array $featureNames
     * @param string|null $message
     * @return void
     */
    protected function requireAllFeatures(array $featureNames, ?string $message = null): void
    {
        if (!$this->hasAllFeatures($featureNames)) {
            $featureList = implode(', ', $featureNames);
            $message = $message ?? "Acesso negado: as funcionalidades não estão disponíveis no seu plano: {$featureList}";
            abort(403, $message);
        }
    }
}

