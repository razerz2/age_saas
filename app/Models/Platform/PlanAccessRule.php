<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlanAccessRule extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'plan_access_rules';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'plan_id',
        'max_admin_users',
        'max_common_users',
        'max_doctors',
    ];

    /**
     * Relacionamento com plano
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relacionamento com funcionalidades
     */
    public function features()
    {
        return $this->belongsToMany(SubscriptionFeature::class, 'plan_access_rule_feature', 'plan_access_rule_id', 'feature_id')
            ->withPivot('allowed')
            ->withTimestamps();
    }
}
