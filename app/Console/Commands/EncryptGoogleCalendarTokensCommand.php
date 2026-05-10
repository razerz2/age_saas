<?php

namespace App\Console\Commands;

use App\Models\Platform\Tenant;
use App\Models\Tenant\GoogleCalendarToken;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class EncryptGoogleCalendarTokensCommand extends Command
{
    protected $signature = 'tenant:google-calendar-tokens:encrypt
        {--dry-run : Simula a migracao sem persistir alteracoes}
        {--tenant= : Slug do tenant para processar apenas um ambiente}';

    protected $description = 'Criptografa tokens Google Calendar legados com compatibilidade multitenant';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $tenantSlug = trim((string) $this->option('tenant'));

        $tenants = $this->resolveTenants($tenantSlug);
        if ($tenants === null) {
            return self::FAILURE;
        }

        $this->info($dryRun
            ? 'Iniciando simulacao da criptografia de tokens Google Calendar...'
            : 'Iniciando criptografia de tokens Google Calendar...');

        $totals = [
            'tenants' => 0,
            'found' => 0,
            'already_encrypted' => 0,
            'migrated' => 0,
            'errors' => 0,
        ];

        $originalTenant = Tenant::current();

        foreach ($tenants as $tenant) {
            $totals['tenants']++;

            try {
                $tenant->makeCurrent();
                $result = $this->processCurrentTenant($dryRun);

                $totals['found'] += $result['found'];
                $totals['already_encrypted'] += $result['already_encrypted'];
                $totals['migrated'] += $result['migrated'];
                $totals['errors'] += $result['errors'];

                $this->line(sprintf(
                    'Tenant %s: encontrados=%d, ja_criptografados=%d, migrados=%d, erros=%d',
                    (string) $tenant->subdomain,
                    $result['found'],
                    $result['already_encrypted'],
                    $result['migrated'],
                    $result['errors']
                ));
            } catch (Throwable $exception) {
                $totals['errors']++;
                $this->error(sprintf('Tenant %s: falha ao processar.', (string) $tenant->subdomain));
                Log::warning('Falha ao criptografar tokens Google Calendar no tenant.', [
                    'tenant_id' => (string) $tenant->id,
                    'tenant_slug' => (string) $tenant->subdomain,
                    'exception' => $exception::class,
                ]);
            } finally {
                Tenant::forgetCurrent();
            }
        }

        if ($originalTenant && (!Tenant::current() || Tenant::current()?->id !== $originalTenant->id)) {
            try {
                $originalTenant->makeCurrent();
            } catch (Throwable $exception) {
                Log::warning('Nao foi possivel restaurar o tenant original apos migracao de tokens Google Calendar.', [
                    'tenant_id' => (string) $originalTenant->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        $this->newLine();
        $this->info('Resumo:');
        $this->line('Tenants processados: ' . $totals['tenants']);
        $this->line('Tokens encontrados: ' . $totals['found']);
        $this->line('Tokens ja criptografados: ' . $totals['already_encrypted']);
        $this->line('Tokens migrados: ' . $totals['migrated']);
        $this->line('Tokens com erro: ' . $totals['errors']);

        if ($dryRun) {
            $this->comment('Dry-run concluido. Nenhuma alteracao foi persistida.');
        } else {
            $this->info('Migracao concluida.');
        }

        return $totals['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Tenant>|null
     */
    protected function resolveTenants(string $tenantSlug)
    {
        if ($tenantSlug !== '') {
            $tenant = Tenant::query()->where('subdomain', $tenantSlug)->first();
            if (!$tenant) {
                $this->error(sprintf('Tenant com slug "%s" nao encontrado.', $tenantSlug));
                return null;
            }

            return collect([$tenant]);
        }

        return Tenant::query()->orderBy('subdomain')->get();
    }

    /**
     * @return array{found:int, already_encrypted:int, migrated:int, errors:int}
     */
    protected function processCurrentTenant(bool $dryRun): array
    {
        $result = [
            'found' => 0,
            'already_encrypted' => 0,
            'migrated' => 0,
            'errors' => 0,
        ];

        GoogleCalendarToken::query()
            ->select(['id', 'access_token', 'refresh_token'])
            ->orderBy('id')
            ->cursor()
            ->each(function (GoogleCalendarToken $token) use (&$result, $dryRun): void {
                $result['found']++;

                $rawAccessToken = $token->getRawOriginal('access_token');
                $rawRefreshToken = $token->getRawOriginal('refresh_token');

                $decodedAccess = $this->decodeAccessTokenRawValue($rawAccessToken);
                $accessEncrypted = GoogleCalendarToken::isEncryptedAccessTokenPayload($decodedAccess);
                $refreshEncrypted = GoogleCalendarToken::looksLikeEncryptedString($rawRefreshToken);

                if ($accessEncrypted && ($rawRefreshToken === null || $refreshEncrypted)) {
                    $result['already_encrypted']++;
                    return;
                }

                try {
                    $accessToken = $token->access_token;
                    $refreshToken = $token->refresh_token;

                    if (!$dryRun) {
                        $token->access_token = $accessToken;
                        $token->refresh_token = $refreshToken;
                        $token->save();
                    }

                    $result['migrated']++;
                } catch (Throwable $exception) {
                    $result['errors']++;
                    Log::warning('Falha ao migrar token Google Calendar.', [
                        'token_id' => (string) $token->id,
                        'exception' => $exception::class,
                    ]);
                }
            });

        return $result;
    }

    protected function decodeAccessTokenRawValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }
}
