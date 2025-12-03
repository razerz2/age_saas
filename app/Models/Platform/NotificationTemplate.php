<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationTemplate extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'display_name',
        'channel',
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
}
