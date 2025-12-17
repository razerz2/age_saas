<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialCharge extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'financial_charges';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'asaas_customer_id',
        'asaas_charge_id',
        'amount',
        'billing_type',
        'status',
        'due_date',
        'payment_link',
        'origin',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /**
     * Relacionamento com transações financeiras (suporta múltiplas para pagamentos parciais)
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'origin_id')
            ->where('origin_type', 'charge')
            ->where('type', 'income');
    }

    /**
     * Relacionamento legado (mantido para compatibilidade)
     * Retorna a primeira transação vinculada
     */
    public function transaction()
    {
        return $this->hasOne(FinancialTransaction::class, 'origin_id')
            ->where('origin_type', 'charge')
            ->where('type', 'income')
            ->oldest();
    }
    
    /**
     * Calcula valor total pago (soma de net_amount das transações pagas)
     */
    public function getPaidAmountAttribute(): float
    {
        return (float) $this->transactions()
            ->where('status', 'paid')
            ->sum('net_amount');
    }
    
    /**
     * Determina status de pagamento considerando pagamentos parciais
     * 
     * @return string pending|partially_paid|paid
     */
    public function getPaymentStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'paid';
        }
        
        if ($this->status === 'cancelled' || $this->status === 'expired') {
            return 'pending';
        }
        
        $paidAmount = $this->paid_amount;
        
        if ($paidAmount <= 0) {
            return 'pending';
        }
        
        if ($paidAmount >= $this->amount) {
            return 'paid';
        }
        
        return 'partially_paid';
    }

    /**
     * Verifica se a cobrança está paga
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Verifica se a cobrança está vencida
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date < now()->startOfDay();
    }
}

