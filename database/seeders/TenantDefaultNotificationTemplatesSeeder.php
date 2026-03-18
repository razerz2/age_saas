<?php

namespace Database\Seeders;

use App\Models\Platform\TenantDefaultNotificationTemplate;
use App\Support\TenantDefaultNotificationTemplateCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TenantDefaultNotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = collect(TenantDefaultNotificationTemplateCatalog::all())
            ->map(function (array $template) use ($now): array {
                return [
                    'id' => (string) Str::uuid(),
                    'channel' => (string) $template['channel'],
                    'key' => (string) $template['key'],
                    'title' => (string) $template['title'],
                    'category' => (string) $template['category'],
                    'language' => (string) $template['language'],
                    'subject' => $template['subject'],
                    'content' => (string) $template['content'],
                    'variables' => json_encode($template['variables'] ?? [], JSON_UNESCAPED_UNICODE),
                    'is_active' => (bool) ($template['is_active'] ?? true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        /** @var Collection<int, array<string, mixed>> $rows */
        foreach ($rows->chunk(50) as $chunk) {
            TenantDefaultNotificationTemplate::query()->upsert(
                $chunk->all(),
                ['channel', 'key'],
                [
                    'title',
                    'category',
                    'language',
                    'subject',
                    'content',
                    'variables',
                    'is_active',
                    'updated_at',
                ]
            );
        }
    }
}
