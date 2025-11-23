<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessHour extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id','doctor_id','weekday','start_time','end_time'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}