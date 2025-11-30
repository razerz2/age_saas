<?php

namespace App\Services\Tenant;

use App\Models\Tenant\GoogleCalendarToken;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected Google_Client $client;
    protected Google_Service_Calendar $service;

    /**
     * Cria uma instância do cliente Google Calendar para um token específico
     */
    public function client(GoogleCalendarToken $token): Google_Client
    {
        $this->client = new Google_Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(route('google.callback'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope([
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events'
        ]);

        // Se o token está expirado, renova
        if ($token->isExpired() && $token->refresh_token) {
            $this->refreshAccessToken($token);
        }

        // Define o token de acesso
        $this->client->setAccessToken($token->access_token);

        $this->service = new Google_Service_Calendar($this->client);

        return $this->client;
    }

    /**
     * Sincroniza um agendamento com o Google Calendar
     */
    public function syncEvent(Appointment $appointment): bool
    {
        try {
            // Buscar o médico através do calendário
            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->googleCalendarToken;

            // Se o médico não tem token Google, não sincroniza
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            // ESTRATÉGIA: Para edição, deletar e criar novo (mais simples e confiável)
            // Se já existe google_event_id, deletar e criar novo ao invés de atualizar
            if ($appointment->google_event_id) {
                // Deletar evento antigo primeiro
                $this->deleteEventFromGoogle($appointment->google_event_id, $calendar->doctor);
                // Limpar ID para criar novo - SEM DISPARAR EVENTOS para evitar loop infinito
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update(['google_event_id' => null]);
                });
            }
            
            // Criar novo evento com informações atualizadas
            return $this->createEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar evento com Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cria um evento no Google Calendar
     */
    public function createEvent(Appointment $appointment): bool
    {
        try {
            // Carregar relacionamentos necessários para construir o evento
            $appointment->load([
                'patient',
                'calendar.doctor.user',
                'type',
                'specialty'
            ]);

            // IMPORTANTE: Verificar se já existe google_event_id antes de criar
            // Isso evita duplicação se o Observer for disparado múltiplas vezes
            // ESTRATÉGIA: Se já existe, deletar e criar novo (mais simples e confiável)
            if ($appointment->google_event_id) {
                // Verificar se o evento ainda existe no Google Calendar
                try {
                    $calendarId = 'primary';
                    $existingEvent = $this->service->events->get($calendarId, $appointment->google_event_id);
                    
                    Log::info('Evento já existe no Google Calendar, deletando para recriar', [
                        'appointment_id' => $appointment->id,
                        'google_event_id' => $appointment->google_event_id,
                    ]);
                    
                    // Se existe, deletar antes de criar novo
                    $calendar = $appointment->calendar;
                    if ($calendar && $calendar->doctor) {
                        $this->deleteEventFromGoogle($appointment->google_event_id, $calendar->doctor);
                    }
                    
                    // Limpar ID para criar novo - SEM DISPARAR EVENTOS para evitar loop infinito
                    $appointment->withoutEvents(function () use ($appointment) {
                        $appointment->update(['google_event_id' => null]);
                    });
                } catch (\Exception $e) {
                    // Evento não existe mais no Google Calendar, continuar para criar novo
                    Log::info('Evento não encontrado no Google Calendar, criando novo', [
                        'appointment_id' => $appointment->id,
                        'google_event_id' => $appointment->google_event_id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Limpar ID inválido - SEM DISPARAR EVENTOS para evitar loop infinito
                    $appointment->withoutEvents(function () use ($appointment) {
                        $appointment->update(['google_event_id' => null]);
                    });
                }
            }

            $event = $this->buildEvent($appointment);
            
            // Adicionar ID do agendamento como propriedade estendida para identificação
            $extendedProperties = new \Google_Service_Calendar_EventExtendedProperties();
            $extendedProperties->setPrivate([
                'appointment_id' => $appointment->id,
            ]);
            $event->setExtendedProperties($extendedProperties);
            
            $calendarId = 'primary';
            $createdEvent = $this->service->events->insert($calendarId, $event);

            // Salva o ID do evento no agendamento - SEM DISPARAR EVENTOS para evitar loop infinito
            $appointment->withoutEvents(function () use ($appointment, $createdEvent) {
                $appointment->update([
                    'google_event_id' => $createdEvent->getId(),
                ]);
            });

            Log::info('Evento criado no Google Calendar', [
                'appointment_id' => $appointment->id,
                'google_event_id' => $createdEvent->getId(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento no Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Atualiza um evento no Google Calendar
     */
    public function updateEvent(Appointment $appointment): bool
    {
        try {
            if (!$appointment->google_event_id) {
                return $this->createEvent($appointment);
            }

            // IMPORTANTE: Verificar se o evento ainda existe no Google Calendar
            // Se não existir, criar novo ao invés de tentar atualizar
            try {
                $calendarId = 'primary';
                $existingEvent = $this->service->events->get($calendarId, $appointment->google_event_id);
            } catch (\Exception $e) {
                // Evento não existe mais, criar novo
                Log::warning('Evento não encontrado no Google Calendar, criando novo ao invés de atualizar', [
                    'appointment_id' => $appointment->id,
                    'google_event_id' => $appointment->google_event_id,
                    'error' => $e->getMessage(),
                ]);
                
                // Limpar ID inválido e criar novo - SEM DISPARAR EVENTOS para evitar loop infinito
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update(['google_event_id' => null]);
                });
                return $this->createEvent($appointment);
            }

            $event = $this->buildEvent($appointment);
            
            // Garantir que as propriedades estendidas estão presentes
            $extendedProperties = new \Google_Service_Calendar_EventExtendedProperties();
            $extendedProperties->setPrivate([
                'appointment_id' => $appointment->id,
            ]);
            $event->setExtendedProperties($extendedProperties);
            
            $updatedEvent = $this->service->events->update($calendarId, $appointment->google_event_id, $event);

            Log::info('Evento atualizado no Google Calendar', [
                'appointment_id' => $appointment->id,
                'google_event_id' => $updatedEvent->getId(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar evento no Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove um evento do Google Calendar
     * Usado para cancelamento de agendamentos
     */
    public function deleteEvent(Appointment $appointment): bool
    {
        try {
            if (!$appointment->google_event_id) {
                return true; // Já não existe no Google
            }

            // Buscar o médico através do calendário
            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $googleEventId = $appointment->google_event_id;

            // Deletar do Google Calendar
            $deleted = $this->deleteEventFromGoogle($googleEventId, $doctor);

            if ($deleted) {
                // Remove o ID do evento do agendamento - SEM DISPARAR EVENTOS para evitar loop infinito
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update([
                        'google_event_id' => null,
                    ]);
                });

                Log::info('Evento removido do Google Calendar', [
                    'appointment_id' => $appointment->id,
                    'google_event_id' => $googleEventId,
                ]);
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erro ao remover evento do Google Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove um evento do Google Calendar (método auxiliar)
     * Usado tanto para cancelamento quanto para edição (deletar antes de recriar)
     */
    protected function deleteEventFromGoogle(string $googleEventId, Doctor $doctor): bool
    {
        try {
            $token = $doctor->googleCalendarToken;

            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            $calendarId = 'primary';
            $this->service->events->delete($calendarId, $googleEventId);

            return true;
        } catch (\Exception $e) {
            // Se evento não existe mais, considerar sucesso
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                Log::info('Evento já não existe no Google Calendar', [
                    'google_event_id' => $googleEventId,
                ]);
                return true;
            }

            Log::error('Erro ao remover evento do Google Calendar', [
                'google_event_id' => $googleEventId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Lista eventos do Google Calendar para um médico
     */
    public function listEvents($doctorId, $startDate = null, $endDate = null): array
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            $token = $doctor->googleCalendarToken;

            if (!$token) {
                return [];
            }

            // Inicializa o cliente
            $this->client($token);

            $calendarId = 'primary';
            $params = [
                'timeMin' => $startDate ? Carbon::parse($startDate)->toRfc3339String() : Carbon::now()->toRfc3339String(),
                'timeMax' => $endDate ? Carbon::parse($endDate)->toRfc3339String() : Carbon::now()->addMonth()->toRfc3339String(),
                'singleEvents' => true,
                'orderBy' => 'startTime',
            ];

            $events = $this->service->events->listEvents($calendarId, $params);

            $result = [];
            foreach ($events->getItems() as $event) {
                $start = $event->getStart()->getDateTime();
                $end = $event->getEnd()->getDateTime();

                $result[] = [
                    'id' => $event->getId(),
                    'title' => $event->getSummary() ?? 'Sem título',
                    'start' => $start,
                    'end' => $end,
                    'description' => $event->getDescription(),
                ];
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Erro ao listar eventos do Google Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Renova o token de acesso usando o refresh token
     */
    public function refreshAccessToken(GoogleCalendarToken $token): bool
    {
        try {
            if (!$token->refresh_token) {
                Log::warning('Tentativa de renovar token sem refresh_token', [
                    'token_id' => $token->id,
                ]);
                return false;
            }

            $this->client = new Google_Client();
            $this->client->setClientId(config('services.google.client_id'));
            $this->client->setClientSecret(config('services.google.client_secret'));
            $this->client->setRedirectUri(route('google.callback'));

            $this->client->refreshToken($token->refresh_token);

            $newAccessToken = $this->client->getAccessToken();

            // Atualiza o token no banco
            $token->update([
                'access_token' => $newAccessToken,
                'expires_at' => isset($newAccessToken['expires_in']) 
                    ? Carbon::now()->addSeconds($newAccessToken['expires_in'])
                    : Carbon::now()->addHour(),
            ]);

            Log::info('Token do Google Calendar renovado', [
                'token_id' => $token->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao renovar token do Google Calendar', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Constrói um objeto Google_Service_Calendar_Event a partir de um Appointment
     */
    protected function buildEvent(Appointment $appointment): Google_Service_Calendar_Event
    {
        // Garantir que os relacionamentos estão carregados
        if (!$appointment->relationLoaded('patient')) {
            $appointment->load('patient');
        }
        if (!$appointment->relationLoaded('calendar')) {
            $appointment->load('calendar.doctor.user');
        }
        if (!$appointment->relationLoaded('type')) {
            $appointment->load('type');
        }
        if (!$appointment->relationLoaded('specialty')) {
            $appointment->load('specialty');
        }

        $event = new Google_Service_Calendar_Event();

        // Título do evento - mais informativo
        $titleParts = [];
        if ($appointment->patient) {
            $titleParts[] = $appointment->patient->full_name;
        }
        if ($appointment->specialty) {
            $titleParts[] = $appointment->specialty->name;
        }
        if ($appointment->type) {
            $titleParts[] = $appointment->type->name;
        }
        
        $title = !empty($titleParts) ? implode(' - ', $titleParts) : 'Consulta';
        $event->setSummary($title);

        // Descrição completa e formatada
        $description = [];
        
        // Seção: Informações do Paciente
        if ($appointment->patient) {
            $description[] = "👤 PACIENTE";
            $description[] = "Nome: {$appointment->patient->full_name}";
            
            if ($appointment->patient->phone) {
                $description[] = "Telefone: {$appointment->patient->phone}";
            }
            if ($appointment->patient->email) {
                $description[] = "E-mail: {$appointment->patient->email}";
            }
            if ($appointment->patient->cpf) {
                $description[] = "CPF: {$appointment->patient->cpf}";
            }
            $description[] = ""; // Linha em branco
        }

        // Seção: Informações da Consulta
        $description[] = "📅 CONSULTA";
        $description[] = "Data: {$appointment->starts_at->format('d/m/Y')}";
        $description[] = "Horário: {$appointment->starts_at->format('H:i')} - {$appointment->ends_at->format('H:i')}";
        
        // Duração calculada ou do tipo de consulta
        if ($appointment->type && $appointment->type->duration_min) {
            $description[] = "Duração: {$appointment->type->duration_min} minutos";
        } else {
            $durationMinutes = $appointment->starts_at->diffInMinutes($appointment->ends_at);
            $description[] = "Duração: {$durationMinutes} minutos";
        }
        
        if ($appointment->specialty) {
            $description[] = "Especialidade: {$appointment->specialty->name}";
        }
        if ($appointment->type) {
            $description[] = "Tipo de Consulta: {$appointment->type->name}";
        }
        
        $statusMap = [
            'scheduled' => 'Agendado',
            'rescheduled' => 'Reagendado',
            'canceled' => 'Cancelado',
            'attended' => 'Atendido',
            'no_show' => 'Não Compareceu'
        ];
        $statusTranslated = $statusMap[$appointment->status] ?? $appointment->status;
        $description[] = "Status: {$statusTranslated}";
        $description[] = ""; // Linha em branco

        // Seção: Informações do Médico
        if ($appointment->calendar && $appointment->calendar->doctor) {
            $doctor = $appointment->calendar->doctor;
            $description[] = "👨‍⚕️ MÉDICO";
            
            if ($doctor->user) {
                $description[] = "Nome: " . ($doctor->user->name_full ?? $doctor->user->name);
            }
            
            if ($doctor->crm_number && $doctor->crm_state) {
                $description[] = "CRM: {$doctor->crm_number}/{$doctor->crm_state}";
            } elseif ($doctor->crm_number) {
                $description[] = "CRM: {$doctor->crm_number}";
            }
            $description[] = ""; // Linha em branco
        }

        // Seção: Observações
        if ($appointment->notes) {
            $description[] = "📝 OBSERVAÇÕES";
            $description[] = $appointment->notes;
            $description[] = ""; // Linha em branco
        }

        // Seção: Informações Técnicas (ocultas)
        $description[] = "---";
        $description[] = "ID do Agendamento: {$appointment->id}";

        $event->setDescription(implode("\n", $description));

        // Data e hora de início
        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($appointment->starts_at->setTimezone('America/Sao_Paulo')->toRfc3339String());
        $start->setTimeZone('America/Sao_Paulo');
        $event->setStart($start);

        // Data e hora de fim
        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($appointment->ends_at->setTimezone('America/Sao_Paulo')->toRfc3339String());
        $end->setTimeZone('America/Sao_Paulo');
        $event->setEnd($end);

        return $event;
    }

    /**
     * Sincroniza uma recorrência como evento recorrente no Google Calendar
     * 
     * IMPORTANTE: Para recorrências sem data fim, usa uma data fim padrão de 1 ano
     * para evitar criação infinita de eventos. O evento pode ser renovado manualmente.
     */
    public function syncRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            // Buscar o médico
            $doctor = $recurring->doctor;
            if (!$doctor) {
                return false;
            }

            $token = $doctor->googleCalendarToken;
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            // Buscar calendário do médico
            $calendar = $doctor->calendars()->first();
            if (!$calendar) {
                return false;
            }

            // Carregar regras e relacionamentos
            $recurring->load(['rules', 'patient', 'appointmentType']);

            if ($recurring->rules->isEmpty()) {
                Log::warning('Recorrência sem regras não pode ser sincronizada', [
                    'recurring_id' => $recurring->id,
                ]);
                return false;
            }

            // Para cada regra, criar um evento recorrente
            // (Uma recorrência pode ter múltiplas regras - ex: segunda e quarta)
            // ESTRATÉGIA: Se já existe evento, deletar antes de criar novo (mais simples e confiável)
            foreach ($recurring->rules as $rule) {
                // IMPORTANTE: Verificar se já existe evento para esta regra
                $existingEventId = $recurring->getGoogleRecurringEventId($rule->id);
                
                if ($existingEventId) {
                    // Se existe, deletar antes de criar novo (estratégia de edição)
                    try {
                        $calendarId = 'primary';
                        $existingEvent = $this->service->events->get($calendarId, $existingEventId);
                        
                        // Evento existe, deletar antes de criar novo
                        Log::info('Evento recorrente já existe, deletando para recriar', [
                            'recurring_id' => $recurring->id,
                            'rule_id' => $rule->id,
                            'google_event_id' => $existingEventId,
                        ]);
                        
                        $this->service->events->delete($calendarId, $existingEventId);
                        
                        // Limpar ID para criar novo
                        $eventIds = $recurring->google_recurring_event_ids ?? [];
                        unset($eventIds[$rule->id]);
                        $recurring->update(['google_recurring_event_ids' => $eventIds]);
                    } catch (\Exception $e) {
                        // Evento não existe mais no Google Calendar, apenas limpar ID
                        Log::info('Evento recorrente não encontrado no Google Calendar, criando novo', [
                            'recurring_id' => $recurring->id,
                            'rule_id' => $rule->id,
                            'google_event_id' => $existingEventId,
                            'error' => $e->getMessage(),
                        ]);
                        
                        // Limpar ID inválido
                        $eventIds = $recurring->google_recurring_event_ids ?? [];
                        unset($eventIds[$rule->id]);
                        $recurring->update(['google_recurring_event_ids' => $eventIds]);
                    }
                }
                
                // Criar novo evento (ou recriar após deletar)
                $this->createRecurringEventForRule($recurring, $rule, $calendar);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar recorrência com Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cria um evento recorrente no Google Calendar para uma regra específica
     */
    protected function createRecurringEventForRule(
        RecurringAppointment $recurring,
        RecurringAppointmentRule $rule,
        $calendar
    ): bool {
        try {
            $event = new Google_Service_Calendar_Event();

            // Título do evento
            $title = $recurring->patient 
                ? "Consulta Recorrente - {$recurring->patient->full_name}"
                : 'Consulta Recorrente';
            
            if ($recurring->appointmentType) {
                $title .= " - {$recurring->appointmentType->name}";
            }

            $event->setSummary($title);

            // Descrição
            $description = [];
            if ($recurring->patient) {
                $description[] = "Paciente: {$recurring->patient->full_name}";
            }
            $description[] = "Agendamento Recorrente";
            // Adicionar ID da recorrência e regra na descrição para identificação
            $description[] = "RecurringAppointment ID: {$recurring->id}";
            $description[] = "Rule ID: {$rule->id}";
            $event->setDescription(implode("\n", $description));
            
            // Adicionar ID da recorrência como extended property para identificação programática
            $extendedProperties = new \Google_Service_Calendar_EventExtendedProperties();
            $extendedProperties->setPrivate([
                'recurring_appointment_id' => $recurring->id,
                'rule_id' => $rule->id,
            ]);
            $event->setExtendedProperties($extendedProperties);

            // Data e hora de início (primeira ocorrência)
            $startDate = Carbon::parse($recurring->start_date);
            $startDateTime = Carbon::parse($startDate->format('Y-m-d') . ' ' . $rule->start_time);
            $endDateTime = Carbon::parse($startDate->format('Y-m-d') . ' ' . $rule->end_time);

            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($startDateTime->setTimezone('America/Sao_Paulo')->toRfc3339String());
            $start->setTimeZone('America/Sao_Paulo');
            $event->setStart($start);

            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($endDateTime->setTimezone('America/Sao_Paulo')->toRfc3339String());
            $end->setTimeZone('America/Sao_Paulo');
            $event->setEnd($end);

            // Configurar recorrência (RRULE)
            $rrule = $this->buildRRule($recurring, $rule);
            if ($rrule) {
                // O Google Calendar API aceita RRULE diretamente como array
                $event->setRecurrence([$rrule]);
            }

            // Criar evento no Google Calendar
            $calendarId = 'primary';
            $createdEvent = $this->service->events->insert($calendarId, $event);

            $googleEventId = $createdEvent->getId();

            // Armazenar o ID do evento recorrente na recorrência
            $recurring->setGoogleRecurringEventId($rule->id, $googleEventId);

            Log::info('Evento recorrente criado no Google Calendar', [
                'recurring_id' => $recurring->id,
                'rule_id' => $rule->id,
                'google_event_id' => $googleEventId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento recorrente no Google Calendar', [
                'recurring_id' => $recurring->id,
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Constrói a regra RRULE para o Google Calendar
     * 
     * IMPORTANTE: Para recorrências sem data fim, define uma data fim padrão de 1 ano
     * para evitar criação infinita de eventos.
     */
    protected function buildRRule(RecurringAppointment $recurring, RecurringAppointmentRule $rule): ?string
    {
        // Converter weekday para formato RRULE (MO, TU, WE, etc)
        $weekdayMap = [
            'monday' => 'MO',
            'tuesday' => 'TU',
            'wednesday' => 'WE',
            'thursday' => 'TH',
            'friday' => 'FR',
            'saturday' => 'SA',
            'sunday' => 'SU',
        ];

        $weekday = strtoupper($weekdayMap[$rule->weekday] ?? 'MO');
        $frequency = strtoupper($rule->frequency ?? 'WEEKLY');
        $interval = $rule->interval ?? 1;

        // Calcular data fim
        $endDate = null;
        if ($recurring->end_type === 'date' && $recurring->end_date) {
            $endDate = Carbon::parse($recurring->end_date);
        } elseif ($recurring->end_type === 'total_sessions' && $recurring->total_sessions) {
            // Para total de sessões, calcular data aproximada
            // Assumindo 1 sessão por semana (pode ser ajustado)
            $weeks = ceil($recurring->total_sessions / 1); // Ajustar se houver múltiplas regras
            $endDate = Carbon::parse($recurring->start_date)->addWeeks($weeks);
        } else {
            // IMPORTANTE: Para recorrências sem data fim, usar data fim padrão de 1 ano
            // Isso evita criação infinita de eventos no Google Calendar
            $endDate = Carbon::parse($recurring->start_date)->addYear();
        }

        // Construir RRULE
        $rrule = "FREQ={$frequency};INTERVAL={$interval};BYDAY={$weekday}";
        
        if ($endDate) {
            $rrule .= ";UNTIL=" . $endDate->format('Ymd\THis\Z');
        }

        return $rrule;
    }

    /**
     * Cancela uma recorrência no Google Calendar
     * Atualiza a data fim para hoje, mantendo eventos passados como histórico
     * e removendo apenas eventos futuros
     * 
     * Funciona para TODOS os tipos de recorrência:
     * - Com data fim (end_type = 'date'): atualiza para terminar hoje
     * - Com número de sessões (end_type = 'total_sessions'): atualiza para terminar hoje
     * - Sem data fim (end_type = 'none'): atualiza para terminar hoje
     */
    public function cancelRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            $doctor = $recurring->doctor;
            if (!$doctor) {
                return false;
            }

            $token = $doctor->googleCalendarToken;
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            $calendarId = 'primary';
            $recurring->load(['rules', 'patient', 'appointmentType']);

            // Para cada regra, atualizar o evento recorrente para terminar hoje
            // IMPORTANTE: Funciona para qualquer tipo de recorrência (com ou sem data fim)
            foreach ($recurring->rules as $rule) {
                $googleEventId = $recurring->getGoogleRecurringEventId($rule->id);
                
                if (!$googleEventId) {
                    continue; // Não há evento para cancelar
                }

                try {
                    // Buscar evento atual
                    $event = $this->service->events->get($calendarId, $googleEventId);
                    
                    // Atualizar RRULE para terminar hoje (mantém eventos passados, remove futuros)
                    // Funciona independente do end_type original (date, total_sessions, ou none)
                    $today = Carbon::now();
                    $rrule = $this->buildRRuleWithEndDate($recurring, $rule, $today);
                    
                    if ($rrule) {
                        $event->setRecurrence([$rrule]);
                        
                        // Atualizar evento
                        $updatedEvent = $this->service->events->update($calendarId, $googleEventId, $event);
                        
                        Log::info('Recorrência cancelada no Google Calendar (eventos futuros removidos, passados mantidos)', [
                            'recurring_id' => $recurring->id,
                            'rule_id' => $rule->id,
                            'google_event_id' => $googleEventId,
                            'end_date' => $today->format('Y-m-d'),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao cancelar evento recorrente no Google Calendar', [
                        'recurring_id' => $recurring->id,
                        'rule_id' => $rule->id,
                        'google_event_id' => $googleEventId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar recorrência no Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove evento recorrente do Google Calendar completamente
     * Usado apenas quando a recorrência é deletada permanentemente
     */
    public function deleteRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            $doctor = $recurring->doctor;
            if (!$doctor) {
                return false;
            }

            $token = $doctor->googleCalendarToken;
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            $calendarId = 'primary';
            $eventIds = $recurring->getGoogleRecurringEventIds();

            foreach ($eventIds as $ruleId => $googleEventId) {
                try {
                    $this->service->events->delete($calendarId, $googleEventId);
                    Log::info('Evento recorrente removido do Google Calendar', [
                        'recurring_id' => $recurring->id,
                        'rule_id' => $ruleId,
                        'google_event_id' => $googleEventId,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Erro ao remover evento recorrente do Google Calendar', [
                        'recurring_id' => $recurring->id,
                        'rule_id' => $ruleId,
                        'google_event_id' => $googleEventId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Limpar IDs armazenados
            $recurring->update(['google_recurring_event_ids' => null]);

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao remover eventos recorrentes do Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Renova um evento recorrente no Google Calendar estendendo a data fim
     * Usado para recorrências sem data fim que estão próximas do fim
     */
    public function renewRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            // Só renova se a recorrência não tiver data fim
            if ($recurring->end_type !== 'none') {
                return false;
            }

            $doctor = $recurring->doctor;
            if (!$doctor) {
                return false;
            }

            $token = $doctor->googleCalendarToken;
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            $calendarId = 'primary';
            $recurring->load(['rules', 'patient', 'appointmentType']);

            // Para cada regra, atualizar o evento recorrente
            foreach ($recurring->rules as $rule) {
                $googleEventId = $recurring->getGoogleRecurringEventId($rule->id);
                
                if (!$googleEventId) {
                    // Se não existe evento, criar um novo
                    $calendar = $doctor->calendars()->first();
                    if ($calendar) {
                        $this->createRecurringEventForRule($recurring, $rule, $calendar);
                    }
                    continue;
                }

                try {
                    // Buscar evento atual
                    $event = $this->service->events->get($calendarId, $googleEventId);
                    
                    // Reconstruir RRULE com nova data fim (mais 1 ano a partir de hoje)
                    $newEndDate = Carbon::now()->addYear();
                    $rrule = $this->buildRRuleWithEndDate($recurring, $rule, $newEndDate);
                    
                    if ($rrule) {
                        $event->setRecurrence([$rrule]);
                        
                        // Atualizar evento
                        $updatedEvent = $this->service->events->update($calendarId, $googleEventId, $event);
                        
                        Log::info('Evento recorrente renovado no Google Calendar', [
                            'recurring_id' => $recurring->id,
                            'rule_id' => $rule->id,
                            'google_event_id' => $googleEventId,
                            'new_end_date' => $newEndDate->format('Y-m-d'),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao renovar evento recorrente no Google Calendar', [
                        'recurring_id' => $recurring->id,
                        'rule_id' => $rule->id,
                        'google_event_id' => $googleEventId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao renovar recorrência no Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Constrói RRULE com data fim específica
     */
    protected function buildRRuleWithEndDate(
        RecurringAppointment $recurring,
        RecurringAppointmentRule $rule,
        Carbon $endDate
    ): string {
        $weekdayMap = [
            'monday' => 'MO',
            'tuesday' => 'TU',
            'wednesday' => 'WE',
            'thursday' => 'TH',
            'friday' => 'FR',
            'saturday' => 'SA',
            'sunday' => 'SU',
        ];

        $weekday = strtoupper($weekdayMap[$rule->weekday] ?? 'MO');
        $frequency = strtoupper($rule->frequency ?? 'WEEKLY');
        $interval = $rule->interval ?? 1;

        $rrule = "FREQ={$frequency};INTERVAL={$interval};BYDAY={$weekday}";
        $rrule .= ";UNTIL=" . $endDate->format('Ymd\THis\Z');

        return $rrule;
    }
}

