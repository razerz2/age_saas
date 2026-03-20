<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrialReminderDispatch extends Model
{
    use HasFactory, HasUuids;

    protected $connection = 'pgsql';
    protected $table = 'trial_reminder_dispatches';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subscription_id',
        'tenant_id',
        'event_key',
        'reference_date',
        'status',
        'channels_sent',
        'attempts',
        'dispatched_at',
        'last_error',
        'meta',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'channels_sent' => 'array',
        'attempts' => 'integer',
        'dispatched_at' => 'datetime',
        'meta' => 'array',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
