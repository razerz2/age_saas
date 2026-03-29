<?php

namespace App\Services\Tenant\WhatsAppBot\Conversation;

use App\Services\Tenant\WhatsAppBot\DTO\InboundMessage;
use Illuminate\Support\Str;

class WhatsAppBotIntentRouter
{
    public const INTENT_UNKNOWN = 'unknown';
    public const INTENT_SCHEDULE = 'schedule';
    public const INTENT_VIEW_APPOINTMENTS = 'view_appointments';
    public const INTENT_CANCEL_APPOINTMENTS = 'cancel_appointments';

    public function resolve(InboundMessage $message): string
    {
        $text = $this->normalizeText((string) $message->text);
        if ($text === '') {
            return self::INTENT_UNKNOWN;
        }

        if (in_array($text, ['1', 'agendar', 'agendar consulta', 'marcar', 'marcar consulta'], true)
            || $this->containsAny($text, ['agendar', 'marcar consulta', 'quero agendar'])) {
            return self::INTENT_SCHEDULE;
        }

        if (in_array($text, ['2', 'ver', 'ver agendamentos', 'meus agendamentos'], true)
            || $this->containsAny($text, ['ver agendamento', 'consultar agendamento', 'meus agendamentos'])) {
            return self::INTENT_VIEW_APPOINTMENTS;
        }

        if (in_array($text, ['3', 'cancelar', 'cancelar agendamento', 'desmarcar'], true)
            || $this->containsAny($text, ['cancelar', 'desmarcar'])) {
            return self::INTENT_CANCEL_APPOINTMENTS;
        }

        return self::INTENT_UNKNOWN;
    }

    public function parseSelectionNumber(string $text): ?int
    {
        $normalized = $this->normalizeText($text);
        if (preg_match('/^(\d{1,2})$/', $normalized, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @param array<int, string>|null $keywords
     */
    public function isResetCommand(string $text, ?array $keywords = null): bool
    {
        $normalized = $this->normalizeText($text);
        $resetKeywords = $keywords ?? ['menu', 'inicio', 'start', 'reiniciar', 'voltar', '0'];

        if ($normalized === '') {
            return false;
        }

        foreach ($resetKeywords as $keyword) {
            if ($this->normalizeForComparison((string) $keyword) === $normalized) {
                return true;
            }
        }

        return false;
    }

    public function isAffirmative(string $text): bool
    {
        $normalized = $this->normalizeText($text);

        return in_array($normalized, ['1', 'sim', 'confirmar', 'ok', 'confirmo'], true);
    }

    public function isNegative(string $text): bool
    {
        $normalized = $this->normalizeText($text);

        return in_array($normalized, ['2', 'nao', 'cancelar', 'voltar'], true);
    }

    public function isGreeting(string $text): bool
    {
        $normalized = $this->normalizeForComparison($text);
        if ($normalized === '') {
            return false;
        }

        if (in_array($normalized, ['oi', 'ola', 'bom dia', 'boa tarde', 'boa noite'], true)) {
            return true;
        }

        return $this->containsAny($normalized, ['oi ', 'ola ', 'quero comecar', 'iniciar']);
    }

    /**
     * @param array<int, string> $keywords
     */
    public function matchesAnyKeyword(string $text, array $keywords): bool
    {
        $normalizedText = $this->normalizeForComparison($text);
        if ($normalizedText === '') {
            return false;
        }

        foreach ($keywords as $keyword) {
            if ($this->normalizeForComparison((string) $keyword) === $normalizedText) {
                return true;
            }
        }

        return false;
    }

    public function normalizeForComparison(string $value): string
    {
        return $this->normalizeText($value);
    }

    /**
     * @param array<int, string> $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeText(string $value): string
    {
        $value = trim(Str::lower($value));
        if ($value === '') {
            return '';
        }

        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        $value = Str::ascii($value);
        $value = preg_replace('/[^a-z0-9\s]/', '', $value) ?? '';

        return trim($value);
    }
}
