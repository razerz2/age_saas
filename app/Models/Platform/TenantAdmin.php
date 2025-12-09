<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class TenantAdmin extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tenant_admins';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'email',
        'password',
        'login_url',
        'name',
        'password_visible',
    ];

    protected $casts = [
        'password_visible' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($tenantAdmin) {
            if (!$tenantAdmin->id) {
                $tenantAdmin->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relacionamento com tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
