<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Integrations;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Exibe a página de configurações
     */
    public function index()
    {
        $settings = [
            // Geral
            'timezone' => TenantSetting::get('timezone', config('app.timezone', 'America/Sao_Paulo')),
            'date_format' => TenantSetting::get('date_format', 'd/m/Y'),
            'time_format' => TenantSetting::get('time_format', 'H:i'),
            'language' => TenantSetting::get('language', 'pt_BR'),
            
            // Agendamentos
            'appointments.default_duration' => TenantSetting::get('appointments.default_duration', '30'),
            'appointments.interval_between' => TenantSetting::get('appointments.interval_between', '0'),
            'appointments.auto_confirm' => TenantSetting::isEnabled('appointments.auto_confirm'),
            'appointments.allow_cancellation' => TenantSetting::isEnabled('appointments.allow_cancellation'),
            'appointments.cancellation_hours' => TenantSetting::get('appointments.cancellation_hours', '24'),
            'appointments.reminder_hours' => TenantSetting::get('appointments.reminder_hours', '24'),
            
            // Calendário
            'calendar.default_start_time' => TenantSetting::get('calendar.default_start_time', '08:00'),
            'calendar.default_end_time' => TenantSetting::get('calendar.default_end_time', '18:00'),
            'calendar.default_weekdays' => TenantSetting::get('calendar.default_weekdays', '1,2,3,4,5'), // Segunda a Sexta
            'calendar.show_weekends' => TenantSetting::isEnabled('calendar.show_weekends'),
            
            // Notificações
            'notifications.appointments.enabled' => TenantSetting::isEnabled('notifications.appointments.enabled'),
            'notifications.form_responses.enabled' => TenantSetting::isEnabled('notifications.form_responses.enabled'),
            'notifications.email.enabled' => TenantSetting::isEnabled('notifications.email.enabled'),
            'notifications.whatsapp.enabled' => TenantSetting::isEnabled('notifications.whatsapp.enabled'),
            
            // Integrações
            'integrations.google_calendar.enabled' => TenantSetting::isEnabled('integrations.google_calendar.enabled'),
            'integrations.google_calendar.auto_sync' => TenantSetting::isEnabled('integrations.google_calendar.auto_sync'),
        ];

        // Buscar integrações ativas
        $integrations = Integrations::where('is_enabled', true)->get();
        
        // Verificar se Google Calendar está cadastrado e configurado
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        
        // Considera válida se existe, está habilitada e tem config não vazio
        $hasGoogleCalendarIntegration = false;
        
        if ($googleCalendarIntegration) {
            $hasConfig = false;
            if ($googleCalendarIntegration->config) {
                $config = $googleCalendarIntegration->config;
                if (is_array($config)) {
                    $hasConfig = !empty($config);
                } elseif (is_string($config)) {
                    $hasConfig = !empty(trim($config));
                } else {
                    $hasConfig = !empty($config);
                }
            }
            
            $hasGoogleCalendarIntegration = $googleCalendarIntegration->is_enabled && $hasConfig;
        }

        return view('tenant.settings.index', compact('settings', 'integrations', 'hasGoogleCalendarIntegration', 'googleCalendarIntegration'));
    }

    /**
     * Atualiza as configurações gerais
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'timezone' => 'required|string',
            'date_format' => 'required|string|in:d/m/Y,Y-m-d,m/d/Y',
            'time_format' => 'required|string|in:H:i,h:i A',
            'language' => 'required|string|in:pt_BR,en_US,es_ES',
        ]);

        TenantSetting::set('timezone', $request->timezone);
        TenantSetting::set('date_format', $request->date_format);
        TenantSetting::set('time_format', $request->time_format);
        TenantSetting::set('language', $request->language);

        return redirect()->route('tenant.settings.index')
            ->with('success', 'Configurações gerais atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de agendamentos
     */
    public function updateAppointments(Request $request)
    {
        $request->validate([
            'appointments_default_duration' => 'required|integer|min:15|max:480',
            'appointments_interval_between' => 'nullable|integer|min:0|max:60',
            'appointments_auto_confirm' => 'boolean',
            'appointments_allow_cancellation' => 'boolean',
            'appointments_cancellation_hours' => 'nullable|integer|min:1',
            'appointments_reminder_hours' => 'nullable|integer|min:1|max:168',
        ]);

        TenantSetting::set('appointments.default_duration', $request->appointments_default_duration);
        TenantSetting::set('appointments.interval_between', $request->appointments_interval_between ?? 0);
        
        if ($request->has('appointments_auto_confirm')) {
            TenantSetting::enable('appointments.auto_confirm');
        } else {
            TenantSetting::disable('appointments.auto_confirm');
        }

        if ($request->has('appointments_allow_cancellation')) {
            TenantSetting::enable('appointments.allow_cancellation');
            TenantSetting::set('appointments.cancellation_hours', $request->appointments_cancellation_hours ?? 24);
        } else {
            TenantSetting::disable('appointments.allow_cancellation');
        }

        TenantSetting::set('appointments.reminder_hours', $request->appointments_reminder_hours ?? 24);

        return redirect()->route('tenant.settings.index')
            ->with('success', 'Configurações de agendamentos atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de calendário
     */
    public function updateCalendar(Request $request)
    {
        $request->validate([
            'calendar_default_start_time' => 'required|date_format:H:i',
            'calendar_default_end_time' => 'required|date_format:H:i',
            'calendar_default_weekdays' => 'required|string',
            'calendar_show_weekends' => 'boolean',
        ]);

        // Valida se o horário de término é depois do início
        if (strtotime($request->calendar_default_end_time) <= strtotime($request->calendar_default_start_time)) {
            return redirect()->route('tenant.settings.index')
                ->with('error', 'O horário de término deve ser posterior ao horário de início.');
        }

        TenantSetting::set('calendar.default_start_time', $request->calendar_default_start_time);
        TenantSetting::set('calendar.default_end_time', $request->calendar_default_end_time);
        TenantSetting::set('calendar.default_weekdays', $request->calendar_default_weekdays);
        
        if ($request->has('calendar_show_weekends')) {
            TenantSetting::enable('calendar.show_weekends');
        } else {
            TenantSetting::disable('calendar.show_weekends');
        }

        return redirect()->route('tenant.settings.index')
            ->with('success', 'Configurações de calendário atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de notificações
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'notifications_appointments_enabled' => 'boolean',
            'notifications_form_responses_enabled' => 'boolean',
            'notifications_email_enabled' => 'boolean',
            'notifications_whatsapp_enabled' => 'boolean',
        ]);

        if ($request->has('notifications_appointments_enabled')) {
            TenantSetting::enable('notifications.appointments.enabled');
        } else {
            TenantSetting::disable('notifications.appointments.enabled');
        }

        if ($request->has('notifications_form_responses_enabled')) {
            TenantSetting::enable('notifications.form_responses.enabled');
        } else {
            TenantSetting::disable('notifications.form_responses.enabled');
        }

        if ($request->has('notifications_email_enabled')) {
            TenantSetting::enable('notifications.email.enabled');
        } else {
            TenantSetting::disable('notifications.email.enabled');
        }

        if ($request->has('notifications_whatsapp_enabled')) {
            TenantSetting::enable('notifications.whatsapp.enabled');
        } else {
            TenantSetting::disable('notifications.whatsapp.enabled');
        }

        return redirect()->route('tenant.settings.index')
            ->with('success', 'Configurações de notificações atualizadas com sucesso.');
    }

    /**
     * Atualiza as configurações de integrações
     */
    public function updateIntegrations(Request $request)
    {
        $request->validate([
            'integrations_google_calendar_enabled' => 'boolean',
            'integrations_google_calendar_auto_sync' => 'boolean',
        ]);

        // Verificar se Google Calendar está cadastrado antes de permitir habilitar
        $googleCalendarIntegration = Integrations::where('key', 'google_calendar')->first();
        
        if ($request->has('integrations_google_calendar_enabled')) {
            // Verificar se a integração está cadastrada e configurada
            if (!$googleCalendarIntegration || !$googleCalendarIntegration->is_enabled || empty($googleCalendarIntegration->config)) {
                return redirect()->route('tenant.settings.index')
                    ->with('error', 'Não é possível habilitar o Google Calendar. Cadastre primeiro a integração em Integrações com a chave "google_calendar" e configure a API.');
            }
            
            TenantSetting::enable('integrations.google_calendar.enabled');
            
            if ($request->has('integrations_google_calendar_auto_sync')) {
                TenantSetting::enable('integrations.google_calendar.auto_sync');
            } else {
                TenantSetting::disable('integrations.google_calendar.auto_sync');
            }
        } else {
            TenantSetting::disable('integrations.google_calendar.enabled');
            TenantSetting::disable('integrations.google_calendar.auto_sync');
        }

        return redirect()->route('tenant.settings.index')
            ->with('success', 'Configurações de integrações atualizadas com sucesso.');
    }
}

