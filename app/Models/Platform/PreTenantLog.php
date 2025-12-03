<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreTenantLog extends Model
{
    use HasFactory;

    protected $table = 'pre_tenant_logs';

    protected $fillable = [
        'pre_tenant_id',
        'event',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Relacionamento com pré-tenant
     */
    public function preTenant()
    {
        return $this->belongsTo(PreTenant::class, 'pre_tenant_id');
    }
}
