<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integrations extends Model
{
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    protected $fillable = ['id','key','is_enabled','config'];

    public function oauthAccounts()
    {
        return $this->hasMany(OauthAccount::class);
    }
}