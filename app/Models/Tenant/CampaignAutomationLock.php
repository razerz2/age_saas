<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignAutomationLock extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'campaign_automation_locks';

    protected $fillable = [
        'campaign_id',
        'trigger',
        'window_date',
        'window_key',
        'timezone',
        'status',
        'run_id',
        'error_message',
    ];

    protected $casts = [
        'window_date' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function run()
    {
        return $this->belongsTo(CampaignRun::class, 'run_id');
    }
}
