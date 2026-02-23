<?php

namespace App\Support;

class FormAnswerFormatter
{
    /**
     * @param mixed $value
     */
    public static function format(string $type, $value): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_string($value) && trim($value) === '') {
            return '—';
        }

        $normalizedType = strtolower(trim($type));
        $booleanTypes = ['yes_no', 'boolean', 'sim_nao', 'toggle'];

        if (in_array($normalizedType, $booleanTypes, true)) {
            return ((int) $value) === 1 ? 'Sim' : 'Não';
        }

        return (string) $value;
    }
}
