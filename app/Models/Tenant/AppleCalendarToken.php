<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppleCalendarToken extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'apple_calendar_tokens';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'doctor_id',
        'username',
        'password',
        'server_url',
        'calendar_url',
    ];

    public $timestamps = true;

    /**
     * Relacionamento com o médico
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
