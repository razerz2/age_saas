<?php

namespace App\Models\Platform;

use App\Models\Platform\TenantLocalizacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    public $incrementing = false;      // UUID
    protected $keyType = 'string';     // UUID como string

    protected $fillable = [
        'legal_name',
        'trade_name',
        'document',
        'email',
        'phone',
        'subdomain',
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',
        'status',
        'trial_ends_at',
        'asaas_customer_id',
        'asaas_synced',
        'asaas_last_sync_at',
        'asaas_last_error',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'asaas_last_sync_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function localizacao()
    {
        return $this->hasOne(TenantLocalizacao::class, 'tenant_id', 'id');
    }
}
