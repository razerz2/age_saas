<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Plan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'plans';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'periodicity',
        'period_months',
        'price_cents',
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
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
     * Relacionamento com regra de acesso
     */
    public function accessRule()
    {
        return $this->hasOne(PlanAccessRule::class, 'plan_id');
    }
}
