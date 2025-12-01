<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TenantPlanLimit extends Model
{
    use HasFactory, HasUuids;

    protected $connection = 'tenant';
    protected $table = 'tenant_plan_limits';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'max_admin_users',
        'max_common_users',
        'max_doctors',
        'allowed_features',
    ];

    protected $casts = [
        'allowed_features' => 'array',
    ];
}
