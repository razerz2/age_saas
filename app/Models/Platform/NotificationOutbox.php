<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationOutbox extends Model
{
    use HasFactory;

    protected $table = 'notifications_outbox';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'channel',
        'subject',
        'body',
        'meta',
        'scheduled_at',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}