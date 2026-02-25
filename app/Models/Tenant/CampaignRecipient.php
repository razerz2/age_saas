<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'campaign_recipients';

    protected $casts = [
        'vars_json' => 'array',
        'meta_json' => 'array',
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignRun()
    {
        return $this->belongsTo(CampaignRun::class);
    }
}

