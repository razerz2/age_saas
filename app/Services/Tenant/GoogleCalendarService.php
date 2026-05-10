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
use Illuminate\Support\Str;

class GoogleCalendarService
{
    protected Google_Client $client;
    protected Google_Service_Calendar $service;

    /**
     * Cria uma instÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ncia do cliente Google Calendar para um token especÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­fico
     */
    public function client(GoogleCalendarToken $token): Google_Client
    {
        $oauthConfig = google_oauth_config();

        $this->client = new Google_Client();
        $this->client->setClientId((string) ($oauthConfig['client_id'] ?? ''));
        $this->client->setClientSecret((string) ($oauthConfig['client_secret'] ?? ''));
        $this->client->setRedirectUri((string) ($oauthConfig['redirect_uri'] ?? route('google.callback')));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->addScope(google_calendar_scopes());

        // Se o token estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ expirado, renova
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
            // Buscar o mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico atravÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©s do calendÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->googleCalendarToken;

            // Se o mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o tem token Google, nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o sincroniza
            if (!$token) {
                return false;
            }

            // Inicializa o cliente
            $this->client($token);

            // ESTRATÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°GIA: Para ediÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o, deletar e criar novo (mais simples e confiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡vel)
            // Se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe google_event_id, deletar e criar novo ao invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©s de atualizar
            if ($appointment->google_event_id) {
                // Deletar evento antigo primeiro
                $this->deleteEventFromGoogle($appointment->google_event_id, $calendar->doctor);
                // Limpar ID para criar novo - SEM DISPARAR EVENTOS para evitar loop infinito
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update(['google_event_id' => null]);
                });
            }
            
            // Criar novo evento com informaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes atualizadas
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
            // Carregar relacionamentos necessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rios para construir o evento
            $appointment->load([
                'patient',
                'calendar.doctor.user',
                'type',
                'specialty'
            ]);

            // IMPORTANTE: Verificar se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe google_event_id antes de criar
            // Isso evita duplicaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o se o Observer for disparado mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºltiplas vezes
            // ESTRATÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°GIA: Se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe, deletar e criar novo (mais simples e confiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡vel)
            if ($appointment->google_event_id) {
                // Verificar se o evento ainda existe no Google Calendar
                try {
                    $calendarId = 'primary';
                    $existingEvent = $this->service->events->get($calendarId, $appointment->google_event_id);
                    
                    Log::info('Evento jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe no Google Calendar, deletando para recriar', [
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
                    // Evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe mais no Google Calendar, continuar para criar novo
                    Log::info('Evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado no Google Calendar, criando novo', [
                        'appointment_id' => $appointment->id,
                        'google_event_id' => $appointment->google_event_id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Limpar ID invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido - SEM DISPARAR EVENTOS para evitar loop infinito
                    $appointment->withoutEvents(function () use ($appointment) {
                        $appointment->update(['google_event_id' => null]);
                    });
                }
            }

            $event = $this->buildEvent($appointment);
            
            // Adicionar ID do agendamento como propriedade estendida para identificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
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
            // Se nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existir, criar novo ao invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©s de tentar atualizar
            try {
                $calendarId = 'primary';
                $existingEvent = $this->service->events->get($calendarId, $appointment->google_event_id);
            } catch (\Exception $e) {
                // Evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe mais, criar novo
                Log::warning('Evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado no Google Calendar, criando novo ao invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©s de atualizar', [
                    'appointment_id' => $appointment->id,
                    'google_event_id' => $appointment->google_event_id,
                    'error' => $e->getMessage(),
                ]);
                
                // Limpar ID invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido e criar novo - SEM DISPARAR EVENTOS para evitar loop infinito
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update(['google_event_id' => null]);
                });
                return $this->createEvent($appointment);
            }

            $event = $this->buildEvent($appointment);
            
            // Garantir que as propriedades estendidas estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o presentes
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
                return true; // JÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe no Google
            }

            // Buscar o mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico atravÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©s do calendÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio
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
     * Cria evento com conferencia Google Meet.
     *
     * @return array{
     *   event_id:?string,
     *   meeting_link:?string,
     *   conference_id:?string,
     *   html_link:?string,
     *   raw:array<string,mixed>
     * }
     */
    public function createEventWithMeet(Appointment $appointment): array
    {
        $appointment->load([
            'patient',
            'calendar.doctor.user',
            'calendar.doctor.googleCalendarToken',
            'type',
            'specialty',
        ]);

        if ($appointment->google_event_id) {
            return $this->updateEventWithMeet($appointment);
        }

        $doctor = $appointment->calendar?->doctor;
        if (!$doctor || !$doctor->googleCalendarToken) {
            throw new \RuntimeException('Medico sem token Google Calendar conectado.');
        }

        $this->client($doctor->googleCalendarToken);

        $event = $this->buildEvent($appointment);
        $this->attachAppointmentExtendedProperties($event, $appointment);
        $event->setConferenceData($this->buildGoogleMeetConferenceData((string) $appointment->id));

        $calendarId = 'primary';
        $createdEvent = $this->service->events->insert($calendarId, $event, [
            'conferenceDataVersion' => 1,
        ]);

        $eventId = $createdEvent->getId();
        if ($eventId) {
            $appointment->withoutEvents(function () use ($appointment, $eventId) {
                if ($appointment->exists) {
                    $appointment->update(['google_event_id' => $eventId]);
                } else {
                    $appointment->google_event_id = $eventId;
                }
            });
        }

        return [
            'event_id' => $eventId,
            'meeting_link' => $this->extractMeetingLinkFromGoogleEvent($createdEvent),
            'conference_id' => $this->extractConferenceIdFromGoogleEvent($createdEvent),
            'html_link' => $createdEvent->getHtmlLink(),
            'raw' => $this->safeGoogleEventPayload($createdEvent),
        ];
    }

    /**
     * Atualiza evento com conferencia Google Meet.
     *
     * @return array{
     *   event_id:?string,
     *   meeting_link:?string,
     *   conference_id:?string,
     *   html_link:?string,
     *   raw:array<string,mixed>
     * }
     */
    public function updateEventWithMeet(Appointment $appointment): array
    {
        $appointment->load([
            'patient',
            'calendar.doctor.user',
            'calendar.doctor.googleCalendarToken',
            'type',
            'specialty',
        ]);

        if (!$appointment->google_event_id) {
            return $this->createEventWithMeet($appointment);
        }

        $doctor = $appointment->calendar?->doctor;
        if (!$doctor || !$doctor->googleCalendarToken) {
            throw new \RuntimeException('Medico sem token Google Calendar conectado.');
        }

        $this->client($doctor->googleCalendarToken);

        $calendarId = 'primary';
        $event = $this->buildEvent($appointment);
        $this->attachAppointmentExtendedProperties($event, $appointment);
        $event->setConferenceData($this->buildGoogleMeetConferenceData((string) $appointment->id));

        try {
            $updatedEvent = $this->service->events->update(
                $calendarId,
                $appointment->google_event_id,
                $event,
                ['conferenceDataVersion' => 1]
            );
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                $appointment->withoutEvents(function () use ($appointment) {
                    if ($appointment->exists) {
                        $appointment->update(['google_event_id' => null]);
                    } else {
                        $appointment->google_event_id = null;
                    }
                });

                return $this->createEventWithMeet($appointment);
            }

            throw $e;
        }

        $updatedEventId = $updatedEvent->getId();
        if ($updatedEventId && $updatedEventId !== $appointment->google_event_id) {
            $appointment->withoutEvents(function () use ($appointment, $updatedEventId) {
                if ($appointment->exists) {
                    $appointment->update(['google_event_id' => $updatedEventId]);
                } else {
                    $appointment->google_event_id = $updatedEventId;
                }
            });
        }

        return [
            'event_id' => $updatedEventId,
            'meeting_link' => $this->extractMeetingLinkFromGoogleEvent($updatedEvent),
            'conference_id' => $this->extractConferenceIdFromGoogleEvent($updatedEvent),
            'html_link' => $updatedEvent->getHtmlLink(),
            'raw' => $this->safeGoogleEventPayload($updatedEvent),
        ];
    }

    public function deleteEventForAppointment(Appointment $appointment): bool
    {
        return $this->deleteEvent($appointment);
    }

    public function extractMeetingLinkFromGoogleEvent($googleEvent): ?string
    {
        if (!$googleEvent) {
            return null;
        }

        $hangoutLink = $googleEvent->getHangoutLink();
        if (is_string($hangoutLink) && trim($hangoutLink) !== '') {
            return $hangoutLink;
        }

        $conferenceData = $googleEvent->getConferenceData();
        if (!$conferenceData) {
            return null;
        }

        $entryPoints = $conferenceData->getEntryPoints() ?? [];
        foreach ($entryPoints as $entryPoint) {
            if ($entryPoint->getEntryPointType() === 'video' && is_string($entryPoint->getUri()) && trim($entryPoint->getUri()) !== '') {
                return $entryPoint->getUri();
            }
        }

        return null;
    }

    public function extractConferenceIdFromGoogleEvent($googleEvent): ?string
    {
        if (!$googleEvent || !$googleEvent->getConferenceData()) {
            return null;
        }

        $conferenceId = $googleEvent->getConferenceData()->getConferenceId();

        return is_string($conferenceId) && trim($conferenceId) !== '' ? $conferenceId : null;
    }

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
            // Se evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe mais, considerar sucesso
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                Log::info('Evento jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe no Google Calendar', [
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

    protected function buildGoogleMeetConferenceData(string $appointmentId): \Google_Service_Calendar_ConferenceData
    {
        $conferenceData = new \Google_Service_Calendar_ConferenceData();
        $createRequest = new \Google_Service_Calendar_CreateConferenceRequest();
        $solutionKey = new \Google_Service_Calendar_ConferenceSolutionKey();
        $solutionKey->setType('hangoutsMeet');

        $createRequest->setConferenceSolutionKey($solutionKey);
        $createRequest->setRequestId($appointmentId . '-' . (string) Str::uuid());
        $conferenceData->setCreateRequest($createRequest);

        return $conferenceData;
    }

    protected function attachAppointmentExtendedProperties(
        Google_Service_Calendar_Event $event,
        Appointment $appointment
    ): void {
        $extendedProperties = new \Google_Service_Calendar_EventExtendedProperties();
        $extendedProperties->setPrivate([
            'appointment_id' => $appointment->id,
        ]);
        $event->setExtendedProperties($extendedProperties);
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeGoogleEventPayload($googleEvent): array
    {
        $conferenceData = $googleEvent?->getConferenceData();
        $entryPoints = [];

        if ($conferenceData) {
            foreach (($conferenceData->getEntryPoints() ?? []) as $entryPoint) {
                $entryPoints[] = [
                    'entry_point_type' => $entryPoint->getEntryPointType(),
                    'uri' => $entryPoint->getUri(),
                    'label' => $entryPoint->getLabel(),
                ];
            }
        }

        return [
            'id' => $googleEvent?->getId(),
            'status' => $googleEvent?->getStatus(),
            'html_link' => $googleEvent?->getHtmlLink(),
            'hangout_link' => $googleEvent?->getHangoutLink(),
            'conference_id' => $conferenceData?->getConferenceId(),
            'conference_type' => $conferenceData?->getConferenceSolution()?->getKey()?->getType(),
            'entry_points' => $entryPoints,
        ];
    }

    /**
     * Lista eventos do Google Calendar para um medico
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
                    'title' => $event->getSummary() ?? 'Sem tÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­tulo',
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
            $oauthConfig = google_oauth_config();
            $this->client->setClientId((string) ($oauthConfig['client_id'] ?? ''));
            $this->client->setClientSecret((string) ($oauthConfig['client_secret'] ?? ''));
            $this->client->setRedirectUri((string) ($oauthConfig['redirect_uri'] ?? route('google.callback')));

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
     * ConstrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³i um objeto Google_Service_Calendar_Event a partir de um Appointment
     */
    protected function buildEvent(Appointment $appointment): Google_Service_Calendar_Event
    {
        // Garantir que os relacionamentos estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o carregados
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

        // TÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­tulo do evento - mais informativo
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

        // DescriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o completa e formatada
        $description = [];
        
        // SeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: InformaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes do Paciente
        if ($appointment->patient) {
            $description[] = "ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“Ãƒâ€šÃ‚Â¤ PACIENTE";
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

        // SeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: InformaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes da Consulta
        $description[] = "ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ CONSULTA";
        $description[] = "Data: {$appointment->starts_at->format('d/m/Y')}";
        $description[] = "HorÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio: {$appointment->starts_at->format('H:i')} - {$appointment->ends_at->format('H:i')}";
        
        // DuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o calculada ou do tipo de consulta
        if ($appointment->type && $appointment->type->duration_min) {
            $description[] = "DuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: {$appointment->type->duration_min} minutos";
        } else {
            $durationMinutes = $appointment->starts_at->diffInMinutes($appointment->ends_at);
            $description[] = "DuraÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: {$durationMinutes} minutos";
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
            'no_show' => 'NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o Compareceu'
        ];
        $statusTranslated = $statusMap[$appointment->status] ?? $appointment->status;
        $description[] = "Status: {$statusTranslated}";
        $description[] = ""; // Linha em branco

        // SeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: InformaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes do MÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico
        if ($appointment->calendar && $appointment->calendar->doctor) {
            $doctor = $appointment->calendar->doctor;
            $description[] = "ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‹Å“Ãƒâ€šÃ‚Â¨ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€šÃ‚ÂÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã‚Â¡ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¸Ãƒâ€šÃ‚Â MÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°DICO";
            
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

        // SeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: ObservaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes
        if ($appointment->notes) {
            $description[] = "ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã…â€œÃƒâ€šÃ‚Â OBSERVAÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ES";
            $description[] = $appointment->notes;
            $description[] = ""; // Linha em branco
        }

        // SeÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o: InformaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes TÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©cnicas (ocultas)
        $description[] = "---";
        $description[] = "ID do Agendamento: {$appointment->id}";

        $event->setDescription(implode("\n", $description));

        // Data e hora de inÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­cio
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
     * Sincroniza uma recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia como evento recorrente no Google Calendar
     * 
     * IMPORTANTE: Para recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncias sem data fim, usa uma data fim padrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de 1 ano
     * para evitar criaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o infinita de eventos. O evento pode ser renovado manualmente.
     */
    public function syncRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            // Buscar o mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico
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

            // Buscar calendÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡rio do mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©dico
            $calendar = $doctor->calendars()->first();
            if (!$calendar) {
                return false;
            }

            // Carregar regras e relacionamentos
            $recurring->load(['rules', 'patient', 'appointmentType']);

            if ($recurring->rules->isEmpty()) {
                Log::warning('RecorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia sem regras nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o pode ser sincronizada', [
                    'recurring_id' => $recurring->id,
                ]);
                return false;
            }

            // Para cada regra, criar um evento recorrente
            // (Uma recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia pode ter mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºltiplas regras - ex: segunda e quarta)
            // ESTRATÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â°GIA: Se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe evento, deletar antes de criar novo (mais simples e confiÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡vel)
            foreach ($recurring->rules as $rule) {
                // IMPORTANTE: Verificar se jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe evento para esta regra
                $existingEventId = $recurring->getGoogleRecurringEventId($rule->id);
                
                if ($existingEventId) {
                    // Se existe, deletar antes de criar novo (estratÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©gia de ediÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o)
                    try {
                        $calendarId = 'primary';
                        $existingEvent = $this->service->events->get($calendarId, $existingEventId);
                        
                        // Evento existe, deletar antes de criar novo
                        Log::info('Evento recorrente jÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ existe, deletando para recriar', [
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
                        // Evento nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe mais no Google Calendar, apenas limpar ID
                        Log::info('Evento recorrente nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o encontrado no Google Calendar, criando novo', [
                            'recurring_id' => $recurring->id,
                            'rule_id' => $rule->id,
                            'google_event_id' => $existingEventId,
                            'error' => $e->getMessage(),
                        ]);
                        
                        // Limpar ID invÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡lido
                        $eventIds = $recurring->google_recurring_event_ids ?? [];
                        unset($eventIds[$rule->id]);
                        $recurring->update(['google_recurring_event_ids' => $eventIds]);
                    }
                }
                
                // Criar novo evento (ou recriar apÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³s deletar)
                $this->createRecurringEventForRule($recurring, $rule, $calendar);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia com Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cria um evento recorrente no Google Calendar para uma regra especÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­fica
     */
    protected function createRecurringEventForRule(
        RecurringAppointment $recurring,
        RecurringAppointmentRule $rule,
        $calendar
    ): bool {
        try {
            $event = new Google_Service_Calendar_Event();

            // TÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­tulo do evento
            $title = $recurring->patient 
                ? "Consulta Recorrente - {$recurring->patient->full_name}"
                : 'Consulta Recorrente';
            
            if ($recurring->appointmentType) {
                $title .= " - {$recurring->appointmentType->name}";
            }

            $event->setSummary($title);

            // DescriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
            $description = [];
            if ($recurring->patient) {
                $description[] = "Paciente: {$recurring->patient->full_name}";
            }
            $description[] = "Agendamento Recorrente";
            // Adicionar ID da recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia e regra na descriÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o para identificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o
            $description[] = "RecurringAppointment ID: {$recurring->id}";
            $description[] = "Rule ID: {$rule->id}";
            $event->setDescription(implode("\n", $description));
            
            // Adicionar ID da recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia como extended property para identificaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o programÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡tica
            $extendedProperties = new \Google_Service_Calendar_EventExtendedProperties();
            $extendedProperties->setPrivate([
                'recurring_appointment_id' => $recurring->id,
                'rule_id' => $rule->id,
            ]);
            $event->setExtendedProperties($extendedProperties);

            // Data e hora de inÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­cio (primeira ocorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia)
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

            // Configurar recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia (RRULE)
            $rrule = $this->buildRRule($recurring, $rule);
            if ($rrule) {
                // O Google Calendar API aceita RRULE diretamente como array
                $event->setRecurrence([$rrule]);
            }

            // Criar evento no Google Calendar
            $calendarId = 'primary';
            $createdEvent = $this->service->events->insert($calendarId, $event);

            $googleEventId = $createdEvent->getId();

            // Armazenar o ID do evento recorrente na recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia
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
     * ConstrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³i a regra RRULE para o Google Calendar
     * 
     * IMPORTANTE: Para recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncias sem data fim, define uma data fim padrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de 1 ano
     * para evitar criaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o infinita de eventos.
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
            // Para total de sessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes, calcular data aproximada
            // Assumindo 1 sessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o por semana (pode ser ajustado)
            $weeks = ceil($recurring->total_sessions / 1); // Ajustar se houver mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºltiplas regras
            $endDate = Carbon::parse($recurring->start_date)->addWeeks($weeks);
        } else {
            // IMPORTANTE: Para recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncias sem data fim, usar data fim padrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o de 1 ano
            // Isso evita criaÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â§ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o infinita de eventos no Google Calendar
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
     * Cancela uma recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia no Google Calendar
     * Atualiza a data fim para hoje, mantendo eventos passados como histÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³rico
     * e removendo apenas eventos futuros
     * 
     * Funciona para TODOS os tipos de recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia:
     * - Com data fim (end_type = 'date'): atualiza para terminar hoje
     * - Com nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºmero de sessÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âµes (end_type = 'total_sessions'): atualiza para terminar hoje
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
            // IMPORTANTE: Funciona para qualquer tipo de recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia (com ou sem data fim)
            foreach ($recurring->rules as $rule) {
                $googleEventId = $recurring->getGoogleRecurringEventId($rule->id);
                
                if (!$googleEventId) {
                    continue; // NÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o hÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¡ evento para cancelar
                }

                try {
                    // Buscar evento atual
                    $event = $this->service->events->get($calendarId, $googleEventId);
                    
                    // Atualizar RRULE para terminar hoje (mantÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â©m eventos passados, remove futuros)
                    // Funciona independente do end_type original (date, total_sessions, ou none)
                    $today = Carbon::now();
                    $rrule = $this->buildRRuleWithEndDate($recurring, $rule, $today);
                    
                    if ($rrule) {
                        $event->setRecurrence([$rrule]);
                        
                        // Atualizar evento
                        $updatedEvent = $this->service->events->update($calendarId, $googleEventId, $event);
                        
                        Log::info('RecorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia cancelada no Google Calendar (eventos futuros removidos, passados mantidos)', [
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
            Log::error('Erro ao cancelar recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia no Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove evento recorrente do Google Calendar completamente
     * Usado apenas quando a recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â© deletada permanentemente
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
     * Usado para recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncias sem data fim que estÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o prÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ximas do fim
     */
    public function renewRecurringEvent(RecurringAppointment $recurring): bool
    {
        try {
            // SÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³ renova se a recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o tiver data fim
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
                    // Se nÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â£o existe evento, criar um novo
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
            Log::error('Erro ao renovar recorrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âªncia no Google Calendar', [
                'recurring_id' => $recurring->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * ConstrÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³i RRULE com data fim especÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â­fica
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
