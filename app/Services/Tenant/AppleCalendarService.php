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
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AppleCalendarService
{
    protected ?Client $client = null;
    protected ?string $calendarUrl = null;
    protected array $availableCalendars = [];

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
            Log::info('Iniciando criação de evento no Apple Calendar', [
                'appointment_id' => $appointment->id,
            ]);

            $appointment->load([
                'patient',
                'calendar.doctor.user',
                'type',
                'specialty'
            ]);

            $calendar = $appointment->calendar;
            if (!$calendar || !$calendar->doctor) {
                Log::warning('Calendário ou médico não encontrado para o agendamento', [
                    'appointment_id' => $appointment->id,
                    'calendar_id' => $appointment->calendar_id,
                    'has_calendar' => !is_null($calendar),
                    'has_doctor' => $calendar && $calendar->doctor ? true : false,
                ]);
                return false;
            }

            $doctor = $calendar->doctor;
            $token = $doctor->appleCalendarToken;

            if (!$token) {
                Log::warning('Token do Apple Calendar não encontrado para o médico', [
                    'appointment_id' => $appointment->id,
                    'doctor_id' => $doctor->id,
                ]);
                return false;
            }

            Log::info('Token do Apple Calendar encontrado', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'server_url' => $token->server_url,
                'calendar_url' => $token->calendar_url,
            ]);

            $this->client($token);

            // Gerar UID único para o evento (simples para evitar quebra de linha)
            $uid = $appointment->id . '@agendamento-saas';
            
            // Construir evento iCal passando o UID
            $vcalendar = $this->buildEvent($appointment, $uid);
            
            $filename = $uid . '.ics';

            // Obter caminho do calendário do token ou da propriedade da classe
            $calendarPath = $token->calendar_url ?: $this->calendarUrl;
            
            // Validar e normalizar o caminho do calendário
            $calendarPath = $this->normalizeCalendarPath($calendarPath, $token, $appointment);
            
            // Garantir que o caminho termine com /
            if (substr($calendarPath, -1) !== '/') {
                $calendarPath .= '/';
            }

            Log::info('Tentando criar evento no Apple Calendar', [
                'appointment_id' => $appointment->id,
                'calendar_path' => $calendarPath,
                'filename' => $filename,
                'full_url' => $calendarPath . $filename,
                'server_url' => $token->server_url,
                'calendar_url_from_token' => $token->calendar_url,
            ]);

            // Serializar o calendário
            $icalContent = $vcalendar->serialize();
            
            Log::debug('Conteúdo iCal gerado', [
                'appointment_id' => $appointment->id,
                'ical_length' => strlen($icalContent),
                'ical_preview' => substr($icalContent, 0, 500),
            ]);

            // Criar evento no CalDAV
            $response = $this->client->request('PUT', $calendarPath . $filename, $icalContent, [
                'Content-Type' => 'text/calendar; charset=utf-8',
            ]);

            Log::info('Resposta do servidor CalDAV', [
                'appointment_id' => $appointment->id,
                'status_code' => $response['statusCode'] ?? 'N/A',
                'headers' => $response['headers'] ?? [],
                'body_preview' => isset($response['body']) ? substr($response['body'], 0, 500) : 'N/A',
            ]);

            // Se sucesso, retornar true
            if (isset($response['statusCode']) && $response['statusCode'] >= 200 && $response['statusCode'] < 300) {
                // Salvar o ID do evento (usando o UID como identificador)
                $appointment->withoutEvents(function () use ($appointment, $uid) {
                    $appointment->update([
                        'apple_event_id' => $uid,
                    ]);
                });

                Log::info('Evento criado no Apple Calendar com sucesso', [
                    'appointment_id' => $appointment->id,
                    'apple_event_id' => $uid,
                    'status_code' => $response['statusCode'],
                    'calendar_path' => $calendarPath,
                ]);

                return true;
            }
            
            // Se receber 403 (Forbidden) e tiver outros calendários disponíveis, tentar o próximo
            if (isset($response['statusCode']) && $response['statusCode'] === 403 && !empty($this->availableCalendars)) {
                Log::warning('Calendário retornou 403 (Forbidden), tentando próximo calendário disponível', [
                    'appointment_id' => $appointment->id,
                    'failed_calendar_path' => $calendarPath,
                    'available_calendars_count' => count($this->availableCalendars),
                ]);
                
                // Tentar os próximos calendários da lista
                foreach ($this->availableCalendars as $index => $cal) {
                    if ($cal['path'] === $calendarPath) {
                        continue; // Pular o que já tentamos
                    }
                    
                    Log::info('Tentando próximo calendário', [
                        'appointment_id' => $appointment->id,
                        'calendar_path' => $cal['path'],
                        'calendar_name' => $cal['name'] ?? 'N/A',
                        'attempt' => $index + 1,
                    ]);
                    
                    // Tentar criar no próximo calendário
                    try {
                        $nextResponse = $this->client->request('PUT', $cal['path'] . $filename, $icalContent, [
                            'Content-Type' => 'text/calendar; charset=utf-8',
                        ]);
                        
                        if (isset($nextResponse['statusCode']) && $nextResponse['statusCode'] >= 200 && $nextResponse['statusCode'] < 300) {
                            // Sucesso!
                            $appointment->withoutEvents(function () use ($appointment, $uid) {
                                $appointment->update([
                                    'apple_event_id' => $uid,
                                ]);
                            });
                            
                            Log::info('✅ Evento criado no Apple Calendar com sucesso (após tentar outro calendário)', [
                                'appointment_id' => $appointment->id,
                                'apple_event_id' => $uid,
                                'calendar_path' => $cal['path'],
                                'calendar_name' => $cal['name'] ?? 'N/A',
                            ]);
                            
                            return true;
                        }
                    } catch (\Exception $e) {
                        Log::debug('Erro ao tentar próximo calendário', [
                            'appointment_id' => $appointment->id,
                            'calendar_path' => $cal['path'],
                            'error' => $e->getMessage(),
                        ]);
                        continue; // Tentar próximo
                    }
                }
            }

            Log::warning('Falha ao criar evento no Apple Calendar - Status HTTP inválido', [
                'appointment_id' => $appointment->id,
                'status_code' => $response['statusCode'] ?? 'N/A',
                'response' => $response,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Erro ao criar evento no Apple Calendar', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Atualiza um evento no Apple Calendar
     * 
     * ESTRATÉGIA: Deletar e criar novamente é mais confiável que atualizar
     * Isso garante que o evento será atualizado corretamente mesmo se houver
     * mudanças no caminho do calendário ou outros problemas
     */
    public function updateEvent(Appointment $appointment): bool
    {
        Log::info('Atualizando evento no Apple Calendar (deletando e criando novamente)', [
            'appointment_id' => $appointment->id,
            'apple_event_id' => $appointment->apple_event_id,
        ]);
        
        // Usar syncEvent que já implementa a estratégia de deletar e criar
        return $this->syncEvent($appointment);
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
                // Tentar atualizar apenas se o agendamento ainda existir no banco
                // (pode já ter sido deletado quando chamado do Observer)
                try {
                    if ($appointment->exists) {
                        $appointment->withoutEvents(function () use ($appointment) {
                            $appointment->update([
                                'apple_event_id' => null,
                            ]);
                        });
                    }
                } catch (\Exception $e) {
                    // Se não conseguir atualizar, não é crítico (já foi deletado)
                    Log::debug('Não foi possível atualizar apple_event_id após deletar (agendamento pode já estar deletado)', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('✅ Evento removido do Apple Calendar com sucesso', [
                    'appointment_id' => $appointment->id,
                    'apple_event_id' => $appleEventId,
                ]);
            } else {
                Log::warning('⚠️ Falha ao remover evento do Apple Calendar', [
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
                Log::warning('Token do Apple Calendar não encontrado para deletar evento', [
                    'apple_event_id' => $appleEventId,
                    'doctor_id' => $doctor->id,
                ]);
                return false;
            }

            $this->client($token);

            $filename = $appleEventId . '.ics';
            
            // Tentar descobrir o caminho correto do calendário
            $calendarPath = $token->calendar_url ?: $this->calendarUrl;
            
            // Se temos um caminho, validar
            if ($calendarPath) {
                if (str_starts_with($calendarPath, 'webcal://')) {
                    $calendarPath = null; // Forçar descoberta
                } elseif (str_starts_with($calendarPath, 'http://') || str_starts_with($calendarPath, 'https://')) {
                    $parsedUrl = parse_url($calendarPath);
                    $calendarPath = $parsedUrl['path'] ?? null;
                }
            }
            
            // Variável para armazenar calendários descobertos (para tentar múltiplos se necessário)
            $calendars = [];
            
            // Se não temos caminho válido, descobrir automaticamente
            if (!$calendarPath || str_ends_with($calendarPath, '/calendars/') || str_ends_with($calendarPath, '/calendars')) {
                Log::info('Descobrindo caminho do calendário para deletar evento', [
                    'apple_event_id' => $appleEventId,
                ]);
                
                try {
                    $calendars = $this->discoverCalendars($token);
                    if (!empty($calendars)) {
                        // Ordenar por prioridade
                        usort($calendars, function ($a, $b) {
                            return $this->getCalendarPriority($a['path'], $a['name']) 
                                <=> $this->getCalendarPriority($b['path'], $b['name']);
                        });
                        $calendarPath = $calendars[0]['path'];
                    } else {
                        $calendarPath = $this->getDefaultCalendarPath($token);
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao descobrir calendário para deletar, usando padrão', [
                        'apple_event_id' => $appleEventId,
                        'error' => $e->getMessage(),
                    ]);
                    $calendarPath = $this->getDefaultCalendarPath($token);
                }
            } else {
                // Se já temos um caminho, tentar descobrir calendários para ter lista completa
                try {
                    $calendars = $this->discoverCalendars($token);
                } catch (\Exception $e) {
                    // Ignorar erro na descoberta se já temos um caminho
                }
            }
            
            // Garantir que o caminho termine com /
            if (substr($calendarPath, -1) !== '/') {
                $calendarPath .= '/';
            }

            Log::info('Tentando deletar evento do Apple Calendar', [
                'apple_event_id' => $appleEventId,
                'calendar_path' => $calendarPath,
                'full_url' => $calendarPath . $filename,
            ]);

            // Tentar deletar - pode estar em qualquer calendário, tentar vários se necessário
            $calendarsToTry = [$calendarPath];
            
            // Se descobrimos calendários, tentar todos eles para garantir que deletamos
            if (!empty($calendars)) {
                foreach ($calendars as $cal) {
                    $calPath = substr($cal['path'], -1) === '/' ? $cal['path'] : $cal['path'] . '/';
                    if ($calPath !== $calendarPath) {
                        $calendarsToTry[] = $calPath;
                    }
                }
            }
            
            foreach ($calendarsToTry as $path) {
                try {
                    if (substr($path, -1) !== '/') {
                        $path .= '/';
                    }
                    
                    $response = $this->client->request('DELETE', $path . $filename);
                    
                    if (isset($response['statusCode']) && $response['statusCode'] >= 200 && $response['statusCode'] < 300) {
                        Log::info('✅ Evento deletado do Apple Calendar com sucesso', [
                            'apple_event_id' => $appleEventId,
                            'calendar_path' => $path,
                            'status_code' => $response['statusCode'],
                        ]);
                        return true;
                    }
                    
                    // Se for 404, o evento já não existe (considerar sucesso)
                    if (isset($response['statusCode']) && $response['statusCode'] === 404) {
                        Log::info('Evento já não existe no Apple Calendar (404)', [
                            'apple_event_id' => $appleEventId,
                            'calendar_path' => $path,
                        ]);
                        return true;
                    }
                } catch (\Exception $e) {
                    // Se for 404, considerar sucesso
                    if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                        Log::info('Evento já não existe no Apple Calendar', [
                            'apple_event_id' => $appleEventId,
                            'calendar_path' => $path,
                        ]);
                        return true;
                    }
                    // Continuar tentando outros calendários
                    continue;
                }
            }

            Log::warning('Não foi possível deletar evento do Apple Calendar', [
                'apple_event_id' => $appleEventId,
                'calendars_tried' => count($calendarsToTry),
            ]);
            
            return false;
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
    protected function buildEvent(Appointment $appointment, ?string $uid = null): VCalendar
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
        
        // Usar UID fornecido ou gerar um simples
        if (!$uid) {
            $uid = $appointment->id . '@agendamento-saas';
        }
        $vevent->UID = $uid;
        $vevent->SUMMARY = $title;
        $vevent->DESCRIPTION = implode("\n", $description);
        
        // Preparar datas e converter para strings no formato iCalendar
        // Formato: YYYYMMDDTHHmmss para hora local com timezone
        $dtStart = $appointment->starts_at->copy()->setTimezone('America/Sao_Paulo');
        $dtEnd = $appointment->ends_at->copy()->setTimezone('America/Sao_Paulo');
        $created = $appointment->created_at->copy()->setTimezone('UTC');
        $lastModified = $appointment->updated_at->copy()->setTimezone('UTC');
        
        // Criar propriedades usando strings formatadas
        $dtStartStr = $dtStart->format('Ymd\THis');
        $dtEndStr = $dtEnd->format('Ymd\THis');
        $createdStr = $created->format('Ymd\THis\Z');
        $lastModifiedStr = $lastModified->format('Ymd\THis\Z');
        
        // Adicionar propriedades usando strings
        // IMPORTANTE: DTSTAMP é adicionado automaticamente pelo VCalendar, não adicionar manualmente
        $vevent->add('DTSTART', $dtStartStr, ['TZID' => 'America/Sao_Paulo']);
        $vevent->add('DTEND', $dtEndStr, ['TZID' => 'America/Sao_Paulo']);
        $vevent->add('CREATED', $createdStr);
        $vevent->add('LAST-MODIFIED', $lastModifiedStr);

        $vcalendar->add($vevent);

        return $vcalendar;
    }

    /**
     * Normaliza e valida o caminho do calendário
     * 
     * O calendar_url é OPCIONAL. Se não fornecido, o sistema tentará descobrir
     * automaticamente o calendário correto via CalDAV PROPFIND.
     * 
     * @param string|null $calendarPath Caminho do calendário (opcional)
     * @param AppleCalendarToken $token Token de autenticação
     * @param Appointment $appointment Agendamento para contexto de logs
     * @return string Caminho do calendário validado
     */
    protected function normalizeCalendarPath(?string $calendarPath, AppleCalendarToken $token, Appointment $appointment): string
    {
        // Se temos um caminho configurado, validar se é válido para CalDAV
        if (!empty($calendarPath)) {
            // webcal:// é apenas read-only (assinatura), não serve para CalDAV
            if (str_starts_with($calendarPath, 'webcal://')) {
                Log::info('calendar_url usa webcal:// (read-only), ignorando e usando descoberta automática', [
                    'appointment_id' => $appointment->id,
                    'webcal_url' => $calendarPath,
                ]);
                $calendarPath = null; // Forçar descoberta automática
            }
            // Se for uma URL completa (http/https), extrair apenas o caminho relativo
            elseif (str_starts_with($calendarPath, 'http://') || str_starts_with($calendarPath, 'https://')) {
                Log::info('Extraindo caminho relativo de URL completa', [
                    'appointment_id' => $appointment->id,
                    'original_url' => $calendarPath,
                ]);
                $parsedUrl = parse_url($calendarPath);
                if ($parsedUrl && isset($parsedUrl['path']) && !empty($parsedUrl['path'])) {
                    $calendarPath = $parsedUrl['path'];
                    Log::info('Caminho relativo extraído com sucesso', [
                        'appointment_id' => $appointment->id,
                        'calendar_path' => $calendarPath,
                    ]);
                    // Se o caminho foi extraído com sucesso, usar ele diretamente
                    return $calendarPath;
                } else {
                    $calendarPath = null;
                }
            } else {
                // Verificar se o caminho aponta para uma coleção de calendários
                // Se terminar com /calendars/, precisamos descobrir o calendário específico
                if (str_ends_with($calendarPath, '/calendars/') || str_ends_with($calendarPath, '/calendars')) {
                    Log::info('calendar_url aponta para coleção de calendários, descobrindo calendário específico', [
                        'appointment_id' => $appointment->id,
                        'collection_path' => $calendarPath,
                    ]);
                    
                    // Descobrir calendários nesta coleção
                    try {
                        $calendars = $this->listCalendarsInHomeSet($token, $calendarPath);
                        if (!empty($calendars)) {
                            // Ordenar por prioridade (home, work primeiro)
                            usort($calendars, function ($a, $b) {
                                return $this->getCalendarPriority($a['path'], $a['name']) 
                                    <=> $this->getCalendarPriority($b['path'], $b['name']);
                            });
                            
                            // Armazenar lista de calendários para tentar outros se necessário
                            $this->availableCalendars = $calendars;
                            
                            $calendarPath = $calendars[0]['path'];
                            Log::info('✅ Calendário específico descoberto na coleção (priorizado)', [
                                'appointment_id' => $appointment->id,
                                'calendar_path' => $calendarPath,
                                'calendar_name' => $calendars[0]['name'] ?? 'N/A',
                                'total_available' => count($calendars),
                            ]);
                            return $calendarPath;
                        } else {
                            Log::warning('Nenhum calendário encontrado na coleção fornecida', [
                                'appointment_id' => $appointment->id,
                                'collection_path' => $calendarPath,
                            ]);
                            // Continuar para descoberta automática completa
                            $calendarPath = null;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao descobrir calendários na coleção, tentando descoberta automática completa', [
                            'appointment_id' => $appointment->id,
                            'collection_path' => $calendarPath,
                            'error' => $e->getMessage(),
                        ]);
                        $calendarPath = null;
                    }
                } else {
                    // Caminho relativo aparenta estar correto (aponta para um calendário específico)
                    Log::info('Usando calendar_url fornecido (caminho relativo)', [
                        'appointment_id' => $appointment->id,
                        'calendar_path' => $calendarPath,
                    ]);
                    return $calendarPath;
                }
            }
        }
        
        // Se não temos um caminho válido configurado, descobrir automaticamente
        Log::info('calendar_url não fornecido ou inválido, descobrindo automaticamente', [
            'appointment_id' => $appointment->id,
        ]);
        
            try {
                $calendars = $this->discoverCalendars($token);
                if (!empty($calendars)) {
                    // Ordenar por prioridade (home, work primeiro)
                    usort($calendars, function ($a, $b) {
                        return $this->getCalendarPriority($a['path'], $a['name']) 
                            <=> $this->getCalendarPriority($b['path'], $b['name']);
                    });
                    
                    // Armazenar lista de calendários para tentar outros se necessário
                    $this->availableCalendars = $calendars;
                    
                    // Usar o primeiro calendário encontrado (já ordenado por prioridade)
                    $calendarPath = $calendars[0]['path'];
                    Log::info('✅ Calendário descoberto automaticamente com sucesso (priorizado)', [
                        'appointment_id' => $appointment->id,
                        'calendar_path' => $calendarPath,
                        'calendar_name' => $calendars[0]['name'] ?? 'N/A',
                        'total_calendars_found' => count($calendars),
                    ]);
                    return $calendarPath;
            } else {
                // Usar caminho padrão como último recurso (pode não funcionar)
                $calendarPath = $this->getDefaultCalendarPath($token);
                Log::warning('⚠️ Nenhum calendário descoberto automaticamente, usando caminho padrão (pode falhar)', [
                    'appointment_id' => $appointment->id,
                    'calendar_path' => $calendarPath,
                    'suggestion' => 'Configure o calendar_url manualmente se este caminho não funcionar',
                ]);
                return $calendarPath;
            }
        } catch (\Exception $e) {
            Log::error('❌ Erro ao descobrir calendário automaticamente', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'suggestion' => 'Configure o calendar_url manualmente para evitar este erro',
            ]);
            // Último recurso: usar caminho padrão
            return $this->getDefaultCalendarPath($token);
        }
    }

    /**
     * Obtém o caminho padrão do calendário (último recurso)
     * 
     * Este método é usado apenas como fallback quando a descoberta automática falha.
     * Este caminho pode não funcionar e geralmente resulta em erro HTTP 400.
     * 
     * RECOMENDAÇÃO: Configure o calendar_url no token ou corrija a descoberta automática.
     * 
     * @param AppleCalendarToken $token Token de autenticação
     * @return string Caminho padrão (provavelmente não funcionará)
     */
    protected function getDefaultCalendarPath(AppleCalendarToken $token): string
    {
        // Para iCloud, tentar o formato mais comum (geralmente não funciona)
        // O caminho correto precisa do calendar-id específico que só pode ser
        // descoberto via PROPFIND ou configurado manualmente
        return '/calendars/users/' . $token->username . '/';
    }

    /**
     * Descobre os calendários disponíveis para um token
     */
    public function discoverCalendars(AppleCalendarToken $token): array
    {
        try {
            $this->client($token);

            // Primeiro, descobrir o principal do usuário
            $principalPath = $this->discoverUserPrincipal($token);
            
            if ($principalPath) {
                Log::info('Principal do usuário descoberto', [
                    'principal_path' => $principalPath,
                ]);
                
                // Agora descobrir calendários no principal
                $calendars = $this->discoverCalendarsFromPrincipal($token, $principalPath);
                
                if (!empty($calendars)) {
                    return $calendars;
                }
            }
            
            // Fallback: tentar caminhos comuns
            return $this->discoverCalendarsFallback($token);
            
        } catch (\Exception $e) {
            Log::error('Erro ao descobrir calendários do Apple Calendar', [
                'token_id' => $token->id,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Descobre o principal do usuário (current-user-principal)
     */
    protected function discoverUserPrincipal(AppleCalendarToken $token): ?string
    {
        try {
            $response = $this->client->propFind('/', [
                '{DAV:}current-user-principal',
            ], 0);
            
            Log::debug('Resposta do PROPFIND para principal', [
                'response_keys' => array_keys($response),
            ]);
            
            // A resposta pode vir em diferentes formatos
            foreach ($response as $path => $props) {
                if (isset($props['{DAV:}current-user-principal'])) {
                    $principal = $props['{DAV:}current-user-principal'];
                    
                    // O principal pode ser uma string ou um objeto
                    if (is_string($principal)) {
                        return $principal;
                    } elseif (is_object($principal) && method_exists($principal, '__toString')) {
                        return (string) $principal;
                    } elseif (is_array($principal) && isset($principal[0])) {
                        return is_string($principal[0]) ? $principal[0] : null;
                    }
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::debug('Erro ao descobrir principal do usuário', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Descobre calendários a partir do principal do usuário
     */
    protected function discoverCalendarsFromPrincipal(AppleCalendarToken $token, string $principalPath): array
    {
        try {
            // Fazer PROPFIND para descobrir calendários no principal
            $response = $this->client->propFind($principalPath, [
                '{DAV:}displayname',
                '{urn:ietf:params:xml:ns:caldav}calendar-home-set',
            ], 0);
            
            // Procurar pelo calendar-home-set
            $calendarHomeSet = null;
            foreach ($response as $path => $props) {
                if (isset($props['{urn:ietf:params:xml:ns:caldav}calendar-home-set'])) {
                    $homeSet = $props['{urn:ietf:params:xml:ns:caldav}calendar-home-set'];
                    
                    // Normalizar para string
                    if (is_string($homeSet) && !empty($homeSet)) {
                        $calendarHomeSet = $homeSet;
                        break;
                    } elseif (is_object($homeSet) && method_exists($homeSet, '__toString')) {
                        $calendarHomeSet = (string) $homeSet;
                        break;
                    } elseif (is_array($homeSet) && isset($homeSet[0])) {
                        $calendarHomeSet = is_string($homeSet[0]) ? $homeSet[0] : null;
                        if ($calendarHomeSet) break;
                    }
                }
            }
            
            if ($calendarHomeSet) {
                Log::info('Calendar home set descoberto', [
                    'calendar_home_set' => $calendarHomeSet,
                ]);
                
                // Agora listar os calendários no calendar-home-set
                return $this->listCalendarsInHomeSet($token, $calendarHomeSet);
            }
            
            return [];
        } catch (\Exception $e) {
            Log::debug('Erro ao descobrir calendários do principal', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Lista calendários no calendar-home-set
     */
    protected function listCalendarsInHomeSet(AppleCalendarToken $token, string $calendarHomeSet): array
    {
        try {
            $response = $this->client->propFind($calendarHomeSet, [
                '{DAV:}displayname',
                '{urn:ietf:params:xml:ns:caldav}calendar-description',
                '{http://calendarserver.org/ns/}getctag',
                '{DAV:}resourcetype',
            ], 1);
            
            $calendars = [];
            foreach ($response as $path => $props) {
                if ($path === $calendarHomeSet) {
                    continue; // Pular o próprio home set
                }
                
                // Verificar se é um calendário (tem resourcetype com calendar)
                $resourceType = $props['{DAV:}resourcetype'] ?? null;
                $isCalendar = false;
                
                if ($resourceType) {
                    // Verificar se contém o tipo de calendário
                    $resourceTypeStr = is_string($resourceType) ? $resourceType : serialize($resourceType);
                    $isCalendar = str_contains($resourceTypeStr, 'calendar') || 
                                 str_contains($resourceTypeStr, 'CALENDAR');
                }
                
                if ($isCalendar && isset($props['{DAV:}displayname'])) {
                    $calendars[] = [
                        'path' => $path,
                        'name' => $props['{DAV:}displayname'],
                        'description' => $props['{urn:ietf:params:xml:ns:caldav}calendar-description'] ?? null,
                    ];
                    
                    Log::info('Calendário encontrado no home set', [
                        'path' => $path,
                        'name' => $props['{DAV:}displayname'],
                    ]);
                }
            }
            
            // Ordenar calendários para priorizar os editáveis (home, work, pessoal, trabalho)
            usort($calendars, function ($a, $b) {
                $priorityA = $this->getCalendarPriority($a['path'], $a['name']);
                $priorityB = $this->getCalendarPriority($b['path'], $b['name']);
                return $priorityA <=> $priorityB;
            });
            
            return $calendars;
        } catch (\Exception $e) {
            Log::debug('Erro ao listar calendários no home set', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
    
    /**
     * Retorna a prioridade de um calendário para ordenação
     * Calendários com menor número de prioridade são tentados primeiro
     * 
     * @param string $path Caminho do calendário
     * @param string $name Nome do calendário
     * @return int Prioridade (menor = mais prioritário)
     */
    protected function getCalendarPriority(string $path, string $name): int
    {
        $pathLower = strtolower($path);
        $nameLower = strtolower(trim($name));
        
        // Prioridade 1: calendários principais editáveis
        if (str_contains($pathLower, '/home/') || 
            str_contains($nameLower, 'pessoal') || 
            str_contains($nameLower, 'home')) {
            return 1;
        }
        
        // Prioridade 2: calendário de trabalho
        if (str_contains($pathLower, '/work/') || 
            str_contains($nameLower, 'trabalho') || 
            str_contains($nameLower, 'work')) {
            return 2;
        }
        
        // Prioridade 3: outros calendários comuns editáveis
        if (str_contains($nameLower, 'calendário') || 
            str_contains($nameLower, 'calendar')) {
            return 3;
        }
        
        // Prioridade 4: lembretes e outros que podem ser read-only
        if (str_contains($nameLower, 'lembrete') || 
            str_contains($nameLower, 'reminder') ||
            str_contains($nameLower, '⚠️') ||
            str_contains($nameLower, '🔔')) {
            return 10; // Baixa prioridade (geralmente read-only)
        }
        
        // Prioridade 5: outros calendários
        return 5;
    }
    
    /**
     * Método fallback para descobrir calendários usando caminhos comuns
     */
    protected function discoverCalendarsFallback(AppleCalendarToken $token): array
    {
        $pathsToTry = [
            '/calendars/',
            '/calendars/users/' . $token->username . '/',
        ];

        $calendars = [];
        
        foreach ($pathsToTry as $basePath) {
            try {
                Log::debug('Tentando descobrir calendários no caminho (fallback)', [
                    'base_path' => $basePath,
                ]);
                
                $response = $this->client->propFind($basePath, [
                    '{DAV:}displayname',
                    '{urn:ietf:params:xml:ns:caldav}calendar-description',
                ], 1);

                foreach ($response as $path => $props) {
                    if (isset($props['{DAV:}displayname']) && $path !== $basePath) {
                        $calendars[] = [
                            'path' => $path,
                            'name' => $props['{DAV:}displayname'],
                            'description' => $props['{urn:ietf:params:xml:ns:caldav}calendar-description'] ?? null,
                        ];
                    }
                }
                
                if (!empty($calendars)) {
                    break;
                }
            } catch (\Exception $e) {
                Log::debug('Tentativa de descobrir calendário falhou (fallback)', [
                    'path' => $basePath,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return $calendars;
    }
}

