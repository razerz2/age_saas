<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'business_hours';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'doctor_id', 'weekday', 'start_time', 'end_time'];

    protected $casts = [
        'weekday' => 'integer',
        'start_time' => 'string',
        'end_time' => 'string',
    ];

    public $timestamps = false;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}