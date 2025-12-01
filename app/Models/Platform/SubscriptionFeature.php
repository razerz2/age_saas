<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SubscriptionFeature extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'subscription_features';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'label',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Relacionamento com regras de acesso
     */
    public function accessRules()
    {
        return $this->belongsToMany(PlanAccessRule::class, 'plan_access_rule_feature', 'feature_id', 'plan_access_rule_id')
            ->withPivot('allowed')
            ->withTimestamps();
    }
}
