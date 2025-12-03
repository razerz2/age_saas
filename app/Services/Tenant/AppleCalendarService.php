<?php

namespace App\Services\Tenant;

use App\Models\Tenant\AppleCalendarToken;
use App\Models\Tenant\Appointment;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\RecurringAppointment;
use App\Models\Tenant\RecurringAppointmentRule;
use Sabre\DAV\Client;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar\DateTime;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppleCalendarService
{
    protected ?Client $client = null;
    protected ?string $calendarUrl = null;

    /**
     * Cria uma instância do cliente CalDAV para um token específico
     */
    public function client(AppleCalendarToken $token): Client
    {
        $settings = [
            'baseUri' => $token->server_url,
            'userName' => $token->username,
            'password' => decrypt($token->password), // Descriptografar senha
        ];

        $this->client = new Client($settings);
        $this->calendarUrl = $token->calendar_url;

        return $this->client;
    }

    /**
     * Sincroniza um agendamento com o Apple Calendar
     */
    public function syncEvent(Appointment $appointment): bool
    {
        try {
            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                return false;
            }

            $this->client($token);

            // Se já existe apple_event_id, deletar e criar novo
            if ($appointment->apple_event_id) {
                $this->deleteEventFromApple($appointment->apple_event_id, $calendar->doctor);
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update(['apple_event_id' => null]);
                });
            }

            return $this->createEvent($appointment);
        } catch (\Exception $e) {
            Log::error('Erro ao sincronizar evento com Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cria um evento no Apple Calendar
     */
    public function createEvent(Appointment $appointment): bool
    {
        try {
            $appointment->load([
                'patient',
                'calendar.doctor.user',
                'type',
                'specialty'
            ]);

            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                return false;
            }

            $this->client($token);

            // Construir evento iCal
            $vcalendar = $this->buildEvent($appointment);

            // Gerar UID único para o evento
            $uid = $appointment->id . '@' . config('app.url');
            $filename = $uid . '.ics';

            // URL completa do calendário
            $calendarPath = $this->calendarUrl ?: $this->getDefaultCalendarPath($token);

            // Criar evento no CalDAV
            $response = $this->client->request('PUT', $calendarPath . $filename, $vcalendar->serialize(), [
                'Content-Type' => 'text/calendar; charset=utf-8',
            ]);

            if ($response['statusCode'] >= 200 && $response['statusCode'] < 300) {
                // Salvar o ID do evento (usando o UID como identificador)
                $appointment->withoutEvents(function () use ($appointment, $uid) {
                    $appointment->update([
                        'apple_event_id' => $uid,
                    ]);
                });

                Log::info('Evento criado no Apple Calendar', [
                    'appointment_id' => $appointment->id,
                    'apple_event_id' => $uid,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento no Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Atualiza um evento no Apple Calendar
     */
    public function updateEvent(Appointment $appointment): bool
    {
        try {
            if (!$appointment->apple_event_id) {
                return $this->createEvent($appointment);
            }

            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                return false;
            }

            $this->client($token);

            // Construir evento iCal atualizado
            $vcalendar = $this->buildEvent($appointment);

            $filename = $appointment->apple_event_id . '.ics';
            $calendarPath = $this->calendarUrl ?: $this->getDefaultCalendarPath($token);

            // Atualizar evento no CalDAV
            $response = $this->client->request('PUT', $calendarPath . $filename, $vcalendar->serialize(), [
                'Content-Type' => 'text/calendar; charset=utf-8',
            ]);

            if ($response['statusCode'] >= 200 && $response['statusCode'] < 300) {
                Log::info('Evento atualizado no Apple Calendar', [
                    'appointment_id' => $appointment->id,
                    'apple_event_id' => $appointment->apple_event_id,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar evento no Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove um evento do Apple Calendar
     */
    public function deleteEvent(Appointment $appointment): bool
    {
        try {
            if (!$appointment->apple_event_id) {
                return true;
            }

            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                return false;
            }

            $doctor = $calendar->doctor;
            $appleEventId = $appointment->apple_event_id;

            $deleted = $this->deleteEventFromApple($appleEventId, $doctor);

            if ($deleted) {
                $appointment->withoutEvents(function () use ($appointment) {
                    $appointment->update([
                        'apple_event_id' => null,
                    ]);
                });

                Log::info('Evento removido do Apple Calendar', [
                    'appointment_id' => $appointment->id,
                    'apple_event_id' => $appleEventId,
                ]);
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error('Erro ao remover evento do Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Remove um evento do Apple Calendar (método auxiliar)
     */
    protected function deleteEventFromApple(string $appleEventId, Doctor $doctor): bool
    {
        try {
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                return false;
            }

            $this->client($token);

            $filename = $appleEventId . '.ics';
            $calendarPath = $this->calendarUrl ?: $this->getDefaultCalendarPath($token);

            $response = $this->client->request('DELETE', $calendarPath . $filename);

            return $response['statusCode'] >= 200 && $response['statusCode'] < 300;
        } catch (\Exception $e) {
            // Se evento não existe mais, considerar sucesso
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                Log::info('Evento já não existe no Apple Calendar', [
                    'apple_event_id' => $appleEventId,
                ]);
                return true;
            }

            Log::error('Erro ao remover evento do Apple Calendar', [
                'apple_event_id' => $appleEventId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Lista eventos do Apple Calendar para um médico
     */
    public function listEvents($doctorId, $startDate = null, $endDate = null): array
    {
        try {
            $doctor = Doctor::findOrFail($doctorId);
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                return [];
            }

            $this->client($token);

            $calendarPath = $this->calendarUrl ?: $this->getDefaultCalendarPath($token);

            // Buscar eventos usando REPORT
            $start = $startDate ? Carbon::parse($startDate) : Carbon::now();
            $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->addMonth();

            $xml = '<?xml version="1.0" encoding="utf-8" ?>
<C:calendar-query xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
    <D:prop>
        <D:getetag/>
        <C:calendar-data/>
    </D:prop>
    <C:filter>
        <C:comp-filter name="VCALENDAR">
            <C:comp-filter name="VEVENT">
                <C:time-range start="' . $start->format('Ymd\THis\Z') . '" end="' . $end->format('Ymd\THis\Z') . '"/>
            </C:comp-filter>
        </C:comp-filter>
    </C:filter>
</C:calendar-query>';

            $response = $this->client->request('REPORT', $calendarPath, $xml, [
                'Content-Type' => 'application/xml; charset=utf-8',
                'Depth' => '1',
            ]);

            $result = [];
            // Processar resposta XML e extrair eventos
            // Implementação simplificada - pode ser melhorada

            return $result;
        } catch (\Exception $e) {
            Log::error('Erro ao listar eventos do Apple Calendar', [
                'doctor_id' => $doctorId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Constrói um objeto VCalendar a partir de um Appointment
     */
    protected function buildEvent(Appointment $appointment): VCalendar
    {
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

        $vcalendar = new VCalendar();

        // Título do evento
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

        // Descrição completa
        $description = [];
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
            $description[] = "";
        }

        $description[] = "📅 CONSULTA";
        $description[] = "Data: {$appointment->starts_at->format('d/m/Y')}";
        $description[] = "Horário: {$appointment->starts_at->format('H:i')} - {$appointment->ends_at->format('H:i')}";

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
        $description[] = "";

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
            $description[] = "";
        }

        if ($appointment->notes) {
            $description[] = "📝 OBSERVAÇÕES";
            $description[] = $appointment->notes;
            $description[] = "";
        }

        $description[] = "---";
        $description[] = "ID do Agendamento: {$appointment->id}";

        // Criar evento
        $vevent = $vcalendar->createComponent('VEVENT');
        $vevent->UID = $appointment->id . '@' . config('app.url');
        $vevent->SUMMARY = $title;
        $vevent->DESCRIPTION = implode("\n", $description);
        $vevent->DTSTART = new DateTime($appointment->starts_at->setTimezone('America/Sao_Paulo'), false);
        $vevent->DTEND = new DateTime($appointment->ends_at->setTimezone('America/Sao_Paulo'), false);
        $vevent->DTSTAMP = new DateTime(Carbon::now()->setTimezone('America/Sao_Paulo'), false);
        $vevent->CREATED = new DateTime($appointment->created_at->setTimezone('America/Sao_Paulo'), false);
        $vevent->LAST_MODIFIED = new DateTime($appointment->updated_at->setTimezone('America/Sao_Paulo'), false);

        $vcalendar->add($vevent);

        return $vcalendar;
    }

    /**
     * Obtém o caminho padrão do calendário
     */
    protected function getDefaultCalendarPath(AppleCalendarToken $token): string
    {
        // Para iCloud, o caminho padrão é geralmente /{username}/calendars/{calendar-id}/
        // Pode ser necessário descobrir o calendar-id através de PROPFIND
        // Por enquanto, retornamos um caminho padrão
        return '/calendars/' . $token->username . '/';
    }

    /**
     * Descobre os calendários disponíveis para um token
     */
    public function discoverCalendars(AppleCalendarToken $token): array
    {
        try {
            $this->client($token);

            // Fazer PROPFIND para descobrir calendários
            $response = $this->client->propFind('/calendars/' . $token->username . '/', [
                '{DAV:}displayname',
                '{urn:ietf:params:xml:ns:caldav}calendar-description',
            ], 1);

            $calendars = [];
            foreach ($response as $path => $props) {
                if (isset($props['{DAV:}displayname'])) {
                    $calendars[] = [
                        'path' => $path,
                        'name' => $props['{DAV:}displayname'],
                        'description' => $props['{urn:ietf:params:xml:ns:caldav}calendar-description'] ?? null,
                    ];
                }
            }

            return $calendars;
        } catch (\Exception $e) {
            Log::error('Erro ao descobrir calendários do Apple Calendar', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}

