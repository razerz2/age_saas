<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MedicalSpecialtyCatalog extends Model
{
    use HasFactory;

    protected $table = 'medical_specialties_catalog';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'type',
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
}
