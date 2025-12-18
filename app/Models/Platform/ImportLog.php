<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $fillable = [
        'file_name',
        'file_hash',
        'type',
        'summary',
        'user_id',
        'status',
        'total_rows',
        'processed_rows',
        'error_count',
        'skipped_count',
        'temp_path',
        'config',
    ];

    protected $casts = [
        'summary' => 'array',
        'config' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
