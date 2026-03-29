<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantWhatsAppGlobalInstance extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'tenant_whatsapp_global_instances';

    public const PROVIDER_WAHA = 'waha';
    public const PROVIDER_EVOLUTION = 'evolution';
    public const STATUS_PENDING = 'pending';
    public const STATUS_READY = 'ready';
    public const STATUS_ERROR = 'error';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'provider',
        'instance_name',
        'managed_by_system',
        'status',
        'last_error',
    ];

    protected $casts = [
        'managed_by_system' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $instance): void {
            if (!$instance->id) {
                $instance->id = (string) Str::uuid();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', strtolower(trim($provider)));
    }

    public static function tableExists(): bool
    {
        static $exists = null;

        if ($exists !== null) {
            return $exists;
        }

        try {
            $model = new static();
            $connection = $model->getConnectionName() ?: config('database.default');
            $exists = Schema::connection($connection)->hasTable($model->getTable());
        } catch (\Throwable) {
            $exists = false;
        }

        return $exists;
    }
}
