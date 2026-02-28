<?php

namespace App\Support\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Throwable;

class CampaignPatientRules
{
    /**
     * @var array<string, array{label:string,operators:array<int,string>}>
     */
    private const FIELD_DEFINITIONS = [
        'gender' => [
            'label' => 'Sexo',
            'operators' => ['=', '!=', 'in', 'not_in', 'is_null', 'is_not_null'],
        ],
        'is_active' => [
            'label' => 'Paciente ativo',
            'operators' => ['=', '!=', 'in', 'not_in', 'is_null', 'is_not_null'],
        ],
        'birth_date' => [
            'label' => 'Data de nascimento',
            'operators' => ['=', '!=', 'in', 'not_in', 'is_null', 'is_not_null', 'birthday_today'],
        ],
        'city' => [
            'label' => 'Cidade',
            'operators' => ['=', '!=', 'in', 'not_in', 'is_null', 'is_not_null'],
        ],
        'state' => [
            'label' => 'UF',
            'operators' => ['=', '!=', 'in', 'not_in', 'is_null', 'is_not_null'],
        ],
    ];

    /**
     * @var array<string, string>
     */
    private const OPERATOR_LABELS = [
        '=' => 'Igual a',
        '!=' => 'Diferente de',
        'in' => 'Em lista',
        'not_in' => 'Fora da lista',
        'is_null' => 'Vazio',
        'is_not_null' => 'Nao vazio',
        'birthday_today' => 'Aniversaria hoje',
    ];

    /**
     * @var array<int, string>
     */
    private const OPERATORS_REQUIRING_VALUE = ['=', '!=', 'in', 'not_in'];

    /**
     * @var array<string, bool>
     */
    private static array $convertTzSupportCache = [];

    /**
     * @return array<int, string>
     */
    public static function allowedFields(): array
    {
        return array_keys(self::FIELD_DEFINITIONS);
    }

