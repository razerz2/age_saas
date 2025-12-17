<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\TenantSetting;
use App\Services\MailTenantService;
use App\Services\WhatsappTenantService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotifyUpcomingAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:notify-upcoming';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia lembretes de agendamentos próximos aos pacientes via email e WhatsApp';

    /**
     * Execute the console command.
     * 
     * FLUXO DE EXECUÇÃO:
     * 1. Busca todos os tenants ativos no banco PLATFORM
     * 2. Para cada tenant:
     *    a. Inicializa o contexto do tenant (troca conexão de banco)
     *    b. Busca agendamentos no banco DO TENANT
     *    c. Envia notificações usando configurações DO TENANT
     *    d. Finaliza o contexto (volta para banco PLATFORM)
     * 3. Mostra resumo final
     */
    public function handle()
    {
        $this->info('🔔 Iniciando envio de lembretes de agendamentos...');

        $totalNotified = 0;
        $totalErrors = 0;

        // ============================================================
        // ETAPA 1: Busca todos os tenants ativos no banco PLATFORM
        // ============================================================
        // Neste momento, estamos conectados ao banco 'platform'
        // A tabela 'tenants' está no banco 'platform'
        $tenants = Tenant::where('status', 'active')->get();
        $this->info("📋 Encontrados {$tenants->count()} tenant(s) ativo(s)");

        // ============================================================
        // ETAPA 2: Itera sobre cada tenant
        // ============================================================
        foreach ($tenants as $tenant) {
            try {
                // ============================================================
                // ETAPA 2a: INICIALIZA O CONTEXTO DO TENANT
                // ============================================================
                // tenancy()->initialize($tenant) faz o seguinte:
                // 1. Ativa o tenant atual (Tenant::current() retorna este tenant)
                // 2. Troca a conexão de banco de dados:
                //    - ANTES: banco 'platform' (tabela: tenants, plans, etc)
                //    - DEPOIS: banco do tenant (ex: 'tenant_clinica_abc')
                // 3. Configura a conexão 'tenant' com as credenciais do tenant:
                //    - host: $tenant->db_host
                //    - database: $tenant->db_name
                //    - username: $tenant->db_username
                //    - password: $tenant->db_password
                //
                // IMPORTANTE: A partir deste ponto, todas as queries usando
                // models com 'connection' => 'tenant' vão para o banco DO TENANT
                // Exemplos: Appointment, Patient, TenantSetting, etc.
                tenancy()->initialize($tenant);

                // ============================================================
                // ETAPA 2b: VERIFICA CONFIGURAÇÕES DO TENANT
                // ============================================================
                // TenantSetting::get() busca no banco DO TENANT
                // Cada tenant tem suas próprias configurações
                if (!TenantSetting::isEnabled('notifications.send_email_to_patients') && 
                    !TenantSetting::isEnabled('notifications.send_whatsapp_to_patients')) {
                    $this->line("⏭️  Tenant {$tenant->trade_name}: Notificações desabilitadas");
                    tenancy()->end(); // Limpa contexto antes de continuar
                    continue;
                }

                // Obtém horas de lembrete configuradas (padrão: 24 horas)
                // Esta configuração é específica de cada tenant
                $reminderHours = (int) TenantSetting::get('appointments.reminder_hours', 24);

                // Calcula o horário alvo (agora + horas de lembrete)
                $targetTime = Carbon::now()->addHours($reminderHours);

                // ============================================================
                // ETAPA 2c: BUSCA AGENDAMENTOS NO BANCO DO TENANT
                // ============================================================
                // Appointment::with() busca no banco DO TENANT atual
                // Cada tenant tem sua própria tabela 'appointments'
                // Os agendamentos são isolados por tenant
                $appointments = Appointment::with(['patient', 'calendar.doctor.user', 'specialty'])
                    ->where('status', 'scheduled')
                    ->whereBetween('starts_at', [
                        $targetTime->copy()->subHour(),
                        $targetTime->copy()->addHour()
                    ])
                    ->whereDoesntHave('patient', function($query) {
                        $query->whereNull('email')->whereNull('phone');
                    })
                    ->get();

                if ($appointments->isEmpty()) {
                    $this->line("ℹ️  Tenant {$tenant->trade_name}: Nenhum agendamento próximo");
                    continue;
                }

                $tenantName = $tenant->trade_name ?? $tenant->legal_name;
                $notified = 0;
                $errors = 0;

                foreach ($appointments as $appointment) {
                    try {
                        $patient = $appointment->patient;
                        if (!$patient) {
                            continue;
                        }

                        $doctorName = $appointment->calendar->doctor->user->name ?? 'Dr(a).';
                        $specialtyName = $appointment->specialty->name ?? '';
                        $appointmentDate = $appointment->starts_at->format('d/m/Y');
                        $appointmentTime = $appointment->starts_at->format('H:i');
                        $appointmentMode = $appointment->appointment_mode === 'online' ? 'Online' : 'Presencial';
                        $hoursUntil = $appointment->starts_at->diffInHours(Carbon::now());

                        // Template de lembrete
                        $emailSubject = "Lembrete de Agendamento - {$tenantName}";
                        $emailBody = "Olá {$patient->full_name},\n\n" .
                            "Este é um lembrete do seu agendamento:\n\n" .
                            "📅 Data: {$appointmentDate}\n" .
                            "🕐 Horário: {$appointmentTime}\n" .
                            "👨‍⚕️ Profissional: {$doctorName}\n" .
                            ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                            "📍 Modalidade: {$appointmentMode}\n" .
                            "⏰ Faltam aproximadamente {$hoursUntil} hora(s)\n\n" .
                            "Não se esqueça!\n\n" .
                            "Atenciosamente,\n{$tenantName}";

                        $whatsappMessage = "Olá {$patient->full_name}! 👋\n\n" .
                            "🔔 *Lembrete de Agendamento*\n\n" .
                            "📅 Data: {$appointmentDate}\n" .
                            "🕐 Horário: {$appointmentTime}\n" .
                            "👨‍⚕️ Profissional: {$doctorName}\n" .
                            ($specialtyName ? "🏥 Especialidade: {$specialtyName}\n" : "") .
                            "📍 Modalidade: {$appointmentMode}\n" .
                            "⏰ Faltam aproximadamente {$hoursUntil} hora(s)\n\n" .
                            "Não se esqueça!\n\n" .
                            "Atenciosamente,\n{$tenantName}";

                        // ============================================================
                        // ETAPA 2d: ENVIA NOTIFICAÇÕES USANDO CONFIGURAÇÕES DO TENANT
                        // ============================================================
                        // MailTenantService e WhatsappTenantService usam as configurações
                        // específicas de cada tenant (email/WhatsApp configurados por tenant)
                        
                        // Enviar por email
                        if ($patient->email && TenantSetting::isEnabled('notifications.send_email_to_patients')) {
                            try {
                                // MailTenantService usa configurações de email DO TENANT
                                $emailService = app(MailTenantService::class);
                                $emailService->send(
                                    $patient->email,
                                    $emailSubject,
                                    $emailBody
                                );
                                $this->line("  ✓ Email enviado para {$patient->full_name}");
                            } catch (\Throwable $e) {
                                Log::error('Erro ao enviar lembrete por email', [
                                    'tenant_id' => $tenant->id,
                                    'appointment_id' => $appointment->id,
                                    'patient_id' => $patient->id,
                                    'error' => $e->getMessage(),
                                ]);
                                $errors++;
                            }
                        }

                        // Enviar por WhatsApp
                        if ($patient->phone && TenantSetting::isEnabled('notifications.send_whatsapp_to_patients')) {
                            try {
                                // WhatsappTenantService usa configurações de WhatsApp DO TENANT
                                $whatsappService = app(WhatsappTenantService::class);
                                $whatsappService->send(
                                    $patient->phone,
                                    $whatsappMessage
                                );
                                $this->line("  ✓ WhatsApp enviado para {$patient->full_name}");
                            } catch (\Throwable $e) {
                                Log::error('Erro ao enviar lembrete por WhatsApp', [
                                    'tenant_id' => $tenant->id,
                                    'appointment_id' => $appointment->id,
                                    'patient_id' => $patient->id,
                                    'error' => $e->getMessage(),
                                ]);
                                $errors++;
                            }
                        }

                        $notified++;
                        $totalNotified++;

                    } catch (\Throwable $e) {
                        Log::error('Erro ao processar lembrete de agendamento', [
                            'tenant_id' => $tenant->id,
                            'appointment_id' => $appointment->id,
                            'error' => $e->getMessage(),
                        ]);
                        $errors++;
                        $totalErrors++;
                    }
                }

                $this->info("✅ Tenant {$tenant->trade_name}: {$notified} lembretes enviados" . ($errors > 0 ? ", {$errors} erros" : ""));

            } catch (\Throwable $e) {
                Log::error('Erro ao processar tenant para lembretes', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("❌ Erro ao processar tenant {$tenant->trade_name}: " . $e->getMessage());
                $totalErrors++;
            } finally {
                // ============================================================
                // ETAPA 2e: FINALIZA O CONTEXTO DO TENANT
                // ============================================================
                // tenancy()->end() faz o seguinte:
                // 1. Desativa o tenant atual (Tenant::current() retorna null)
                // 2. Volta a conexão de banco para 'platform'
                // 3. Limpa todas as configurações do tenant
                //
                // IMPORTANTE: Sempre usar finally para garantir que o contexto
                // seja limpo mesmo em caso de erro, evitando "vazamento" de
                // contexto entre tenants
                tenancy()->end();
            }
        }

        $this->newLine();
        $this->info("📊 Resumo: {$totalNotified} lembretes enviados" . ($totalErrors > 0 ? ", {$totalErrors} erros" : ""));

        return Command::SUCCESS;
    }
}

