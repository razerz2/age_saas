<?php

namespace Database\Seeders;

use App\Models\Platform\WhatsAppUnofficialTemplate;
use App\Support\WhatsAppUnofficialTemplateCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WhatsAppUnofficialTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = collect(WhatsAppUnofficialTemplateCatalog::all())
            ->map(function (array $template) use ($now): array {
                return [
                    'id' => (string) Str::uuid(),
                    'key' => (string) $template['key'],
                    'title' => (string) $template['title'],
                    'category' => (string) $template['category'],
                    'body' => (string) $template['body'],
                    'variables' => json_encode($template['variables'] ?? [], JSON_UNESCAPED_UNICODE),
                    'is_active' => (bool) ($template['is_active'] ?? true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            });

        /** @var Collection<int, array<string, mixed>> $rows */
        foreach ($rows->chunk(50) as $chunk) {
            WhatsAppUnofficialTemplate::query()->upsert(
                $chunk->all(),
                ['key'],
                [
                    'title',
                    'category',
                    'body',
                    'variables',
                    'is_active',
                    'updated_at',
                ]
            );
        }
    }
}
