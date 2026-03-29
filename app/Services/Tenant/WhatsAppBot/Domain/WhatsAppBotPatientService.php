<?php

namespace App\Services\Tenant\WhatsAppBot\Domain;

use App\Http\Requests\Tenant\StorePatientRequest;
use App\Models\Tenant\Patient;
use App\Services\WhatsApp\PhoneNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Unique;

class WhatsAppBotPatientService
{
    /**
     * @return array<int, array{key:string,label:string,prompt:string,required:bool}>
     */
    public function registrationFieldDefinitions(): array
    {
        $definitions = [];
        foreach ($this->registrationFieldOrder() as $field) {
            $definitions[] = [
                'key' => $field,
                'label' => $this->fieldLabel($field),
                'prompt' => $this->fieldPrompt($field),
                'required' => true,
            ];
        }

        return $definitions;
    }

    /**
     * @param array<string, mixed> $draft
     * @return array{valid:bool,value:?string,error:?string}
     */
    public function validateRegistrationField(string $field, string $rawValue, array $draft = []): array
    {
        $value = $this->normalizeRegistrationFieldValue($field, $rawValue);
        if ($value === '') {
            return [
                'valid' => false,
                'value' => null,
                'error' => 'Campo obrigatorio. ' . $this->fieldPrompt($field),
            ];
        }

        if ($field === 'cpf' && !$this->isValidCpf($value)) {
            return [
                'valid' => false,
                'value' => null,
                'error' => 'CPF invalido. Envie no formato 000.000.000-00 ou apenas numeros.',
            ];
        }

        if ($field === 'birth_date') {
            try {
                $birthDate = Carbon::createFromFormat('Y-m-d', $value);
            } catch (\Throwable) {
                return [
                    'valid' => false,
                    'value' => null,
                    'error' => 'Data de nascimento invalida. Informe no formato DD/MM/AAAA.',
                ];
            }

            if ($birthDate->gt(now()->startOfDay())) {
                return [
                    'valid' => false,
                    'value' => null,
                    'error' => 'A data de nascimento nao pode estar no futuro.',
                ];
            }
        }

        $rules = $this->storePatientRules();
        $messages = $this->storePatientMessages();
        $fieldRules = isset($rules[$field]) ? (array) $rules[$field] : ['string', 'max:255'];
        $fieldRules = $this->makeFieldRequired($fieldRules);

        if ($field === 'cpf') {
            $fieldRules = array_values(array_filter($fieldRules, function ($rule): bool {
                if ($rule instanceof Unique) {
                    return false;
                }

                return !(is_string($rule) && str_starts_with($rule, 'unique:'));
            }));
        }

        $validator = Validator::make(
            [$field => $value],
            [$field => $fieldRules],
            $messages
        );

        if ($validator->fails()) {
            return [
                'valid' => false,
                'value' => null,
                'error' => (string) ($validator->errors()->first($field) ?: 'Valor invalido.'),
            ];
        }

        return [
            'valid' => true,
            'value' => $value,
            'error' => null,
        ];
    }

    /**
     * @param array<string, mixed> $registrationData
     *
     * @throws ValidationException
     */
    public function createFromRegistration(array $registrationData, string $normalizedPhone): Patient
    {
        $rules = $this->storePatientRules();
        $messages = $this->storePatientMessages();

        $payload = [];
        foreach (array_keys($rules) as $field) {
            if (array_key_exists($field, $registrationData)) {
                $payload[$field] = $this->normalizeRegistrationFieldValue($field, (string) $registrationData[$field]);
            }
        }

        $payload['phone'] = $this->normalizePhoneDigits($normalizedPhone);
        if ($payload['phone'] === '') {
            unset($payload['phone']);
        }

        $cpf = (string) ($payload['cpf'] ?? '');
        if ($cpf !== '') {
            $existing = $this->findByCpf($cpf, false);
            if ($existing instanceof Patient) {
                return $existing;
            }
        }

        $conversationRules = $rules;
        foreach (['full_name', 'cpf', 'email', 'birth_date'] as $requiredField) {
            $conversationRules[$requiredField] = $this->makeFieldRequired((array) ($conversationRules[$requiredField] ?? ['string']));
        }

        $validator = Validator::make($payload, $conversationRules, $messages);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $fillable = array_flip((new Patient())->getFillable());
        $payload = array_intersect_key($payload, $fillable);
        $payload['id'] = (string) Str::uuid();
        $payload['is_active'] = true;

        return Patient::query()->create($payload);
    }

