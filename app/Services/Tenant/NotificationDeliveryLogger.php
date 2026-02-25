<?php

namespace App\Services\Tenant;

use App\Models\Tenant\NotificationDelivery;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class NotificationDeliveryLogger
{
    private ?bool $tableExists = null;
    private bool $missingTableWarningLogged = false;

    public function logSuccess(
        string $tenantId,
        string $channel,
        string $key,
        ?string $provider,
        ?string $recipient,
        ?string $subject,
        string $message,
        array $meta = []
    ): void {
        $this->store(
            tenantId: $tenantId,
            channel: $channel,
            key: $key,
            provider: $provider,
            status: 'success',
            recipient: $recipient,
            subject: $subject,
            message: $message,
            errorMessage: null,
            errorCode: null,
            meta: $meta
        );
    }

    public function logError(
        string $tenantId,
        string $channel,
        string $key,
        ?string $provider,
        ?string $recipient,
        ?string $subject,
        string $message,
        Throwable $e,
        array $meta = []
    ): void {
        $this->store(
            tenantId: $tenantId,
            channel: $channel,
            key: $key,
            provider: $provider,
            status: 'error',
            recipient: $recipient,
            subject: $subject,
            message: $message,
            errorMessage: $e->getMessage(),
            errorCode: $this->normalizeErrorCode($e),
            meta: $meta
        );
    }

    private function store(
        string $tenantId,
        string $channel,
        string $key,
        ?string $provider,
        string $status,
        ?string $recipient,
        ?string $subject,
        string $message,
        ?string $errorMessage,
        ?string $errorCode,
        array $meta
    ): void {
        if (!$this->hasTable()) {
            return;
        }

        $normalizedChannel = strtolower(trim($channel));
        $normalizedStatus = strtolower(trim($status));
        $normalizedKey = trim($key);
        $normalizedTenantId = trim($tenantId);

        if ($normalizedTenantId === '' || $normalizedChannel === '' || $normalizedKey === '') {
            return;
        }

        if (!in_array($normalizedStatus, ['success', 'error'], true)) {
            $normalizedStatus = 'error';
        }

        $normalizedMessage = str_replace(["\r\n", "\r"], "\n", $message);
        $normalizedSubject = $subject !== null ? str_replace(["\r\n", "\r"], "\n", $subject) : null;

        $payload = [
            'tenant_id' => $normalizedTenantId,
            'channel' => $normalizedChannel,
            'key' => $normalizedKey,
            'provider' => $this->normalizeNullableString($provider),
            'status' => $normalizedStatus,
            'sent_at' => now(),
            'recipient' => $this->maskRecipient($normalizedChannel, $recipient),
            'subject' => $this->shouldStoreBody() ? $normalizedSubject : null,
            'subject_sha256' => $normalizedSubject !== null ? hash('sha256', $normalizedSubject) : null,
            'message_sha256' => hash('sha256', $normalizedMessage),
            'message_length' => strlen($normalizedMessage),
            'error_message' => $this->normalizeNullableString($errorMessage),
            'error_code' => $this->normalizeNullableString($errorCode),
            'meta' => $this->sanitizeMeta($meta),
            'subject_raw' => $this->shouldStoreBody() ? $normalizedSubject : null,
            'message_raw' => $this->shouldStoreBody() ? $normalizedMessage : null,
        ];

        try {
            NotificationDelivery::query()->create($payload);
        } catch (QueryException $e) {
            if ($this->isUndefinedTableError($e)) {
                $this->tableExists = false;
                $this->logMissingTableWarning();
                return;
            }

            Log::warning('notification_delivery_db_write_failed', [
                'tenant_id' => $tenantId,
                'channel' => $normalizedChannel,
                'key' => $normalizedKey,
                'error' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            Log::warning('notification_delivery_log_failed', [
                'tenant_id' => $tenantId,
                'channel' => $normalizedChannel,
                'key' => $normalizedKey,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function hasTable(): bool
    {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }

        try {
            $this->tableExists = Schema::connection('tenant')->hasTable('notification_deliveries');
        } catch (Throwable) {
            $this->tableExists = false;
        }

        if ($this->tableExists === false) {
            $this->logMissingTableWarning();
        }

        return $this->tableExists;
    }

    private function isUndefinedTableError(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;
        if ($sqlState === '42P01') {
            return true;
        }

        return str_contains(strtolower($e->getMessage()), 'notification_deliveries')
            && str_contains(strtolower($e->getMessage()), 'does not exist');
    }

    private function logMissingTableWarning(): void
    {
        if ($this->missingTableWarningLogged) {
            return;
        }

        $this->missingTableWarningLogged = true;
        Log::warning('notification_deliveries_table_missing');
    }

    private function normalizeErrorCode(Throwable $e): ?string
    {
        $code = $e->getCode();
        if (!is_scalar($code)) {
            return null;
        }

        $code = trim((string) $code);
        return $code !== '' ? $code : null;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }

    private function maskRecipient(string $channel, ?string $recipient): ?string
    {
        if ($recipient === null) {
            return null;
        }

        $recipient = trim($recipient);
        if ($recipient === '') {
            return null;
        }

        if ($channel === 'email') {
            if (!str_contains($recipient, '@')) {
                return '***';
            }

            [$local, $domain] = explode('@', $recipient, 2);
            if ($local === '') {
                return '***@' . $domain;
            }

            if (strlen($local) === 1) {
                return '*@' . $domain;
            }

            return substr($local, 0, 1)
                . str_repeat('*', max(strlen($local) - 2, 1))
                . substr($local, -1)
                . '@'
                . $domain;
        }

        $digits = preg_replace('/\D+/', '', $recipient) ?? '';
        if ($digits === '') {
            return '***';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4) . substr($digits, -4);
    }

    private function shouldStoreBody(): bool
    {
        return filter_var((string) env('NOTIFICATION_STORE_BODY', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    private function sanitizeMeta(array $meta): array
    {
        $sanitized = [];

        foreach ($meta as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if ($value === null || is_scalar($value)) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeMetaArray($value, 0);
            }
        }

        return $sanitized;
    }

    private function sanitizeMetaArray(array $values, int $depth): array
    {
        if ($depth >= 3) {
            return [];
        }

        $sanitized = [];
        foreach ($values as $key => $value) {
            $normalizedKey = is_int($key) ? (string) $key : (is_string($key) ? $key : null);
            if ($normalizedKey === null || $normalizedKey === '') {
                continue;
            }

            if ($value === null || is_scalar($value)) {
                $sanitized[$normalizedKey] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$normalizedKey] = $this->sanitizeMetaArray($value, $depth + 1);
            }
        }

        return $sanitized;
    }
}
