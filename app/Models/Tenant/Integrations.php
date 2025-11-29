<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integrations extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'integrations';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = ['id', 'key', 'is_enabled', 'config'];

    protected $casts = [
        'config' => 'array',
        'is_enabled' => 'boolean',
    ];

    public $timestamps = true;

    public function oauthAccounts()
    {
        return $this->hasMany(OauthAccount::class);
    }
}