<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'calendars';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'doctor_id', 'name', 'external_id'];

    public $timestamps = true;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}