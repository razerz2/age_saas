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

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'status_label',
        'level_label',
        'context_label',
        'created_at_human',
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Nova',
            'read' => 'Lida',
            default => ucfirst((string) $this->status),
        };
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'error' => 'Erro',
            'warning' => 'Aviso',
            'info' => 'Informação',
            'success' => 'Sucesso',
            default => ucfirst((string) $this->level),
        };
    }

    public function getContextLabelAttribute(): string
    {
        return match ($this->context) {
            null, '' => 'Não informado',
            'invoice' => 'Fatura',
            'payment' => 'Pagamento',
            'subscription' => 'Assinatura',
            'tenant' => 'Tenant',
            'customer' => 'Cliente',
            'webhook' => 'Webhook',
            default => ucfirst((string) $this->context),
        };
    }

    public function getCreatedAtHumanAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->copy()->locale('pt_BR')->diffForHumans();
    }

    public static function unreadCount()
    {
        return static::where('status', 'new')->count();
    }
}
