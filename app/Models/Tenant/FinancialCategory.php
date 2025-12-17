<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialCategory extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'financial_categories';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'name',
        'type',
        'color',
        'active',
    ];

    protected $casts = [
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
        return $this->hasMany(FinancialTransaction::class, 'category_id');
    }
}

