<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebhookLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'webhook_logs';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'provider',
        'event',
        'invoice_id',
        'payment_id',
        'payload',
        'processed',
        'received_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'received_at' => 'datetime',
    ];
}
