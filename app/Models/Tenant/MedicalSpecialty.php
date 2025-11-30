<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalSpecialty extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'medical_specialties';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'name', 'code', 'label_singular', 'label_plural', 'registration_label'];

    public $timestamps = true;

    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_specialty', 'specialty_id', 'doctor_id');
    }
}