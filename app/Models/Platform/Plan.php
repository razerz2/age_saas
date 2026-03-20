<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;

class Plan extends Model
{
    use HasFactory, HasUuids;

    public const TYPE_REAL = 'real';
    public const TYPE_TEST = 'test';

    protected $connection = 'pgsql';
    protected $table = 'plans';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'periodicity',
        'period_months',
        'price_cents',
        'category',
        'plan_type',
        'show_on_landing_page',
        'trial_enabled',
        'trial_days',
        'features',
        'is_active',
    ];

    const CATEGORY_COMMERCIAL = 'commercial';
    const CATEGORY_CONTRACTUAL = 'contractual';
    const CATEGORY_SANDBOX = 'sandbox';

    protected $casts = [
        'features' => 'array',
        'show_on_landing_page' => 'boolean',
        'trial_enabled' => 'boolean',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Retorna o preço em reais (float)
     */
    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }

    /**
     * Retorna o preço formatado com R$
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'R$ ' . number_format($this->price_cents / 100, 2, ',', '.');
    }

    /**
     * Garante que features sempre seja um array
     */
    public function getFeaturesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return [];
    }

    /**
     * Relacionamento com regra de acesso
     */
    public function accessRule()
    {
        return $this->hasOne(PlanAccessRule::class, 'plan_id');
    }

    public function isReal(): bool
    {
        return $this->plan_type === self::TYPE_REAL;
    }

    public function isTest(): bool
    {
        return $this->plan_type === self::TYPE_TEST;
    }

    public function isVisibleOnLanding(): bool
    {
        return (bool) $this->show_on_landing_page;
    }

    public function hasCommercialTrial(): bool
    {
        return $this->isReal()
            && (bool) $this->is_active
            && (bool) $this->trial_enabled
            && (int) $this->trial_days > 0;
    }

    public function trialDaysLabel(): string
    {
        $days = (int) $this->trial_days;

        if ($days <= 0) {
            return 'Sem trial';
        }

        return $days . ' ' . ($days === 1 ? 'dia' : 'dias');
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_COMMERCIAL => 'Comercial',
            self::CATEGORY_CONTRACTUAL => 'Contratual',
            self::CATEGORY_SANDBOX => 'Sandbox',
            default => (string) $this->category,
        };
    }

    public function categoryBadgeClass(): string
    {
        return match ($this->category) {
            self::CATEGORY_COMMERCIAL => 'bg-info',
            self::CATEGORY_CONTRACTUAL => 'bg-primary',
            self::CATEGORY_SANDBOX => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }

    public function planTypeLabel(): string
    {
        return $this->isTest() ? 'Teste' : 'Producao';
    }

    public function planTypeBadgeClass(): string
    {
        return $this->isTest() ? 'bg-warning text-dark' : 'bg-success';
    }

    public function landingVisibilityLabel(): string
    {
        return $this->isVisibleOnLanding() ? 'Visivel na Landing' : 'Oculto na Landing';
    }

    public function landingVisibilityBadgeClass(): string
    {
        return $this->isVisibleOnLanding() ? 'bg-primary' : 'bg-secondary';
    }

    /**
     * Escopo para planos disponíveis à comercialização pública.
     */
    public function scopePubliclyAvailable(Builder $query): Builder
    {
        return $query
            ->where('category', self::CATEGORY_COMMERCIAL)
            ->where('is_active', true)
            ->where('plan_type', self::TYPE_REAL)
            ->where('show_on_landing_page', true);
    }

    public function isPubliclyAvailable(): bool
    {
        return $this->category === self::CATEGORY_COMMERCIAL
            && (bool) $this->is_active
            && $this->isReal()
            && $this->isVisibleOnLanding();
    }
}
