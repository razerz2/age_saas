<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlanAccessRuleFeature extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'plan_access_rule_feature';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'plan_access_rule_id',
        'feature_id',
        'allowed',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];
}
