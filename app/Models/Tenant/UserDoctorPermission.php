<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDoctorPermission extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'user_doctor_permissions';

    protected $fillable = [
        'user_id',
        'doctor_id',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}

