<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'assets';

    protected $fillable = [
        'disk',
        'path',
        'filename',
        'mime',
        'size',
        'checksum_sha256',
        'meta_json',
        'created_by',
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];
}
