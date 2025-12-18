<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClinicNetwork extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($network) {
            if (!$network->id) {
                $network->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relacionamento com tenants
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'network_id');
    }

    /**
     * Relacionamento com usuários da rede
     */
    public function users()
    {
        return $this->hasMany(NetworkUser::class, 'clinic_network_id');
    }
}

