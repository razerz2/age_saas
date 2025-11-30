<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringAppointmentRule extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'recurring_appointment_rules';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id',
        'recurring_appointment_id',
        'weekday',
        'start_time',
        'end_time',
        'frequency',
        'interval',
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'interval' => 'integer',
    ];

    public $timestamps = true;

    public function recurringAppointment()
    {
        return $this->belongsTo(RecurringAppointment::class);
    }

    /**
     * Converte weekday string para número (Carbon dayOfWeek)
     * 0 = Sunday, 1 = Monday, ..., 6 = Saturday
     */
    public function getWeekdayNumber(): int
    {
        return match($this->weekday) {
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            default => 1,
        };
    }

    /**
     * Converte weekday string para número (método estático)
     * 0 = Sunday, 1 = Monday, ..., 6 = Saturday
     */
    public static function weekdayToNumber(string $weekday): int
    {
        return match($weekday) {
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            default => 1,
        };
    }

    /**
     * Converte número do dia da semana para string
     */
    public static function weekdayFromNumber(int $dayOfWeek): string
    {
        return match($dayOfWeek) {
            0 => 'sunday',
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            default => 'monday',
        };
    }
}

