<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\OnlineAppointmentInstruction;
use App\Models\Tenant\TenantSetting;
use App\Services\MailTenantService;
use App\Services\WhatsappTenantService;
use App\Mail\FormToFillMail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OnlineAppointmentController extends Controller
{
    use HasDoctorFilter;
    /**
     * Lista apenas agendamentos online
     */
    public function index()
    {
        // Verificar se o modo permite acesso ao módulo
        $mode = TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            abort(404);
        }

        $query = Appointment::where('appointment_mode', 'online')
            ->with(['patient', 'calendar.doctor.user', 'type', 'specialty', 'onlineInstructions']);
        
        // Aplicar filtro de médico
        $this->applyDoctorFilterWhereHas($query, 'calendar', 'doctor_id');

        $appointments = $query->latest('starts_at')->paginate(20);

        return view('tenant.online_appointments.index', compact('appointments'));
    }

    /**
     * Exibe formulário para configurar instruções
     */
    public function show($slug, $id)
    {
        // Verificar se o modo permite acesso ao módulo
        $mode = TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            abort(404);
        }

        $appointment = Appointment::with(['patient', 'calendar.doctor.user', 'type', 'specialty', 'onlineInstructions'])
            ->findOrFail($id);

        // Verificar se é agendamento online
        if ($appointment->appointment_mode !== 'online') {
            abort(403, 'Esta consulta não é online.');
        }

        // Criar instruções vazias se não existir
        if (!$appointment->onlineInstructions) {
            OnlineAppointmentInstruction::create([
                'id' => Str::uuid(),
                'appointment_id' => $appointment->id,
            ]);
            $appointment->refresh();
            $appointment->load('onlineInstructions');
        }

        // Verificar configurações de notificação
        $settings = TenantSetting::getAll();
        $canSendEmail = ($settings['notifications.send_email_to_patients'] ?? false) === 'true' || 
                        ($settings['notifications.send_email_to_patients'] ?? false) === true;
        $canSendWhatsapp = ($settings['notifications.send_whatsapp_to_patients'] ?? false) === 'true' || 
                           ($settings['notifications.send_whatsapp_to_patients'] ?? false) === true;

        return view('tenant.online_appointments.show', compact('appointment', 'canSendEmail', 'canSendWhatsapp'));
    }

    /**
     * Salva as instruções
     */
    public function save(Request $request, $slug, $id)
    {
        // Verificar se o modo permite acesso ao módulo
        $mode = TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            abort(404);
        }

        $appointment = Appointment::findOrFail($id);

        if ($appointment->appointment_mode !== 'online') {
            abort(403, 'Esta consulta não é online.');
        }

        $request->validate([
            'meeting_link' => 'nullable|url',
            'meeting_app' => 'nullable|string|max:255',
            'general_instructions' => 'nullable|string',
            'patient_instructions' => 'nullable|string',
        ]);

        $instructions = $appointment->onlineInstructions;
        
        if (!$instructions) {
            $instructions = OnlineAppointmentInstruction::create([
                'id' => Str::uuid(),
                'appointment_id' => $appointment->id,
            ]);
        }

        $instructions->update([
            'meeting_link' => $request->meeting_link,
            'meeting_app' => $request->meeting_app,
            'general_instructions' => $request->general_instructions,
            'patient_instructions' => $request->patient_instructions,
        ]);

        return redirect()->route('tenant.online-appointments.show', $id)
            ->with('success', 'Instruções salvas com sucesso.');
    }

    /**
     * Envia instruções por email
     */
    public function sendEmail(Request $request, $slug, $id)
    {
        $appointment = Appointment::with(['patient', 'onlineInstructions'])
            ->findOrFail($id);

        if ($appointment->appointment_mode !== 'online') {
            abort(403, 'Esta consulta não é online.');
        }

        // Verificar configuração
        $settings = TenantSetting::getAll();
        $canSendEmail = ($settings['notifications.send_email_to_patients'] ?? false) === 'true' || 
                        ($settings['notifications.send_email_to_patients'] ?? false) === true;

        if (!$canSendEmail) {
            return redirect()->back()
                ->with('error', 'Envio de email aos pacientes está desabilitado nas configurações.');
        }

        if (!$appointment->patient->email) {
            return redirect()->back()
                ->with('error', 'O paciente não possui email cadastrado.');
        }

        if (!$appointment->onlineInstructions) {
            return redirect()->back()
                ->with('error', 'Configure as instruções antes de enviar.');
        }

        try {
            // Criar email com instruções
            $instructions = $appointment->onlineInstructions;
            $data = [
                'patient_name' => $appointment->patient->full_name,
                'appointment_date' => $appointment->starts_at->format('d/m/Y'),
                'appointment_time' => $appointment->starts_at->format('H:i'),
                'meeting_link' => $instructions->meeting_link,
                'meeting_app' => $instructions->meeting_app,
                'general_instructions' => $instructions->general_instructions,
                'patient_instructions' => $instructions->patient_instructions,
            ];

            MailTenantService::send(
                $appointment->patient->email,
                'Instruções para Consulta Online - ' . $appointment->starts_at->format('d/m/Y'),
                'emails.online-appointment-instructions',
                $data
            );

            // Atualizar timestamp
            $instructions->update([
                'sent_by_email_at' => now(),
            ]);

            return redirect()->route('tenant.online-appointments.show', $id)
                ->with('success', 'Instruções enviadas por email com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao enviar instruções por email', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Envia instruções por WhatsApp
     */
    public function sendWhatsapp(Request $request, $slug, $id)
    {
        // Verificar se o modo permite acesso ao módulo
        $mode = TenantSetting::get('appointments.default_appointment_mode', 'user_choice');
        if ($mode === 'presencial') {
            abort(404);
        }

        $appointment = Appointment::with(['patient', 'onlineInstructions'])
            ->findOrFail($id);

        if ($appointment->appointment_mode !== 'online') {
            abort(403, 'Esta consulta não é online.');
        }

        // Verificar configuração
        $settings = TenantSetting::getAll();
        $canSendWhatsapp = ($settings['notifications.send_whatsapp_to_patients'] ?? false) === 'true' || 
                           ($settings['notifications.send_whatsapp_to_patients'] ?? false) === true;

        if (!$canSendWhatsapp) {
            return redirect()->back()
                ->with('error', 'Envio de WhatsApp aos pacientes está desabilitado nas configurações.');
        }

        if (!$appointment->patient->phone) {
            return redirect()->back()
                ->with('error', 'O paciente não possui telefone cadastrado.');
        }

        if (!$appointment->onlineInstructions) {
            return redirect()->back()
                ->with('error', 'Configure as instruções antes de enviar.');
        }

        try {
            $instructions = $appointment->onlineInstructions;
            
            // Montar mensagem
            $message = "Olá {$appointment->patient->full_name},\n\n";
            $message .= "Sua consulta ONLINE foi agendada para {$appointment->starts_at->format('d/m/Y')} às {$appointment->starts_at->format('H:i')}.\n\n";
            
            if ($instructions->meeting_link) {
                $message .= "Link da reunião:\n{$instructions->meeting_link}\n\n";
            }
            
            if ($instructions->meeting_app) {
                $message .= "Aplicativo:\n{$instructions->meeting_app}\n\n";
            }
            
            if ($instructions->general_instructions) {
                $message .= "Instruções:\n{$instructions->general_instructions}\n\n";
            }
            
            if ($instructions->patient_instructions) {
                $message .= "Observações:\n{$instructions->patient_instructions}\n";
            }

            $sent = WhatsappTenantService::send($appointment->patient->phone, $message);

            if ($sent) {
                // Atualizar timestamp
                $instructions->update([
                    'sent_by_whatsapp_at' => now(),
                ]);

                return redirect()->route('tenant.online-appointments.show', $id)
                    ->with('success', 'Instruções enviadas por WhatsApp com sucesso.');
            } else {
                return redirect()->back()
                    ->with('error', 'Erro ao enviar WhatsApp. Verifique as configurações.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar instruções por WhatsApp', [
                'appointment_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao enviar WhatsApp: ' . $e->getMessage());
        }
    }
}

