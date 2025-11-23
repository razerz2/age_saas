<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id','name','description','specialty_id','doctor_id','is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections()
    {
        return $this->hasMany(FormSection::class);
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }

    public function specialty()
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
