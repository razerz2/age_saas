<?php

namespace App\Jobs\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Appointment;
use App\Services\TenantNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class SendAppointmentNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries = 3;

    protected string $tenantId;
    protected string $appointmentId;
    protected string $action;
    protected ?array $metadata;

    public function __construct(string $tenantId, string $appointmentId, string $action, ?array $metadata = null)
    {
        $this->tenantId = $tenantId;
        $this->appointmentId = $appointmentId;
        $this->action = $action;
        $this->metadata = $metadata;
    }

    public function handle(): void
    {
        $queueConnection = (string) config('queue.default', 'sync');
        $queueName = (string) config("queue.connections.{$queueConnection}.queue", 'default');

        Log::info('📥 Appointment notification job processing', [
            'tenant_id' => $this->tenantId,
            'appointment_id' => $this->appointmentId,
            'action' => $this->action,
            'queue_connection' => $queueConnection,
            'queue' => $queueName,
            'attempt' => $this->job?->attempts(),
            'job_id' => $this->job?->getJobId(),
            'connection_name' => $this->job?->getConnectionName(),
        ]);

        $tenant = PlatformTenant::find($this->tenantId);
        if (!$tenant) {
            Log::error('Tenant não encontrado para job de notificação de agendamento', [
                'tenant_id' => $this->tenantId,
                'appointment_id' => $this->appointmentId,
                'action' => $this->action,
            ]);
            return;
        }

        $tenant->makeCurrent();

        try {
            $appointment = Appointment::with(['patient', 'calendar.doctor.user', 'specialty'])
                ->find($this->appointmentId);

            if (!$appointment) {
                Log::warning('Agendamento não encontrado para job de notificação', [
                    'tenant_id' => $this->tenantId,
                    'appointment_id' => $this->appointmentId,
                    'action' => $this->action,
                ]);
                return;
            }

            TenantNotificationService::notifyAppointment(
                $this->action,
                $appointment,
                $this->metadata
            );

            Log::info('✅ Appointment notification job completed', [
                'tenant_id' => $this->tenantId,
                'appointment_id' => $this->appointmentId,
                'action' => $this->action,
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao processar job de notificação de agendamento', [
                'tenant_id' => $this->tenantId,
                'appointment_id' => $this->appointmentId,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        } finally {
            SpatieTenant::forgetCurrent();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('❌ Job de notificação de agendamento falhou definitivamente', [
            'tenant_id' => $this->tenantId,
            'appointment_id' => $this->appointmentId,
            'action' => $this->action,
            'error' => $exception->getMessage(),
        ]);
    }
}
