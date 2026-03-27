<?php

namespace App\Models\Platform;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppOfficialTemplate extends Model
{
    use HasFactory;

    public const PROVIDER = 'whatsapp_business';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ARCHIVED = 'archived';
    public const DIRECT_EDITABLE_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_REJECTED,
    ];

    protected $table = 'whatsapp_official_templates';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'key',
        'meta_template_name',
        'provider',
        'category',
        'language',
        'header_text',
        'body_text',
        'footer_text',
        'buttons',
        'variables',
        'sample_variables',
        'version',
        'status',
        'meta_template_id',
        'meta_waba_id',
        'meta_response',
        'last_synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'buttons' => 'array',
        'variables' => 'array',
        'sample_variables' => 'array',
        'meta_response' => 'array',
        'last_synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template): void {
            if (!$template->id) {
                $template->id = (string) Str::uuid();
            }

            if (trim((string) $template->provider) === '') {
                $template->provider = self::PROVIDER;
            }
        });
    }

    public function scopeOfficialProvider(Builder $query): Builder
    {
        return $query->where('provider', self::PROVIDER);
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForPlatformBaseline(Builder $query): Builder
    {
        return $query->whereNull('tenant_id');
    }

    public function isDirectlyEditable(): bool
    {
        return in_array($this->status, self::DIRECT_EDITABLE_STATUSES, true);
    }

    public function requiresVersioningForEdit(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
