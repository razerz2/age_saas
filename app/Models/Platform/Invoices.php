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
        'provider',
        'provider_id',
    ];

    protected $casts = [
        'due_date' => 'date',
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
