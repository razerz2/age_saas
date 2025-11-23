<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id','name','duration_min','is_active'];

    protected $casts = ['is_active' => 'boolean'];
}