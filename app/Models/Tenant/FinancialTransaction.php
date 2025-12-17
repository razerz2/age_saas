<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'financial_transactions';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'type',
        'origin_type',
        'origin_id',
        'direction',
        'description',
        'amount',
        'gross_amount',
        'gateway_fee',
        'net_amount',
        'date',
        'status',
        'account_id',
        'category_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            
            // Definir direction baseado em type se não fornecido
            if (empty($model->direction)) {
                $model->direction = $model->type === 'income' ? 'credit' : 'debit';
            }
            
            // Preencher valores se não fornecidos
            if (empty($model->gross_amount)) {
                $model->gross_amount = $model->amount ?? 0;
            }
            
            if ($model->net_amount === null) {
                $model->net_amount = ($model->gross_amount ?? $model->amount ?? 0) - ($model->gateway_fee ?? 0);
            }
            
            // Garantir que amount seja igual a net_amount (compatibilidade)
            if (empty($model->amount)) {
                $model->amount = $model->net_amount ?? $model->gross_amount ?? 0;
            }
        });

        // Regras de imutabilidade: bloquear update/delete se status = paid
        static::updating(function ($model) {
            if ($model->getOriginal('status') === 'paid') {
                throw new \RuntimeException('Não é possível atualizar uma transação já paga. Use estorno para reverter.');
            }
        });

        static::deleting(function ($model) {
            if ($model->status === 'paid') {
                throw new \RuntimeException('Não é possível excluir uma transação já paga. Use estorno para reverter.');
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function category()
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function commission()
    {
        return $this->hasOne(DoctorCommission::class, 'transaction_id');
    }
}

