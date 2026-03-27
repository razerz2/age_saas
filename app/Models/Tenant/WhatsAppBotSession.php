<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppBotSession extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'whatsapp_bot_sessions';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'channel',
        'provider',
        'contact_phone',
        'contact_identifier',
        'status',
        'current_flow',
        'current_step',
        'state',
        'last_inbound_message_type',
        'last_inbound_message_at',
        'last_outbound_message_at',
        'last_payload',
        'meta',
    ];

    protected $casts = [
        'state' => 'array',
        'last_payload' => 'array',
        'meta' => 'array',
        'last_inbound_message_at' => 'datetime',
        'last_outbound_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $session): void {
            if (empty($session->id)) {
                $session->id = (string) Str::uuid();
            }
        });
    }
}

