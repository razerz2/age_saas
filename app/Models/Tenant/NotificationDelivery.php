<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationDelivery extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'notification_deliveries';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'channel',
        'key',
        'provider',
        'status',
        'sent_at',
        'recipient',
        'subject',
        'subject_sha256',
        'message_sha256',
        'message_length',
        'error_message',
        'error_code',
        'meta',
        'subject_raw',
        'message_raw',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $delivery): void {
            if (empty($delivery->id)) {
                $delivery->id = (string) Str::uuid();
            }
        });
    }
}

