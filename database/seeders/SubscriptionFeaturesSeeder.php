<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Platform\SubscriptionFeature;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubscriptionFeaturesSeeder extends Seeder
{
    /**
     * Seed das funcionalidades disponíveis para regras de acesso
     */
    public function run(): void
    {
        $features = [
            // Funcionalidades essenciais (is_default = true)
            [
                'name' => 'appointments',
                'label' => 'Agendamentos',
                'is_default' => true,
            ],
            [
                'name' => 'patients',
                'label' => 'Pacientes',
                'is_default' => true,
            ],
            [
                'name' => 'doctors',
                'label' => 'Médicos',
                'is_default' => true,
            ],
            [
                'name' => 'calendar',
                'label' => 'Agenda',
                'is_default' => true,
            ],
            [
                'name' => 'specialties',
                'label' => 'Especialidades',
                'is_default' => true,
            ],
            
            // Funcionalidades opcionais (is_default = false)
            [
                'name' => 'online_appointments',
                'label' => 'Consultas Online',
                'is_default' => false,
            ],
            [
                'name' => 'medical_appointments',
                'label' => 'Atendimento Médico',
                'is_default' => false,
            ],
            [
                'name' => 'users',
                'label' => 'Usuários',
                'is_default' => false,
            ],
            [
                'name' => 'business_hours',
                'label' => 'Horários Médicos',
                'is_default' => false,
            ],
            [
                'name' => 'forms',
                'label' => 'Formulários',
                'is_default' => false,
            ],
            [
                'name' => 'reports',
                'label' => 'Relatórios',
                'is_default' => false,
            ],
            [
                'name' => 'integrations',
                'label' => 'Integrações',
                'is_default' => false,
            ],
            [
                'name' => 'settings',
                'label' => 'Configurações',
                'is_default' => false,
            ],
            [
                'name' => 'whatsapp_notifications',
                'label' => 'Notificação por WhatsApp',
                'is_default' => false,
            ],
            [
                'name' => 'email_notifications',
                'label' => 'Notificação por Email',
                'is_default' => false,
            ],
            [
                'name' => 'finance',
                'label' => 'Módulo Financeiro',
                'is_default' => false,
            ],
        ];

        foreach ($features as $feature) {
            SubscriptionFeature::updateOrCreate(
                ['name' => $feature['name']],
                [
                    'label' => $feature['label'],
                    'is_default' => $feature['is_default'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('✅ Funcionalidades criadas com sucesso!');
        $this->command->info('   - Funcionalidades essenciais: ' . count(array_filter($features, fn($f) => $f['is_default'])));
        $this->command->info('   - Funcionalidades opcionais: ' . count(array_filter($features, fn($f) => !$f['is_default'])));
    }
}

