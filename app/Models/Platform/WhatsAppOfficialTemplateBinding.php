<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WhatsAppOfficialTemplateBinding extends Model
{
    use HasFactory;

    public const SCOPE_PLATFORM = 'platform';
    public const SCOPE_TENANT = 'tenant';

    protected $table = 'whatsapp_official_template_bindings';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'scope',
        'event_key',
        'whatsapp_official_template_id',
        'provider',
        'language',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $binding): void {
            if (!$binding->id) {
                $binding->id = (string) Str::uuid();
            }
        });
    }

    public function officialTemplate(): BelongsTo
    {
        return $this->belongsTo(WhatsAppOfficialTemplate::class, 'whatsapp_official_template_id');
    }

    public function scopeByScope(Builder $query, string $scope): Builder
    {
        return $query->where('scope', $scope);
    }

    public function scopeByEvent(Builder $query, string $eventKey): Builder
    {
        return $query->where('event_key', $eventKey);
    }
}

