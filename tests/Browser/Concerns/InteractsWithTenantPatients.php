<?php

namespace Tests\Browser\Concerns;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Patient;
use Illuminate\Support\Str;
use RuntimeException;

trait InteractsWithTenantPatients
{
    use InteractsWithTenantTestContext;

    /**
     * @return array{id: string, full_name: string, cpf: string}
     */
    protected function createControlledPatientTarget(
        string $seed,
        string $namePrefix = 'Paciente Edit Base',
        string $testTag = 'dusk_patient_edit'
    ): array
    {
        $tenant = $this->resolveTenantForPatientTests();
        $tenant->makeCurrent();

        $cpf = $this->generateUniqueCpf($seed);

        $patient = Patient::query()->create([
            'id' => (string) Str::uuid(),
            'full_name' => sprintf('%s %s', $namePrefix, $seed),
            'cpf' => $cpf,
            'is_active' => true,
            'is_test' => true,
            'test_tag' => $testTag,
        ]);

        return [
            'id' => (string) $patient->id,
            'full_name' => (string) $patient->full_name,
            'cpf' => (string) $patient->cpf,
        ];
    }

    protected function tenantPatientExists(string $patientId): bool
    {
        $tenant = $this->resolveTenantForPatientTests();
        $tenant->makeCurrent();

        return Patient::query()->whereKey($patientId)->exists();
    }

    private function generateUniqueCpf(string $seed): string
    {
        $numericSeed = preg_replace('/\D+/', '', $seed) ?: $seed;

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $suffix = str_pad((string) $attempt, 2, '0', STR_PAD_LEFT);
            $candidate = substr(str_pad($numericSeed . $suffix, 11, '0', STR_PAD_LEFT), -11);

            if (! Patient::query()->where('cpf', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new RuntimeException('Nao foi possivel gerar CPF unico para o paciente alvo de teste.');
    }

    private function resolveTenantForPatientTests(): PlatformTenant
    {
        $context = $this->tenantTestContext();
        $tenant = PlatformTenant::query()
            ->where('subdomain', $context->slug)
            ->first();

        if (! $tenant instanceof PlatformTenant) {
            throw new RuntimeException(sprintf('Tenant "%s" nao encontrado para setup do teste de paciente.', $context->slug));
        }

        return $tenant;
    }
}
