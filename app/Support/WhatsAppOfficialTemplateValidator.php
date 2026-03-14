<?php

namespace App\Support;

class WhatsAppOfficialTemplateValidator
{
    /**
     * @return array<int, string>
     */
    public static function findInvalidPlaceholders(string $bodyText): array
    {
        preg_match_all('/\{\{([^}]+)\}\}/', $bodyText, $matches);

        $invalid = [];
        foreach ($matches[1] ?? [] as $raw) {
            $content = trim((string) $raw);
            if (!preg_match('/^\d+$/', $content)) {
                $invalid[] = $content;
            }
        }

        return array_values(array_unique($invalid));
    }

    /**
     * @return array<int, string>
     */
    public static function extractNumericPlaceholders(string $bodyText): array
    {
        preg_match_all('/\{\{(\d+)\}\}/', $bodyText, $matches);
        $placeholders = array_values(array_unique($matches[1] ?? []));
        sort($placeholders, SORT_NATURAL);

        return $placeholders;
    }

    /**
     * @param mixed $variables
     * @return array<string, string>
     */
    public static function normalizeVariables(mixed $variables): array
    {
        if (is_string($variables) && trim($variables) !== '') {
            $decoded = json_decode($variables, true);
            if (is_array($decoded)) {
                $variables = $decoded;
            }
        }

        if (!is_array($variables)) {
            return [];
        }

        $normalized = [];
        foreach ($variables as $key => $value) {
            $normalizedKey = preg_replace('/\D+/', '', (string) $key) ?? '';
            $normalizedValue = trim((string) $value);
            if ($normalizedKey === '' || $normalizedValue === '') {
                continue;
            }

            $normalized[$normalizedKey] = $normalizedValue;
        }

        ksort($normalized, SORT_NATURAL);

        return $normalized;
    }

    /**
     * @param mixed $sampleVariables
     * @return array<string, string>
     */
    public static function normalizeSampleVariables(mixed $sampleVariables): array
    {
        if (is_string($sampleVariables) && trim($sampleVariables) !== '') {
            $decoded = json_decode($sampleVariables, true);
            if (is_array($decoded)) {
                $sampleVariables = $decoded;
            }
        }

        if (!is_array($sampleVariables)) {
            return [];
        }

        $normalized = [];
        foreach ($sampleVariables as $key => $value) {
            $normalizedKey = preg_replace('/\D+/', '', (string) $key) ?? '';
            $normalizedValue = trim((string) $value);
            if ($normalizedKey === '' || $normalizedValue === '') {
                continue;
            }

            $normalized[$normalizedKey] = $normalizedValue;
        }

        ksort($normalized, SORT_NATURAL);

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    public static function validatePlaceholderConsistency(string $bodyText, mixed $variables): array
    {
        $errors = [];

        $invalid = self::findInvalidPlaceholders($bodyText);
        if ($invalid !== []) {
            $errors['body_text'] = 'Somente placeholders no formato {{1}}, {{2}}, ... são permitidos.';
            return $errors;
        }

        if (self::hasPlaceholderAtBodyEdges($bodyText)) {
            $errors['body_text'] = 'A Meta não permite placeholders no início ou no fim do body_text.';
            return $errors;
        }

        $placeholders = self::extractNumericPlaceholders($bodyText);
        $normalizedVariables = self::normalizeVariables($variables);
        $variableKeys = array_map('strval', array_keys($normalizedVariables));

        if ($placeholders !== [] && $normalizedVariables === []) {
            $errors['variables'] = 'O campo variables deve mapear todos os placeholders usados no body_text.';
            return $errors;
        }

        if ($placeholders === [] && $normalizedVariables !== []) {
            $errors['variables'] = 'Variables informado sem placeholders no body_text.';
            return $errors;
        }

        $placeholders = array_map('strval', $placeholders);
        sort($variableKeys, SORT_NATURAL);
        sort($placeholders, SORT_NATURAL);
        if ($placeholders !== $variableKeys) {
            $errors['variables'] = 'Inconsistência entre placeholders do body_text e chaves do variables.';
        }

        return $errors;
    }

    public static function hasPlaceholderAtBodyEdges(string $bodyText): bool
    {
        $trimmedBody = trim($bodyText);
        if ($trimmedBody === '') {
            return false;
        }

        // Meta rejeita templates com parâmetro no início do BODY.
        if (preg_match('/^\s*[[:punct:]]*\s*\{\{\d+\}\}/', $trimmedBody) === 1) {
            return true;
        }

        // Meta rejeita templates com parâmetro no fim do BODY, mesmo com pontuação final.
        if (preg_match('/\{\{\d+\}\}[[:punct:]]*\s*$/', $trimmedBody) === 1) {
            return true;
        }

        $lines = preg_split('/\R/', $trimmedBody) ?: [];
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if ($line === '') {
                continue;
            }

            // Linha final não pode ser somente um placeholder.
            return preg_match('/^\{\{\d+\}\}[[:punct:]]*$/', $line) === 1;
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function validateSampleVariablesConsistency(
        string $bodyText,
        mixed $sampleVariables,
        bool $requiredWhenPlaceholder = false
    ): array {
        $errors = [];
        $placeholders = array_map('strval', self::extractNumericPlaceholders($bodyText));
        $normalizedSamples = self::normalizeSampleVariables($sampleVariables);
        $sampleKeys = array_map('strval', array_keys($normalizedSamples));

        if ($placeholders === []) {
            if ($normalizedSamples !== []) {
                $errors['sample_variables'] = 'Sample variables informado sem placeholders no body_text.';
            }

            return $errors;
        }

        if ($normalizedSamples === []) {
            if ($requiredWhenPlaceholder) {
                $errors['sample_variables'] = 'Informe exemplos em sample_variables para todos os placeholders antes de enviar para a Meta.';
            }

            return $errors;
        }

        sort($placeholders, SORT_NATURAL);
        sort($sampleKeys, SORT_NATURAL);
        if ($placeholders !== $sampleKeys) {
            $errors['sample_variables'] = 'Inconsistência entre placeholders do body_text e chaves de sample_variables.';
        }

        return $errors;
    }
}