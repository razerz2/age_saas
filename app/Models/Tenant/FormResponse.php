<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'form_responses';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'form_id', 'appointment_id', 'patient_id', 'submitted_at', 'status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public $timestamps = false;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function answers()
    {
        return $this->hasMany(ResponseAnswer::class, 'response_id');
    }
}
