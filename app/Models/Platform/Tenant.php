<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
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
}
