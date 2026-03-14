<?php

namespace App\Exceptions;

use RuntimeException;

class WhatsAppMetaApiException extends RuntimeException
{
    /**
     * @param array<string, mixed> $metaError
     */
    public function __construct(
        string $message,
        private readonly int $httpStatus = 0,
        private readonly array $metaError = [],
        private readonly ?string $responseSummary = null
    ) {
        parent::__construct($message, $httpStatus);
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * @return array<string, mixed>
     */
    public function metaError(): array
    {
        return $this->metaError;
    }

    public function responseSummary(): ?string
    {
        return $this->responseSummary;
    }

    public function userSafeMessage(): string
    {
        $message = trim((string) ($this->metaError['message'] ?? ''));
        $code = $this->metaError['code'] ?? null;
        $details = trim((string) ($this->metaError['details'] ?? ''));
        $fbtraceId = trim((string) ($this->metaError['fbtrace_id'] ?? ''));

        $parts = [];
        if ($message !== '') {
            $parts[] = $message;
        }
        if ($code !== null && $code !== '') {
            $parts[] = 'code=' . $code;
        }
        if ($details !== '') {
            $parts[] = 'details=' . $details;
        }
        if ($fbtraceId !== '') {
            $parts[] = 'fbtrace_id=' . $fbtraceId;
        }

        $metaDetails = implode(' | ', $parts);
        if ($metaDetails === '') {
            return $this->getMessage();
        }

        $status = $this->httpStatus > 0 ? 'status ' . $this->httpStatus . ' - ' : '';
        return 'Falha HTTP na API Meta: ' . $status . $metaDetails . '.';
    }
}
