<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OauthAccount extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'oauth_accounts';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'id', 'integration_id', 'user_id',
        'access_token', 'refresh_token', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public $timestamps = false;

    public function integration()
    {
        return $this->belongsTo(Integrations::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
