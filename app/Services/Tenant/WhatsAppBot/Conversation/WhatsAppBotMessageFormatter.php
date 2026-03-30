<?php

namespace App\Services\Tenant\WhatsAppBot\Conversation;

class WhatsAppBotMessageFormatter
{
    /**
     * @param array<int, string> $options
     */
    public function numberedOptions(array $options): string
    {
        $lines = [];
        $position = 1;

        foreach ($options as $option) {
            $text = trim((string) $option);
            if ($text === '') {
                continue;
            }

            $lines[] = sprintf('%d) %s', $position, $text);
            $position++;
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<int, string> $blocks
     */
    public function compose(array $blocks): string
    {
        $parts = [];

        foreach ($blocks as $block) {
            $text = trim((string) $block);
            if ($text === '') {
                continue;
            }

            $parts[] = $text;
        }

        return implode("\n\n", $parts);
    }

    /**
     * @param array<int, string> $options
     */
    public function promptWithOptions(string $prompt, array $options): string
    {
        return $this->compose([
            $prompt,
            $this->numberedOptions($options),
        ]);
    }

    /**
     * @param array<int, string> $detailLines
     * @param array<int, string> $options
     */
    public function confirmation(string $question, array $detailLines, array $options): string
    {
        return $this->compose([
            $question,
            implode("\n", array_values(array_filter(array_map(
                static fn ($line): string => trim((string) $line),
                $detailLines
            ), static fn (string $line): bool => $line !== ''))),
            $this->numberedOptions($options),
        ]);
    }

    public function sanitizeDisplayName(string $value, string $fallback): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        if ($text === '') {
            return $fallback;
        }

        $hasTechnicalToken = preg_match('/\b(dusk|teste?|test)\b/i', $text) === 1;
        $hasLongNumericSuffix = preg_match('/\d{5,}/', $text) === 1;

        if ($hasTechnicalToken && $hasLongNumericSuffix) {
            return $fallback;
        }

        return $text;
    }
}

