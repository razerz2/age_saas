<?php

namespace App\Services\Tenant;

use App\Models\Tenant\NotificationTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationTemplateService
{
    private ?bool $overrideTableExists = null;
    private bool $missingTableWarningLogged = false;

    /**
     * Exemplo de uso:
     * $template = app(NotificationTemplateService::class)
     *     ->getEffectiveTemplate($tenantId, 'email', 'appointment.pending_confirmation');
     */
    public function listKeys(): array
    {
        $templates = $this->catalogTemplates();
        $supportedChannels = $this->supportedChannels();
        $items = [];

        foreach ($templates as $key => $templateConfig) {
            $channels = array_values(array_filter(
                $supportedChannels,
                static fn (string $channel): bool => isset($templateConfig[$channel]) && is_array($templateConfig[$channel])
            ));

            $items[] = [
                'key' => (string) $key,
                'label' => (string) ($templateConfig['label'] ?? $key),
                'channels' => $channels,
            ];
        }

        return $items;
    }

    public function getDefaultTemplate(string $channel, string $key): array
    {
        return $this->resolveDefaultTemplate($channel, $key);
    }

    public function getOverride(string $tenantId, string $channel, string $key): ?NotificationTemplate
    {
        $channel = $this->normalizeChannel($channel);

        if (!$this->hasOverridesTable()) {
            return null;
        }

        try {
            return NotificationTemplate::query()
                ->forTenant($tenantId)
                ->forChannel($channel)
                ->forKey($key)
                ->first();
        } catch (QueryException $e) {
            if ($this->isUndefinedTableError($e)) {
                $this->overrideTableExists = false;
                $this->logMissingTableWarning($tenantId, 'read');
                return null;
            }

            throw $e;
        }
    }

    public function getEffectiveTemplate(string $tenantId, string $channel, string $key): array
    {
        $default = $this->resolveDefaultTemplate($channel, $key);
        $override = $this->getOverride($tenantId, $default['channel'], $key);

        if ($override) {
            return [
                'key' => $key,
                'channel' => $default['channel'],
                'label' => $default['label'],
                'subject' => $default['channel'] === 'email' ? $override->subject : null,
                'content' => $override->content,
                'is_override' => true,
            ];
        }

        return [
            'key' => $key,
            'channel' => $default['channel'],
            'label' => $default['label'],
            'subject' => $default['subject'],
            'content' => $default['content'],
            'is_override' => false,
        ];
    }

    public function saveOverride(
        string $tenantId,
        string $channel,
        string $key,
        ?string $subject,
        string $content
    ): NotificationTemplate {
        $default = $this->resolveDefaultTemplate($channel, $key);
        $content = trim($content);

        $this->assertOverridesTableAvailableForWrite($tenantId);

        if ($content === '') {
            throw ValidationException::withMessages([
                'content' => 'O conteudo do template e obrigatorio.',
            ]);
        }

        $subjectToSave = null;
        if ($default['channel'] === 'email') {
            $subjectToSave = $subject !== null ? trim($subject) : null;

            $defaultHasSubject = $default['subject'] !== null && trim($default['subject']) !== '';
            if ($defaultHasSubject && ($subjectToSave === null || $subjectToSave === '')) {
                throw ValidationException::withMessages([
                    'subject' => 'O assunto e obrigatorio para templates de email.',
                ]);
            }
        }

        return NotificationTemplate::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'channel' => $default['channel'],
                'key' => $key,
            ],
            [
                'subject' => $default['channel'] === 'email' ? $subjectToSave : null,
                'content' => $content,
            ]
        );
    }

    public function restoreDefault(string $tenantId, string $channel, string $key): void
    {
        $default = $this->resolveDefaultTemplate($channel, $key);
        $this->assertOverridesTableAvailableForWrite($tenantId);

        NotificationTemplate::query()
            ->forTenant($tenantId)
            ->forChannel($default['channel'])
            ->forKey($key)
            ->delete();
    }

    private function hasOverridesTable(): bool
    {
        if ($this->overrideTableExists !== null) {
            return $this->overrideTableExists;
        }

        try {
            $this->overrideTableExists = Schema::connection('tenant')->hasTable('notification_templates');
        } catch (Throwable) {
            $this->overrideTableExists = false;
        }

        return $this->overrideTableExists;
    }

    private function assertOverridesTableAvailableForWrite(string $tenantId): void
    {
        if ($this->hasOverridesTable()) {
            return;
        }

        $this->logMissingTableWarning($tenantId, 'write');

        throw ValidationException::withMessages([
            'templates' => 'A tabela notification_templates nao existe neste tenant. Execute as migrations do tenant (database/migrations/tenant).',
        ]);
    }

    private function isUndefinedTableError(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        if ($sqlState === '42P01') {
            return true;
        }

        return str_contains(strtolower($e->getMessage()), 'relation "notification_templates" does not exist');
    }

    private function logMissingTableWarning(string $tenantId, string $operation): void
    {
        if ($this->missingTableWarningLogged) {
            return;
        }

        $this->missingTableWarningLogged = true;

        Log::warning('Tabela notification_templates ausente no banco tenant. Usando defaults do catalogo.', [
            'tenant_id' => $tenantId,
            'operation' => $operation,
            'table' => 'notification_templates',
        ]);
    }

    private function resolveDefaultTemplate(string $channel, string $key): array
    {
        $channel = $this->normalizeChannel($channel);
        $supportedChannels = $this->supportedChannels();
        if (!in_array($channel, $supportedChannels, true)) {
            throw ValidationException::withMessages([
                'channel' => "Canal invalido: {$channel}.",
            ]);
        }

        $templates = $this->catalogTemplates();
        if (!array_key_exists($key, $templates)) {
            throw ValidationException::withMessages([
                'key' => "Template '{$key}' nao encontrado no catalogo padrao.",
            ]);
        }

        $template = $templates[$key];
        if (!isset($template[$channel]) || !is_array($template[$channel])) {
            throw ValidationException::withMessages([
                'channel' => "O template '{$key}' nao suporta o canal '{$channel}'.",
            ]);
        }

        $content = trim((string) ($template[$channel]['content'] ?? ''));
        if ($content === '') {
            throw ValidationException::withMessages([
                'content' => "Template padrao invalido para '{$key}' no canal '{$channel}'.",
            ]);
        }

        $subject = $template[$channel]['subject'] ?? null;

        return [
            'key' => $key,
            'channel' => $channel,
            'label' => (string) ($template['label'] ?? $key),
            'subject' => $subject !== null ? (string) $subject : null,
            'content' => $content,
        ];
    }

    private function normalizeChannel(string $channel): string
    {
        return strtolower(trim($channel));
    }

    private function supportedChannels(): array
    {
        return array_values((array) config('notification_templates.channels', ['email', 'whatsapp']));
    }

    private function catalogTemplates(): array
    {
        return (array) config('notification_templates.templates', []);
    }
}
