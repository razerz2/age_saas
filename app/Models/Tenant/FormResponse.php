<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id','form_id','appointment_id','patient_id','submitted_at','status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class, 'response_id');
    }
}
