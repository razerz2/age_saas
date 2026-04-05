<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignTemplate extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'campaign_templates';

    protected $fillable = [
        'name',
        'channel',
        'provider_type',
        'template_key',
        'title',
        'content',
        'variables_json',
        'is_active',
    ];

    protected $casts = [
        'variables_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeForWhatsApp(Builder $query): Builder
    {
        return $query->where('channel', 'whatsapp');
    }

    public function scopeUnofficial(Builder $query): Builder
    {
        return $query->where('provider_type', 'unofficial');
    }
}

