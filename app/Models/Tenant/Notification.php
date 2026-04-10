<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'notifications';

    public $incrementing = false;
    protected $keyType = 'string';
    
    // A tabela não tem updated_at, apenas created_at e read_at
    public $timestamps = false;

    protected $fillable = [
        'type',
        'title',
        'message',
        'level',
        'status',
        'related_id',
        'related_type',
        'metadata',
        'read_at',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'type_label',
        'status_label',
        'created_at_human',
        'meta_label',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Marca a notificação como lida
     */
    public function markAsRead()
    {
        $this->update([
            'status' => 'read',
            'read_at' => now(),
        ]);
    }

    /**
     * Retorna a relação polimórfica com o modelo relacionado
     */
    public function related()
    {
        return $this->morphTo('related', 'related_type', 'related_id');
    }

    /**
     * Scope para notificações não lidas
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope para notificações lidas
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope para um tipo específico
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'Agendamento',
            'form_response' => 'Resposta de formulário',
            default => 'Geral',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Nova',
            'read' => 'Lida',
            default => $this->read_at ? 'Lida' : 'Nova',
        };
    }

    public function getCreatedAtHumanAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->copy()->locale('pt_BR')->diffForHumans();
    }

    public function getMetaLabelAttribute(): string
    {
        $parts = array_filter([
            $this->type_label,
            $this->status_label,
            $this->created_at_human,
        ], static fn ($value) => $value !== null && $value !== '');

        return implode(' - ', $parts);
    }
}
