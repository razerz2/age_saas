<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSpecialty extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id','name','code'];

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specialty');
    }
}