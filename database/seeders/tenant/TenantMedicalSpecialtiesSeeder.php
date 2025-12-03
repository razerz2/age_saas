<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Platform\MedicalSpecialtyCatalog;

class TenantMedicalSpecialtiesSeeder extends Seeder
{
    /**
     * Copia as especialidades médicas do catálogo da platform para o tenant.
     */
    public function run()
    {
        Log::info("📋 Iniciando cópia de especialidades médicas...");

        try {
            // Busca todas as especialidades médicas do catálogo da platform
            // Força o uso da conexão 'pgsql' (platform) para garantir acesso ao catálogo
            $catalogSpecialties = MedicalSpecialtyCatalog::on('pgsql')
                ->where('type', 'medical_specialty')
                ->orderBy('name')
                ->get();

            if ($catalogSpecialties->isEmpty()) {
                Log::warning("⚠️ Nenhuma especialidade médica encontrada no catálogo da platform.");
                return;
            }

            $inserted = 0;
            $skipped = 0;

            foreach ($catalogSpecialties as $catalog) {
                // Verifica se já existe (evita duplicatas)
                // Usa conexão 'tenant' explicitamente
                $exists = DB::connection('tenant')
                    ->table('medical_specialties')
                    ->where('id', $catalog->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Insere no banco do tenant
                DB::connection('tenant')->table('medical_specialties')->insert([
                    'id'         => $catalog->id,
                    'name'       => $catalog->name,
                    'code'       => $catalog->code,
                    'created_at' => $catalog->created_at ?? now(),
                    'updated_at' => $catalog->updated_at ?? now(),
                ]);

                $inserted++;
            }

            Log::info("✅ Especialidades médicas copiadas com sucesso!", [
                'inseridas' => $inserted,
                'ignoradas' => $skipped,
                'total_no_catalogo' => $catalogSpecialties->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Erro ao copiar especialidades médicas", [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

