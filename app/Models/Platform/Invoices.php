<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Invoices extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subscription_id',
        'tenant_id',
        'amount_cents',
        'due_date',
        'status',
        'payment_link',
        'payment_method',
        'provider',
        'provider_id',
        'asaas_payment_id',
        'asaas_synced',
        'asaas_sync_status',
        'asaas_last_sync_at',
        'asaas_last_error',
    ];

    protected $casts = [
        'due_date'            => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'asaas_last_sync_at'  => 'datetime',
    ];

    // 💰 Accessor para valor formatado
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount_cents / 100, 2, ',', '.');
    }

    // 🔗 Relacionamentos
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
