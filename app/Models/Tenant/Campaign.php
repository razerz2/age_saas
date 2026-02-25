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
        'scheduled_at',
        'created_by',
    ];

    protected $casts = [
        'channels_json' => 'array',
        'content_json' => 'array',
        'audience_json' => 'array',
        'automation_json' => 'array',
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
