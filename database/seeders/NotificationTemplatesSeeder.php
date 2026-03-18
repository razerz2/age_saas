<?php

namespace Database\Seeders;

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Models\Platform\WhatsAppUnofficialTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPlatformEmailTemplates();
        $this->seedTenantEmailTemplates();
    }

    private function seedPlatformEmailTemplates(): void
    {
        $templates = WhatsAppUnofficialTemplate::query()
            ->active()
            ->orderBy('key')
            ->get();

        foreach ($templates as $template) {
            $key = strtolower(trim((string) $template->key));
            $body = (string) $template->body;

            if ($key === '' || trim($body) === '') {
                continue;
            }

            $displayName = $this->resolveDisplayName((string) $template->title, $key);
            $subject = $this->resolveSubject($displayName, $key);

            $this->createEmailTemplateIfMissing(
                scope: NotificationTemplate::SCOPE_PLATFORM,
                name: $key,
                displayName: $displayName,
                subject: $subject,
                body: $body,
                variables: $this->normalizeVariables($template->variables),
                enabled: (bool) $template->is_active
            );
        }
    }

    private function seedTenantEmailTemplates(): void
    {
        $templates = TenantDefaultNotificationTemplate::query()
            ->active()
            ->where('channel', NotificationTemplate::CHANNEL_WHATSAPP)
            ->orderBy('key')
            ->get();

        foreach ($templates as $template) {
            $key = strtolower(trim((string) $template->key));
            $body = (string) $template->content;

            if ($key === '' || trim($body) === '') {
                continue;
            }

            $displayName = $this->resolveDisplayName((string) $template->title, $key);
            $subject = $this->resolveSubject($displayName, $key);

            $this->createEmailTemplateIfMissing(
                scope: NotificationTemplate::SCOPE_TENANT,
                name: $key,
                displayName: $displayName,
                subject: $subject,
                body: $body,
                variables: $this->normalizeVariables($template->variables),
                enabled: (bool) $template->is_active
            );
        }
    }

    private function createEmailTemplateIfMissing(
        string $scope,
        string $name,
        string $displayName,
        string $subject,
        string $body,
        array $variables,
        bool $enabled
    ): void {
        NotificationTemplate::query()->firstOrCreate(
            [
                'scope' => $scope,
                'channel' => NotificationTemplate::CHANNEL_EMAIL,
                'name' => $name,
            ],
            [
                'id' => (string) Str::uuid(),
                'display_name' => $displayName,
                'subject' => $subject,
                'body' => $body,
                'default_subject' => $subject,
                'default_body' => $body,
                'variables' => $variables,
                'enabled' => $enabled,
            ]
        );
    }

    private function resolveDisplayName(string $title, string $key): string
    {
        $normalized = trim($title);
        if ($normalized !== '') {
            return Str::limit($normalized, 160, '');
        }

        return Str::limit($this->humanizeKey($key), 160, '');
    }

    private function resolveSubject(string $displayName, string $key): string
    {
        $subject = trim($displayName);
        if ($subject !== '') {
            return Str::limit($subject, 255, '');
        }

        return Str::limit($this->humanizeKey($key), 255, '');
    }

    private function humanizeKey(string $key): string
    {
        return Str::of($key)
            ->replace(['.', '_', '-'], ' ')
            ->squish()
            ->title()
            ->toString();
    }

    /**
     * @param mixed $variables
     * @return array<int, string>
     */
    private function normalizeVariables(mixed $variables): array
    {
        if (!is_array($variables)) {
            return [];
        }

        return collect($variables)
            ->filter(fn ($item): bool => is_scalar($item))
            ->map(fn ($item): string => (string) $item)
            ->values()
            ->all();
    }
}
