<?php

namespace App\Services\Platform;

use App\Models\Platform\Tenant;
use App\Models\Platform\TenantDefaultNotificationTemplate;
use Database\Seeders\TenantDefaultNotificationTemplatesSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenantDefaultNotificationTemplateProvisioningService
{
    /**
     * @return array{
     *   total_defaults:int,
     *   inserted:int,
     *   updated:int,
     *   skipped:int,
     *   dry_run:bool,
     *   overwrite_existing:bool,
     *   reason:?string
     * }
     */
    public function syncForTenant(
        Tenant $tenant,
        bool $overwriteExisting = false,
        bool $dryRun = false
    ): array {
        if (!Schema::connection('pgsql')->hasTable('tenant_default_notification_templates')) {
            return $this->emptyResult($overwriteExisting, $dryRun, 'Tabela tenant_default_notification_templates ausente na Platform.');
        }

        if (!Schema::connection('tenant')->hasTable('notification_templates')) {
            return $this->emptyResult($overwriteExisting, $dryRun, 'Tabela notification_templates ausente no banco tenant.');
        }

        $defaults = $this->loadActiveDefaults();

        if ($defaults->isEmpty()) {
            return $this->emptyResult($overwriteExisting, $dryRun, 'Nenhum template ativo encontrado no baseline da Platform.');
        }

        $tenantId = (string) $tenant->id;

        $existingPairs = DB::connection('tenant')
            ->table('notification_templates')
            ->where('tenant_id', $tenantId)
            ->whereIn('channel', $defaults->pluck('channel')->unique()->values()->all())
            ->whereIn('key', $defaults->pluck('key')->unique()->values()->all())
            ->get(['channel', 'key'])
            ->mapWithKeys(static fn ($row): array => [$row->channel . '|' . $row->key => true])
            ->all();

        $now = now();
        $insertRows = [];
        $updateRows = [];
        $skipped = 0;

        foreach ($defaults as $template) {
            $pair = $template->channel . '|' . $template->key;

            if (isset($existingPairs[$pair])) {
                if ($overwriteExisting) {
                    $updateRows[] = [
                        'tenant_id' => $tenantId,
                        'channel' => (string) $template->channel,
                        'key' => (string) $template->key,
                        'subject' => $template->channel === 'email'
                            ? ($template->subject !== null ? (string) $template->subject : null)
                            : null,
                        'content' => (string) $template->content,
                        'updated_at' => $now,
                    ];
                } else {
                    $skipped++;
                }

                continue;
            }

            $insertRows[] = [
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'channel' => (string) $template->channel,
                'key' => (string) $template->key,
                'subject' => $template->channel === 'email'
                    ? ($template->subject !== null ? (string) $template->subject : null)
                    : null,
                'content' => (string) $template->content,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!$dryRun) {
            DB::connection('tenant')->transaction(function () use ($insertRows, $updateRows): void {
                if ($insertRows !== []) {
                    foreach (array_chunk($insertRows, 100) as $chunk) {
                        DB::connection('tenant')->table('notification_templates')->insert($chunk);
                    }
                }

                foreach ($updateRows as $row) {
                    DB::connection('tenant')
                        ->table('notification_templates')
                        ->where('tenant_id', $row['tenant_id'])
                        ->where('channel', $row['channel'])
                        ->where('key', $row['key'])
                        ->update([
                            'subject' => $row['subject'],
                            'content' => $row['content'],
                            'updated_at' => $row['updated_at'],
                        ]);
                }
            });
        }

        return [
            'total_defaults' => $defaults->count(),
            'inserted' => count($insertRows),
            'updated' => count($updateRows),
            'skipped' => $skipped,
            'dry_run' => $dryRun,
            'overwrite_existing' => $overwriteExisting,
            'reason' => null,
        ];
    }

    /**
     * @return EloquentCollection<int, TenantDefaultNotificationTemplate>
     */
    private function loadActiveDefaults(): EloquentCollection
    {
        $defaults = TenantDefaultNotificationTemplate::query()
            ->active()
            ->orderBy('channel')
            ->orderBy('key')
            ->get();

        if ($defaults->isNotEmpty()) {
            return $defaults;
        }

        app(TenantDefaultNotificationTemplatesSeeder::class)->run();

        return TenantDefaultNotificationTemplate::query()
            ->active()
            ->orderBy('channel')
            ->orderBy('key')
            ->get();
    }

    /**
     * @return array{
     *   total_defaults:int,
     *   inserted:int,
     *   updated:int,
     *   skipped:int,
     *   dry_run:bool,
     *   overwrite_existing:bool,
     *   reason:?string
     * }
     */
    private function emptyResult(bool $overwriteExisting, bool $dryRun, string $reason): array
    {
        return [
            'total_defaults' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            'overwrite_existing' => $overwriteExisting,
            'reason' => $reason,
        ];
    }
}
