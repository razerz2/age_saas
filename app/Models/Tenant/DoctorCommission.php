<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DoctorCommission extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'doctor_commissions';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'doctor_id',
        'transaction_id',
        'percentage',
        'amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'amount' => 'decimal:2',
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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function transaction()
    {
        return $this->belongsTo(FinancialTransaction::class, 'transaction_id');
    }

    /**
     * Marca a comissão como paga
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Verifica se a comissão está paga
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}

