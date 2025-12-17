<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialAccount extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'financial_accounts';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'name',
        'type',
        'initial_balance',
        'active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'account_id');
    }

    /**
     * Calcula o saldo atual da conta
     */
    public function getCurrentBalanceAttribute()
    {
        $income = $this->transactions()
            ->where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $expense = $this->transactions()
            ->where('type', 'expense')
            ->where('status', 'paid')
            ->sum('amount');

        return $this->initial_balance + $income - $expense;
    }
}

