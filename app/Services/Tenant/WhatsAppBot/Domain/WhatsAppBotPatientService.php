<?php

namespace App\Services\Tenant\WhatsAppBot\Domain;

use App\Models\Tenant\Patient;
use App\Services\WhatsApp\PhoneNormalizer;
use Illuminate\Support\Str;

class WhatsAppBotPatientService
{
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
}
