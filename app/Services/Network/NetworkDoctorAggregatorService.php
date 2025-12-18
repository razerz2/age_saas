<?php

namespace App\Services\Network;

use App\Models\Platform\ClinicNetwork;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\MedicalSpecialty;
use Illuminate\Support\Collection;

class NetworkDoctorAggregatorService
{
    /**
     * Agrega médicos de todos os tenants da rede
     *
     * @param ClinicNetwork $network
     * @param array $filters Filtros opcionais: ['specialty' => string, 'city' => string, 'state' => string]
     * @return Collection<array> Coleção de arrays com dados dos médicos
     */
    public function aggregateDoctors(ClinicNetwork $network, array $filters = []): Collection
    {
        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->get();

        $aggregatedDoctors = collect();

        foreach ($tenants as $tenant) {
            try {
                // Inicializa o contexto do tenant
                tenancy()->initialize($tenant);

                // Busca médicos do tenant
                $doctors = Doctor::with(['user', 'specialties'])
                    ->whereHas('user', function ($query) {
                        $query->where('status', 'active');
                    })
                    ->get();

                // Aplica filtros
                if (!empty($filters['specialty'])) {
                    $doctors = $doctors->filter(function ($doctor) use ($filters) {
                        return $doctor->specialties->contains(function ($specialty) use ($filters) {
                            return $specialty->id === $filters['specialty'] ||
                                   $specialty->name === $filters['specialty'] ||
                                   str_contains(strtolower($specialty->name), strtolower($filters['specialty']));
                        });
                    });
                }

                // Para cada médico, cria um DTO
                foreach ($doctors as $doctor) {
                    $dto = $this->createDoctorDto($tenant, $doctor);

                // Aplica filtros de localidade
                if (!empty($filters['city']) && $dto['city'] !== $filters['city']) {
                    continue;
                }

                if (!empty($filters['state']) && $dto['state'] !== $filters['state']) {
                    continue;
                }

                // Filtro por tenant_slug
                if (!empty($filters['tenant_slug']) && $dto['tenant_slug'] !== $filters['tenant_slug']) {
                    continue;
                }

                $aggregatedDoctors->push($dto);
                }

            } finally {
                // SEMPRE finaliza o contexto do tenant
                tenancy()->end();
            }
        }

        return $aggregatedDoctors;
    }

    /**
     * Cria um DTO (Data Transfer Object) para um médico
     *
     * @param Tenant $tenant
     * @param Doctor $doctor
     * @return array
     */
    protected function createDoctorDto(Tenant $tenant, Doctor $doctor): array
    {
        $user = $doctor->user;
        $localizacao = $tenant->localizacao;

        return [
            'tenant_slug' => $tenant->subdomain,
            'tenant_name' => $tenant->trade_name ?? $tenant->legal_name,
            'tenant_id' => $tenant->id,
            'doctor_id' => $doctor->id,
            'doctor_name' => $user ? $user->name : 'Médico sem nome',
            'crm_number' => $doctor->crm_number,
            'crm_state' => $doctor->crm_state,
            'specialties' => $doctor->specialties->map(function ($specialty) {
                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'code' => $specialty->code,
                ];
            })->toArray(),
            'city' => $localizacao && $localizacao->cidade ? $localizacao->cidade->nome : null,
            'state' => $localizacao && $localizacao->estado ? $localizacao->estado->sigla : null,
            'address' => $localizacao ? [
                'street' => $localizacao->endereco,
                'number' => $localizacao->n_endereco,
                'complement' => $localizacao->complemento,
                'neighborhood' => $localizacao->bairro,
                'zipcode' => $localizacao->cep,
            ] : null,
        ];
    }

    /**
     * Retorna todas as especialidades únicas da rede
     *
     * @param ClinicNetwork $network
     * @return Collection
     */
    public function getNetworkSpecialties(ClinicNetwork $network): Collection
    {
        $tenants = Tenant::where('network_id', $network->id)
            ->where('status', 'active')
            ->get();

        $specialties = collect();

        foreach ($tenants as $tenant) {
            try {
                tenancy()->initialize($tenant);

                $tenantSpecialties = MedicalSpecialty::all();

                foreach ($tenantSpecialties as $specialty) {
                    // Evita duplicatas por ID
                    if (!$specialties->contains('id', $specialty->id)) {
                        $specialties->push([
                            'id' => $specialty->id,
                            'name' => $specialty->name,
                            'code' => $specialty->code,
                        ]);
                    }
                }

            } finally {
                tenancy()->end();
            }
        }

        return $specialties->unique('id')->values();
    }
}

