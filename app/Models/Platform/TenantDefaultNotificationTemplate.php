<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TenantDefaultNotificationTemplate extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'tenant_default_notification_templates';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'channel',
        'key',
        'title',
        'category',
        'language',
        'subject',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template): void {
            if (!$template->id) {
                $template->id = (string) Str::uuid();
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

