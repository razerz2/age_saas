<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Throwable;

class CampaignRecipientContextBuilder
{
    /**
     * @return array<string,mixed>
     */
    public function buildBaseContext(?string $date = null): array
    {
        $tenant = tenant();
        $clinicName = trim((string) ($tenant?->trade_name ?? $tenant?->legal_name ?? ''));
        $clinicPhone = trim((string) ($tenant?->phone ?? ''));
        $clinicEmail = trim((string) ($tenant?->email ?? ''));
        $clinicAddress = trim((string) ($tenant?->address ?? ''));
        $slug = trim((string) ($tenant?->subdomain ?? ''));
        $publicBookingUrl = $slug !== '' ? $this->buildPublicBookingUrl($slug) : null;
        $portalUrl = $publicBookingUrl;
        $whatsAppLink = $this->buildWhatsAppLink($clinicPhone);

        return [
            'clinic' => [
                'name' => $clinicName !== '' ? $clinicName : null,
                'phone' => $clinicPhone !== '' ? $clinicPhone : null,
                'email' => $clinicEmail !== '' ? $clinicEmail : null,
                'address' => $clinicAddress !== '' ? $clinicAddress : null,
            ],
            'links' => [
                'public_booking' => $publicBookingUrl,
                'portal' => $portalUrl,
                'whatsapp' => $whatsAppLink,
            ],
            'now' => [
                'date' => $this->normalizeDate($date),
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function buildFromPatient(Patient $patient, ?string $date = null): array
    {
        return $this->buildFromPatientData(
            patientId: (string) $patient->id,
            fullName: (string) ($patient->full_name ?? ''),
            cpf: (string) ($patient->cpf ?? ''),
            email: (string) ($patient->email ?? ''),
            phone: (string) ($patient->phone ?? ''),
            date: $date
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function buildFromPatientData(
        ?string $patientId,
        ?string $fullName,
        ?string $cpf,
        ?string $email,
        ?string $phone,
        ?string $date = null
    ): array {
        $normalizedFullName = $this->normalizeNullableString($fullName);
        $context = $this->buildBaseContext($date);
        $context['patient'] = [
            'id' => $this->normalizeNullableString($patientId),
            'name' => $normalizedFullName,
            'full_name' => $normalizedFullName,
            'first_name' => $this->extractFirstName($normalizedFullName),
            'cpf' => $this->normalizeNullableString($cpf),
            'email' => $this->normalizeNullableString($email),
            'phone' => $this->normalizeNullableString($phone),
        ];

        return $context;
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);
        return $normalized !== '' ? $normalized : null;
    }

    private function extractFirstName(?string $fullName): ?string
    {
        $normalized = trim((string) $fullName);
        if ($normalized === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $normalized);
        $first = is_array($parts) ? trim((string) ($parts[0] ?? '')) : '';

        return $first !== '' ? $first : null;
    }

    private function normalizeDate(?string $date): string
    {
        $normalized = trim((string) $date);
        if ($normalized === '') {
            return now()->format('d/m/Y');
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $normalized) === 1) {
            return $normalized;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized) === 1) {
            return Carbon::createFromFormat('Y-m-d', $normalized)->format('d/m/Y');
        }

        try {
            return Carbon::parse($normalized)->format('d/m/Y');
        } catch (\Throwable) {
            return now()->format('d/m/Y');
        }
    }

    private function buildWhatsAppLink(?string $rawPhone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $rawPhone);
        $digits = trim((string) $digits);

        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/' . $digits;
    }

    private function buildPublicBookingUrl(string $slug): string
    {
        $slug = trim($slug, '/');

        try {
            if (Route::has('public.patient.identify')) {
                return route('public.patient.identify', ['slug' => $slug]);
            }
        } catch (Throwable) {
            // Fallback para URL pública canônica.
        }

        return url('/customer/' . $slug . '/agendamento/identificar');
    }
}
