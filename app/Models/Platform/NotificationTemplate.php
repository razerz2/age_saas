<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory;

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const SCOPE_PLATFORM = 'platform';
    public const SCOPE_TENANT = 'tenant';

    protected $table = 'notification_templates';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'display_name',
        'channel',
        'scope',
        'subject',
        'body',
        'default_subject',
        'default_body',
        'variables',
        'enabled',
    ];

    protected $casts = [
        'variables' => 'array',
        'enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($template) {
            if (!$template->id) {
                $template->id = (string) Str::uuid();
            }
        });
    }

    public function scopeEmailChannel(Builder $query): Builder
    {
        return $query->where('channel', self::CHANNEL_EMAIL);
    }

    public function scopeByScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }
}