    public function findById(string $patientId): ?Patient
    {
        $id = trim($patientId);
        if ($id === '') {
            return null;
        }

        return Patient::query()
            ->where('is_active', true)
            ->find($id);
    }

    public function findByNormalizedPhone(string $normalizedPhone): ?Patient
    {
        $digits = preg_replace('/\D+/', '', $normalizedPhone) ?? '';
        if ($digits === '') {
            return null;
        }

        $candidates = array_values(array_unique(array_filter([
            $digits,
            '+' . $digits,
            str_starts_with($digits, '55') ? substr($digits, 2) : null,
            str_starts_with($digits, '55') ? '+'.substr($digits, 2) : null,
        ])));

        $query = Patient::query()
            ->where('is_active', true)
            ->where(function ($innerQuery) use ($candidates): void {
                foreach ($candidates as $candidate) {
                    $innerQuery->orWhere('phone', $candidate);
                }
            });

        $directMatch = $query->first();
        if ($directMatch instanceof Patient) {
            return $directMatch;
        }

        $connection = \DB::connection('tenant')->getDriverName();
        if ($connection === 'pgsql') {
            $regexMatch = Patient::query()
                ->where('is_active', true)
                ->whereRaw("REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '', 'g') = ?", [$digits])
                ->first();

            if ($regexMatch instanceof Patient) {
                return $regexMatch;
            }
        } elseif ($connection === 'mysql') {
            $regexMatch = Patient::query()
                ->where('is_active', true)
                ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), '(', ''), ')', ''), '-', ''), ' ', '') = ?", [$digits])
                ->first();

            if ($regexMatch instanceof Patient) {
                return $regexMatch;
            }
        }

        return Patient::query()
            ->where('is_active', true)
            ->get()
            ->first(function (Patient $patient) use ($digits): bool {
                $phone = trim((string) ($patient->phone ?? ''));
                if ($phone === '') {
                    return false;
                }

                $normalized = PhoneNormalizer::normalizeE164($phone);
                $normalizedDigits = preg_replace('/\D+/', '', $normalized) ?? '';

                return $normalizedDigits === $digits;
            });
    }

    public function normalizeCpf(string $value): string
    {
        $digits = preg_replace('/\D+/', '', trim($value)) ?? '';

        return strlen($digits) === 11 ? $digits : '';
    }

    public function isValidCpf(string $cpf): bool
    {
        $digits = $this->normalizeCpf($cpf);
        if ($digits === '') {
            return false;
        }

        if (preg_match('/(\d)\1{10}/', $digits) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += ((int) $digits[$i]) * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $digits[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public function findByCpf(string $cpf, bool $onlyActive = true): ?Patient
    {
        $cpfDigits = $this->normalizeCpf($cpf);
        if ($cpfDigits === '') {
            return null;
        }

        $cpfFormatted = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfDigits) ?: $cpfDigits;

        $query = Patient::query()
            ->where(function ($innerQuery) use ($cpfDigits, $cpfFormatted): void {
                $innerQuery->where('cpf', $cpfDigits)
                    ->orWhere('cpf', $cpfFormatted);
            });

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        $directMatch = $query->first();
        if ($directMatch instanceof Patient) {
            return $directMatch;
        }

        $connection = \DB::connection('tenant')->getDriverName();
        if ($connection === 'pgsql') {
            $regexMatch = Patient::query()
                ->when($onlyActive, fn ($q) => $q->where('is_active', true))
                ->whereRaw("REGEXP_REPLACE(COALESCE(cpf, ''), '[^0-9]', '', 'g') = ?", [$cpfDigits])
                ->first();

            if ($regexMatch instanceof Patient) {
                return $regexMatch;
            }
        } elseif ($connection === 'mysql') {
            $regexMatch = Patient::query()
                ->when($onlyActive, fn ($q) => $q->where('is_active', true))
                ->whereRaw("REPLACE(REPLACE(COALESCE(cpf, ''), '.', ''), '-', '') = ?", [$cpfDigits])
                ->first();

            if ($regexMatch instanceof Patient) {
                return $regexMatch;
            }
        }

        return Patient::query()
            ->when($onlyActive, fn ($q) => $q->where('is_active', true))
            ->get()
            ->first(function (Patient $patient) use ($cpfDigits): bool {
                $dbCpf = preg_replace('/\D+/', '', (string) ($patient->cpf ?? '')) ?? '';

                return $dbCpf === $cpfDigits;
            });
    }

    public function createFromPhoneAndName(string $normalizedPhone, string $name): Patient
    {
        $phone = preg_replace('/\D+/', '', $normalizedPhone) ?? '';
        $fullName = trim($name);

        if ($phone === '' || $fullName === '') {
            throw new \InvalidArgumentException('Unable to create patient without phone and name.');
        }

        return Patient::query()->create([
            'id' => (string) Str::uuid(),
            'full_name' => $fullName,
            'cpf' => $this->generateUniqueCpfPlaceholder(),
            'phone' => $phone,
            'is_active' => true,
        ]);
    }

    private function generateUniqueCpfPlaceholder(): string
    {
        do {
            $cpf = str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
        } while (Patient::query()->where('cpf', $cpf)->exists());

        return $cpf;
    }

    /**
     * @return array<int, string>
     */
    private function registrationFieldOrder(): array
    {
        $requiredFromRules = [];
        foreach ($this->storePatientRules() as $field => $rules) {
            $ruleList = is_array($rules) ? $rules : [$rules];
            foreach ($ruleList as $rule) {
                if ($this->isRuleRequired($rule)) {
                    $requiredFromRules[] = (string) $field;
                    break;
                }
            }
        }

        $baseOrder = ['full_name', 'cpf', 'email', 'birth_date'];

        return array_values(array_unique(array_merge($baseOrder, $requiredFromRules)));
    }

    private function normalizePhoneDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function normalizeRegistrationFieldValue(string $field, string $value): string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return '';
        }

        return match ($field) {
            'cpf' => $this->normalizeCpf($normalized),
            'email' => mb_strtolower($normalized),
            'birth_date' => $this->normalizeBirthDate($normalized),
            default => $normalized,
        };
    }

    private function normalizeBirthDate(string $value): string
    {
        $raw = trim($value);
        if ($raw === '') {
            return '';
        }

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable) {
                // Continue trying the next supported format.
            }
        }

        return '';
    }

    /**
     * @param array<int, mixed> $rules
     * @return array<int, mixed>
     */
    private function makeFieldRequired(array $rules): array
    {
        foreach ($rules as $rule) {
            if ($this->isRuleRequired($rule)) {
                return $rules;
            }
        }

        array_unshift($rules, 'required');

        return $rules;
    }

    private function isRuleRequired(mixed $rule): bool
    {
        if (is_string($rule)) {
            return str_starts_with($rule, 'required');
        }

        $ruleClass = is_object($rule) ? get_class($rule) : '';
        if ($ruleClass === '') {
            return false;
        }

        return str_contains($ruleClass, 'Required');
    }

    /**
     * @return array<string, mixed>
     */
    private function storePatientRules(): array
    {
        $request = new StorePatientRequest();

        return $request->rules();
    }

    /**
     * @return array<string, string>
     */
    private function storePatientMessages(): array
    {
        $request = new StorePatientRequest();

        return $request->messages();
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'full_name' => 'nome completo',
            'cpf' => 'CPF',
            'email' => 'e-mail',
            'birth_date' => 'data de nascimento',
            'gender_id' => 'genero',
            'phone' => 'telefone',
            'postal_code' => 'CEP',
            'street' => 'logradouro',
            'number' => 'numero',
            'complement' => 'complemento',
            'neighborhood' => 'bairro',
            'city' => 'cidade',
            'state' => 'estado',
            'estado_id' => 'estado',
            'cidade_id' => 'cidade',
            default => str_replace('_', ' ', $field),
        };
    }

    private function fieldPrompt(string $field): string
    {
        return match ($field) {
            'full_name' => 'Informe seu nome completo.',
            'cpf' => 'Informe seu CPF (apenas numeros ou no formato 000.000.000-00).',
            'email' => 'Informe seu e-mail.',
            'birth_date' => 'Informe sua data de nascimento (DD/MM/AAAA).',
            'phone' => 'Informe seu telefone com DDD.',
            'postal_code' => 'Informe seu CEP.',
            'street' => 'Informe seu logradouro.',
            'number' => 'Informe o numero do endereco.',
            'neighborhood' => 'Informe seu bairro.',
            'state', 'estado_id' => 'Informe o estado.',
            'city', 'cidade_id' => 'Informe a cidade.',
            default => 'Informe seu ' . $this->fieldLabel($field) . '.',
        };
    }
}
