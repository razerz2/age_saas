<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemNotification extends Model
{
    use HasFactory; // opcional, se quiser factory

    /**
     * A chave primária não é auto incrementável.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * O tipo da chave primária é string (UUID).
     *
     * @var string
     */
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'title', 'message', 'context', 'level', 'status'
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function markAsRead()
    {
        $this->update(['status' => 'read']);
    }

    public static function unreadCount()
    {
        return static::where('status', 'new')->count();
    }
}
