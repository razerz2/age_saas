<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRun extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'campaign_runs';

    protected $casts = [
        'context_json' => 'array',
        'totals_json' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class);
    }
}

