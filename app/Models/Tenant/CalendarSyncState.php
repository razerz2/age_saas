<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarSyncState extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'calendar_sync_state';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'appointment_id', 'external_event_id', 'provider', 'last_sync_at'
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    public $timestamps = false;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
