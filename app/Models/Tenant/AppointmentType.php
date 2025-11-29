<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'appointment_types';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'doctor_id', 'name', 'duration_min', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_min' => 'integer',
    ];

    public $timestamps = true;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}