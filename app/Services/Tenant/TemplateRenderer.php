<?php

namespace App\Services\Tenant;

use DateTimeInterface;
use stdClass;

class TemplateRenderer
{
    /**
     * @return list<string>
     */
    public function extractPlaceholders(string $content): array
    {
        if ($content === '') {
            return [];
        }

        preg_match_all('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', $content, $matches);
        $placeholders = $matches[1] ?? [];
        if (!is_array($placeholders) || $placeholders === []) {
            return [];
        }

        $normalized = [];
        foreach ($placeholders as $placeholder) {
            $name = trim((string) $placeholder);
            if ($name !== '') {
                $normalized[] = $name;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function render(string $content, array $context): string
    {
        if ($content === '') {
            return $content;
        }

        $missing = new stdClass();

        $rendered = preg_replace_callback('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', function (array $matches) use ($context, $missing) {
            $path = $matches[1];
            $value = data_get($context, $path, $missing);

            if ($value === $missing) {
                return $matches[0];
            }

            $stringValue = $this->stringifyValue($value);
            return $stringValue ?? $matches[0];
        }, $content);

        return $rendered ?? $content;
    }

    private function stringifyValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        return null;
    }
}
