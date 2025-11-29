<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantSetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'tenant_settings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Obtém um valor de configuração
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Define um valor de configuração
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Verifica se uma configuração está habilitada
     * Por padrão retorna true (notificações habilitadas por padrão)
     */
    public static function isEnabled(string $key): bool
    {
        $value = static::get($key);
        // Se não existe, retorna true (habilitado por padrão)
        if ($value === null) {
            return true;
        }
        return $value === 'true';
    }

    /**
     * Habilita uma configuração
     */
    public static function enable(string $key): void
    {
        static::set($key, 'true');
    }

    /**
     * Desabilita uma configuração
     */
    public static function disable(string $key): void
    {
        static::set($key, 'false');
    }
}

