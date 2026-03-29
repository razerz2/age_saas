<?php

namespace Database\Seeders\Platform;

use Illuminate\Database\Seeder;
use App\Models\Platform\Plan;
use App\Models\Platform\SubscriptionFeature;
use App\Models\Platform\PlanAccessRule;
use Illuminate\Support\Facades\DB;

class SubscriptionAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Criar plano Developer (se não existir)
            $plan = Plan::firstOrCreate(
                ['name' => 'Developer'],
                [
                    'periodicity' => 'monthly',
                    'period_months' => 1,
                    'price_cents' => 0,
                    'features' => json_encode([]),
                    'is_active' => true,
                ]
            );

            $this->command->info("✅ Plano Developer criado/verificado: {$plan->id}");

            // 2. Criar todas as funcionalidades
            // Funcionalidades essenciais (obrigatórias em todos os planos)
            $features = [
                ['name' => 'appointments', 'label' => 'Agendamentos', 'is_default' => true],
                ['name' => 'calendar', 'label' => 'Calendário', 'is_default' => true],
                ['name' => 'doctors', 'label' => 'Médicos', 'is_default' => true],
                ['name' => 'patients', 'label' => 'Pacientes', 'is_default' => true],
                ['name' => 'specialties', 'label' => 'Especialidades', 'is_default' => true],
                ['name' => 'users', 'label' => 'Usuários', 'is_default' => true],
                // Funcionalidades opcionais (podem ser desmarcadas)
                ['name' => 'forms', 'label' => 'Formulários', 'is_default' => false],
                ['name' => 'agenda_recorrente', 'label' => 'Agenda Recorrente', 'is_default' => false],
                ['name' => 'teleconsulta', 'label' => 'Teleconsulta', 'is_default' => false],
                ['name' => 'notifications', 'label' => 'Notificações', 'is_default' => false],
                ['name' => 'reports', 'label' => 'Relatórios', 'is_default' => false],
                ['name' => 'whatsapp', 'label' => 'WhatsApp', 'is_default' => false],
                ['name' => 'whatsapp_bot', 'label' => 'Bot de WhatsApp', 'is_default' => false],
            ];

            $createdFeatures = [];
            foreach ($features as $featureData) {
                $feature = SubscriptionFeature::updateOrCreate(
                    ['name' => $featureData['name']],
                    [
                        'label' => $featureData['label'],
                        'is_default' => $featureData['is_default'],
                    ]
                );
                $createdFeatures[] = $feature;
                $this->command->info("  ✓ Funcionalidade criada/atualizada: {$feature->label} (" . ($feature->is_default ? 'Essencial' : 'Opcional') . ")");
            }

            $this->command->info("✅ Total de funcionalidades: " . count($createdFeatures));

            // 3. Criar regra de acesso completa para o plano Developer
            $rule = PlanAccessRule::firstOrCreate(
                ['plan_id' => $plan->id],
                [
                    'max_admin_users' => 999,
                    'max_common_users' => 999,
                    'max_doctors' => 999,
                ]
            );

            $this->command->info("✅ Regra de acesso criada/verificada: {$rule->id}");

            // 4. Associar TODAS as features com allowed = true
            $rule->features()->detach(); // Remove associações antigas

            foreach ($createdFeatures as $feature) {
                $rule->features()->attach($feature->id, [
                    'allowed' => true,
                ]);
            }

            $this->command->info("✅ Todas as funcionalidades associadas à regra");

            DB::commit();

            $this->command->info("\n🎉 Seeder executado com sucesso!");
            $this->command->info("   - Plano: Developer");
            $this->command->info("   - Funcionalidades: " . count($createdFeatures));
            $this->command->info("   - Regra de acesso: Criada com limites ilimitados");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("❌ Erro ao executar seeder: {$e->getMessage()}");
            throw $e;
        }
    }
}