    /**
     * @return array<int, string>
     */
    public static function allowedOperators(): array
    {
        return array_keys(self::OPERATOR_LABELS);
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    public static function fieldOptions(): array
    {
        $options = [];
        foreach (self::FIELD_DEFINITIONS as $field => $definition) {
            $options[] = [
                'value' => $field,
                'label' => $definition['label'],
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value:string,label:string}>
     */
    public static function operatorOptions(): array
    {
        $options = [];
        foreach (self::OPERATOR_LABELS as $operator => $label) {
            $options[] = [
                'value' => $operator,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function fieldOperators(): array
    {
        $result = [];
        foreach (self::FIELD_DEFINITIONS as $field => $definition) {
            $result[$field] = $definition['operators'];
        }

        return $result;
    }

    public static function fieldLabel(string $field): string
    {
        return self::FIELD_DEFINITIONS[$field]['label'] ?? $field;
    }

    public static function operatorLabel(string $operator): string
    {
        return self::OPERATOR_LABELS[$operator] ?? $operator;
    }

    public static function operatorRequiresValue(string $operator): bool
    {
        return in_array($operator, self::OPERATORS_REQUIRING_VALUE, true);
    }

    /**
     * @param mixed $rules
     * @return array{normalized:?array{logic:string,conditions:array<int,array{field:string,op:string,value?:mixed}>},errors:array<string,string>}
     */
    public static function validateAndNormalize(mixed $rules): array
    {
        if ($rules === null || $rules === '') {
            return ['normalized' => null, 'errors' => []];
        }

        if (!is_array($rules)) {
            return [
                'normalized' => null,
                'errors' => ['rules_json' => 'Regras invalidas.'],
            ];
        }

        $errors = [];
        $rawConditions = $rules['conditions'] ?? [];
        if ($rawConditions === null || (is_array($rawConditions) && $rawConditions === [])) {
            return ['normalized' => null, 'errors' => []];
        }

        if (!is_array($rawConditions)) {
            $errors['rules_json.conditions'] = 'As condicoes devem ser enviadas em lista.';

            return [
                'normalized' => null,
                'errors' => $errors,
            ];
        }

        $logic = strtoupper(trim((string) ($rules['logic'] ?? 'AND')));
        if (!in_array($logic, ['AND', 'OR'], true)) {
            $errors['rules_json.logic'] = 'A logica das regras deve ser AND ou OR.';
        }

        $conditions = [];
        foreach ($rawConditions as $index => $rawCondition) {
            $pathPrefix = 'rules_json.conditions.' . $index;

            if (!is_array($rawCondition)) {
                $errors[$pathPrefix] = 'Condicao invalida.';
                continue;
            }

            $field = strtolower(trim((string) ($rawCondition['field'] ?? '')));
            $operator = strtolower(trim((string) ($rawCondition['op'] ?? '')));
            $rawValue = $rawCondition['value'] ?? null;
            if (self::isEffectivelyEmptyCondition($field, $operator, $rawValue)) {
                continue;
            }

            if ($field === '' || !array_key_exists($field, self::FIELD_DEFINITIONS)) {
                $errors[$pathPrefix . '.field'] = 'Campo de regra nao permitido.';
                continue;
            }

            if ($operator === '' || !array_key_exists($operator, self::OPERATOR_LABELS)) {
                $errors[$pathPrefix . '.op'] = 'Operador de regra nao permitido.';
                continue;
            }

            if (!in_array($operator, self::FIELD_DEFINITIONS[$field]['operators'], true)) {
                $errors[$pathPrefix . '.op'] = 'Operador nao permitido para o campo selecionado.';
                continue;
            }

            $condition = [
                'field' => $field,
                'op' => $operator,
            ];

            if (self::operatorRequiresValue($operator)) {
                $value = self::normalizeConditionValue($field, $operator, $rawValue);
                if ($value === null || (is_array($value) && $value === [])) {
                    $errors[$pathPrefix . '.value'] = 'Informe um valor valido para esta regra.';
                    continue;
                }

                $condition['value'] = $value;
            }

            $conditions[] = $condition;
        }

        if ($conditions === []) {
            if ($errors === []) {
                return [
                    'normalized' => null,
                    'errors' => [],
                ];
            }

            if (!array_key_exists('rules_json.conditions', $errors) && !array_key_exists('rules_json.logic', $errors)) {
                $errors['rules_json.conditions'] = 'Adicione pelo menos uma condicao valida.';
            }

            return [
                'normalized' => null,
                'errors' => $errors,
            ];
        }

        if ($errors !== []) {
            return [
                'normalized' => null,
                'errors' => $errors,
            ];
        }

        return [
            'normalized' => [
                'logic' => $logic,
                'conditions' => $conditions,
            ],
            'errors' => [],
        ];
    }

    /**
     * @param mixed $rules
     * @return array{logic:string,conditions:array<int,array{field:string,op:string,value?:mixed}>}|null
     */
    public static function normalizeRules(mixed $rules): ?array
    {
        $result = self::validateAndNormalize($rules);
        return $result['normalized'];
    }

    /**
     * @param mixed $rules
     */
    public static function applyToPatientQuery(Builder $query, mixed $rules, string $timezone): Builder
    {
        $normalized = self::normalizeRules($rules);
        if ($normalized === null) {
            return $query;
        }

        $conditions = $normalized['conditions'];
        if (self::requiresAddressJoin($conditions)) {
            $query->leftJoin('patient_addresses as campaign_rule_addresses', function ($join) {
                $join->on('campaign_rule_addresses.patient_id', '=', 'patients.id');
            });
        }

        if (self::requiresGenderJoin($conditions)) {
            $query->leftJoin('genders as campaign_rule_genders', function ($join) {
                $join->on('campaign_rule_genders.id', '=', 'patients.gender_id');
            });
        }

        $logic = $normalized['logic'];
        $query->where(function (Builder $group) use ($conditions, $logic, $timezone) {
            foreach ($conditions as $index => $condition) {
                $method = $logic === 'OR' && $index > 0 ? 'orWhere' : 'where';

                $group->{$method}(function (Builder $conditionBuilder) use ($condition, $timezone) {
                    self::applyCondition($conditionBuilder, $condition, $timezone);
                });
            }
        });

        return $query;
    }

    /**
     * @param mixed $rules
     * @return array{logic:string,logic_label:string,conditions:array<int,array{field_label:string,operator_label:string,value_label:string}>}|null
     */
    public static function describeRules(mixed $rules): ?array
    {
        $normalized = self::normalizeRules($rules);
        if ($normalized === null) {
            return null;
        }

        $conditions = [];
        foreach ($normalized['conditions'] as $condition) {
            $conditions[] = [
                'field_label' => self::fieldLabel((string) $condition['field']),
                'operator_label' => self::operatorLabel((string) $condition['op']),
                'value_label' => self::formatConditionValue($condition),
            ];
        }

        return [
            'logic' => $normalized['logic'],
            'logic_label' => $normalized['logic'] === 'OR'
                ? 'Qualquer condicao (OR)'
                : 'Todas as condicoes (AND)',
            'conditions' => $conditions,
        ];
    }

    /**
     * @param array<int, array{field:string,op:string,value?:mixed}> $conditions
     */
    private static function requiresAddressJoin(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (in_array($condition['field'], ['city', 'state'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array{field:string,op:string,value?:mixed}> $conditions
     */
    private static function requiresGenderJoin(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if ($condition['field'] === 'gender') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{field:string,op:string,value?:mixed} $condition
     */
    private static function applyCondition(Builder $query, array $condition, string $timezone): void
    {
        $field = (string) $condition['field'];
        $operator = (string) $condition['op'];
        $value = $condition['value'] ?? null;
        $column = self::columnForField($field);

        if ($operator === 'birthday_today') {
            self::applyBirthdayToday($query, $column, $timezone);
            return;
        }

        if ($operator === 'is_null') {
            $query->whereNull($column);
            return;
        }

        if ($operator === 'is_not_null') {
            $query->whereNotNull($column);
            return;
        }

        if ($operator === 'in') {
            $list = is_array($value) ? $value : [];
            if ($list === []) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn($column, $list);
            return;
        }

        if ($operator === 'not_in') {
            $list = is_array($value) ? $value : [];
            if ($list === []) {
                return;
            }

            $query->whereNotIn($column, $list);
            return;
        }

        if ($operator === '=') {
            $query->where($column, '=', $value);
            return;
        }

        if ($operator === '!=') {
            $query->where($column, '!=', $value);
        }
    }

    private static function applyBirthdayToday(Builder $query, string $column, string $timezone): void
    {
        $query->whereNotNull($column);

        if (self::supportsConvertTz($query, $timezone)) {
            $query
                ->whereRaw("DAY({$column}) = DAY(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', ?))", [$timezone])
                ->whereRaw("MONTH({$column}) = MONTH(CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', ?))", [$timezone]);
            return;
        }

        $today = Carbon::now($timezone);
        $query
            ->whereDay($column, $today->day)
            ->whereMonth($column, $today->month);
    }

    private static function supportsConvertTz(Builder $query, string $timezone): bool
    {
        $connection = $query->getConnection();
        $key = $connection->getName() . '|' . $timezone;

        if (array_key_exists($key, self::$convertTzSupportCache)) {
            return self::$convertTzSupportCache[$key];
        }

        try {
            $row = $connection->selectOne(
                "SELECT CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', ?) AS converted_now",
                [$timezone]
            );

            $convertedNow = null;
            if (is_object($row)) {
                $values = array_values((array) $row);
                $convertedNow = $values[0] ?? null;
            } elseif (is_array($row)) {
                $values = array_values($row);
                $convertedNow = $values[0] ?? null;
            }

            self::$convertTzSupportCache[$key] = $convertedNow !== null;
        } catch (Throwable) {
            self::$convertTzSupportCache[$key] = false;
        }

        return self::$convertTzSupportCache[$key];
    }

    /**
     * @param mixed $value
     */
    private static function normalizeConditionValue(string $field, string $operator, mixed $value): mixed
    {
        if ($operator === 'in' || $operator === 'not_in') {
            $list = self::normalizeList($value);
            if ($list === []) {
                return null;
            }

            $normalizedList = [];
            foreach ($list as $item) {
                $normalizedValue = self::normalizeSingleValue($field, $item);
                if ($normalizedValue === null) {
                    continue;
                }

                if (!in_array($normalizedValue, $normalizedList, true)) {
                    $normalizedList[] = $normalizedValue;
                }
            }

            return $normalizedList;
        }

        return self::normalizeSingleValue($field, $value);
    }

    /**
     * @param mixed $value
     */
    private static function normalizeSingleValue(string $field, mixed $value): mixed
    {
        if ($field === 'is_active') {
            return self::normalizeNullableBool($value);
        }

        if ($field === 'birth_date') {
            return self::normalizeDateValue($value);
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if ($field === 'state') {
            return strtoupper(substr($raw, 0, 2));
        }

        if ($field === 'gender') {
            return strtoupper($raw);
        }

        return $raw;
    }

    /**
     * @param mixed $value
     */
    private static function normalizeNullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on', 'ativo', 'sim'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off', 'inativo', 'nao'], true)) {
            return false;
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private static function normalizeDateValue(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function normalizeList(mixed $value): array
    {
        $items = is_array($value)
            ? $value
            : explode(',', (string) $value);

        $normalized = [];
        foreach ($items as $item) {
            $token = trim((string) $item);
            if ($token === '') {
                continue;
            }

            $normalized[] = $token;
        }

        return $normalized;
    }

    private static function columnForField(string $field): string
    {
        return match ($field) {
            'gender' => 'campaign_rule_genders.abbreviation',
            'is_active' => 'patients.is_active',
            'birth_date' => 'patients.birth_date',
            'city' => 'campaign_rule_addresses.city',
            'state' => 'campaign_rule_addresses.state',
            default => 'patients.id',
        };
    }

    /**
     * @param array{field:string,op:string,value?:mixed} $condition
     */
    private static function formatConditionValue(array $condition): string
    {
        $field = (string) ($condition['field'] ?? '');
        $operator = (string) ($condition['op'] ?? '');
        if (!self::operatorRequiresValue($operator)) {
            return '-';
        }

        $value = $condition['value'] ?? null;
        if (is_bool($value)) {
            if ($field === 'is_active') {
                return $value ? 'Ativo' : 'Inativo';
            }

            return $value ? 'Sim' : 'Nao';
        }

        if (is_array($value)) {
            $items = array_map(function ($item) use ($field) {
                if (is_bool($item)) {
                    if ($field === 'is_active') {
                        return $item ? 'Ativo' : 'Inativo';
                    }

                    return $item ? 'Sim' : 'Nao';
                }

                return (string) $item;
            }, $value);

            return implode(', ', $items);
        }

        return (string) $value;
    }

    /**
     * @param mixed $value
     */
    private static function isEffectivelyEmptyCondition(string $field, string $operator, mixed $value): bool
    {
        if ($field !== '' || $operator !== '') {
            return false;
        }

        if ($value === null) {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if (trim((string) $item) !== '') {
                    return false;
                }
            }

            return true;
        }

        return trim((string) $value) === '';
    }
}
