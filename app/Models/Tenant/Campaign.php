<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'campaigns';

    protected $fillable = [
        'name',
        'type',
        'status',
        'channels_json',
        'content_json',
        'audience_json',
        'automation_json',
        'rules_json',
        'schedule_mode',
        'starts_at',
        'ends_at',
        'schedule_weekdays',
        'schedule_times',
        'timezone',
        'scheduled_at',
        'created_by',
    ];

    protected $casts = [
        'channels_json' => 'array',
        'content_json' => 'array',
        'audience_json' => 'array',
        'automation_json' => 'array',
        'rules_json' => 'array',
        'schedule_weekdays' => 'array',
        'schedule_times' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function runs()
    {
        return $this->hasMany(CampaignRun::class);
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function automationLocks()
    {
        return $this->hasMany(CampaignAutomationLock::class);
    }
}
