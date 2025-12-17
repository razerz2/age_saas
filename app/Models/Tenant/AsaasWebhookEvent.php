<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AsaasWebhookEvent extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'asaas_webhook_events';

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'asaas_event_id',
        'type',
        'status',
        'payload',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
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
     * Verifica se o evento já foi processado
     */
    public function isProcessed(): bool
    {
        return $this->status === 'success' && !is_null($this->processed_at);
    }

    /**
     * Marca o evento como processado com sucesso
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'success',
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Marca o evento como ignorado
     */
    public function markAsSkipped(string $reason = null): void
    {
        $this->update([
            'status' => 'skipped',
            'processed_at' => now(),
            'error_message' => $reason,
        ]);
    }

    /**
     * Marca o evento como erro
     */
    public function markAsError(string $errorMessage): void
    {
        $this->update([
            'status' => 'error',
            'processed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }
}

