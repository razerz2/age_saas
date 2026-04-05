<?php

namespace App\Http\Requests\Tenant;

use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\CampaignTemplate;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Support\Tenant\CampaignPatientRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    /** @var array<string,mixed> */
    private array $rawWhatsAppInput = [];
    private const CHANNEL_UNAVAILABLE_MESSAGE = 'Canal indisponÃ­vel para campanhas. Ajuste os canais na aba Campanhas.';
    private const OFFICIAL_TEMPLATE_REQUIRED_MESSAGE = 'Campanhas com WhatsApp Oficial exigem template oficial aprovado pela Meta.';

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rawChannels = $this->input('channels');

        if ($rawChannels === null) {
            $rawChannels = $this->input('channels_json');
        }

        if ($rawChannels !== null && !is_array($rawChannels)) {
            $rawChannels = [$rawChannels];
        }

        if (is_array($rawChannels)) {
            $normalizedChannels = [];
            foreach ($rawChannels as $channel) {
                $normalized = strtolower(trim((string) $channel));
                if ($normalized === '') {
                    continue;
                }

                if (!in_array($normalized, $normalizedChannels, true)) {
                    $normalizedChannels[] = $normalized;
                }
            }

            $this->merge([
                'channels' => $normalizedChannels,
                'channels_json' => $normalizedChannels,
            ]);
        }

        $contentJson = $this->input('content_json', []);
        if (!is_array($contentJson)) {
            $contentJson = [];
        }

        if ($this->hasChannel('whatsapp')) {
            $this->captureRawWhatsAppInput($contentJson);
            $contentJson = $this->normalizeWhatsAppContentPayload($contentJson);
        }

        $this->merge([
            'content_json' => $contentJson,
        ]);

        if (!$this->isAutomatedType()) {
            // "scheduled_at" is managed by explicit manual actions (start/schedule),
            // not by create/edit payload for manual campaigns.
            $this->merge([
                'scheduled_at' => null,
            ]);
            return;
        }

        $scheduleMode = strtolower(trim((string) $this->input('schedule_mode', 'period')));
        if (!in_array($scheduleMode, ['period', 'indefinite'], true)) {
            $scheduleMode = 'period';
        }

        $timezone = trim((string) $this->input('timezone', ''));
        if ($timezone === '') {
            $timezone = $this->resolveTenantTimezone();
        }

        $payload = [
            'schedule_mode' => $scheduleMode,
            'weekdays' => $this->normalizeWeekdays($this->input('weekdays', [])),
            'times' => $this->normalizeTimes($this->input('times', [])),
            'timezone' => $timezone,
        ];

        if ($scheduleMode === 'indefinite') {
            $payload['ends_at'] = null;
        }

        $rawRules = $this->input('rules_json');
        if (is_array($rawRules)) {
            $rawConditions = $rawRules['conditions'] ?? [];
            $conditions = [];

            if (is_array($rawConditions)) {
                foreach ($rawConditions as $condition) {
                    if (!is_array($condition)) {
                        continue;
                    }

                    $conditions[] = [
                        'field' => strtolower(trim((string) ($condition['field'] ?? ''))),
                        'op' => strtolower(trim((string) ($condition['op'] ?? ''))),
                        'value' => $condition['value'] ?? null,
                    ];
                }
            }

            $payload['rules_json'] = [
                'logic' => strtoupper(trim((string) ($rawRules['logic'] ?? 'AND'))),
                'conditions' => $conditions,
            ];
        }

        $this->merge($payload);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:manual,automated'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'in:email,whatsapp'],

            'content_json' => ['required', 'array'],
            'content_json.version' => ['required', 'integer', 'in:1'],

            'content_json.email' => [Rule::requiredIf(fn () => $this->hasChannel('email')), 'array'],
            'content_json.email.subject' => [Rule::requiredIf(fn () => $this->hasChannel('email')), 'string', 'max:150'],
            'content_json.email.body_html' => ['nullable', 'string'],
            'content_json.email.body_text' => ['nullable', 'string'],
            'content_json.email.attachments' => ['nullable', 'array'],
            'content_json.email.attachments.*' => ['array'],
            'content_json.email.attachments.*.source' => ['nullable', 'in:upload'],
            'content_json.email.attachments.*.asset_id' => ['nullable', 'integer'],
            'content_json.email.attachments.*.filename' => ['nullable', 'string', 'max:255'],
            'content_json.email.attachments.*.mime' => ['nullable', 'string', 'max:150'],
            'content_json.email.attachments.*.size' => ['nullable', 'integer', 'min:1'],

            'content_json.whatsapp' => [Rule::requiredIf(fn () => $this->hasChannel('whatsapp')), 'array'],
            'content_json.whatsapp.provider' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')),
                Rule::in(['whatsapp_business', 'zapi', 'waha', 'evolution']),
            ],
            'content_json.whatsapp.composition_mode' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')),
                Rule::in(['manual', 'template']),
            ],
            'content_json.whatsapp.template_type' => ['nullable', Rule::in(['official', 'unofficial'])],
            'content_json.whatsapp.official_template_id' => ['nullable', 'string', 'max:64'],
            'content_json.whatsapp.template_id' => ['nullable', 'integer'],
            'content_json.whatsapp.message_type' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp') && $this->isWhatsappManualMode()),
                'in:text,media',
            ],
            'content_json.whatsapp.text' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'text'),
                'string',
            ],
            'content_json.whatsapp.media' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'media'),
                'array',
            ],
            'content_json.whatsapp.media.kind' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'media'),
                'in:image,video,document,audio',
            ],
            'content_json.whatsapp.media.source' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'media'),
                'in:url,upload',
            ],
            'content_json.whatsapp.media.url' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'media'
                    && $this->input('content_json.whatsapp.media.source') === 'url'),
                'url',
            ],
            'content_json.whatsapp.media.asset_id' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->isWhatsappManualMode()
                    && $this->input('content_json.whatsapp.message_type') === 'media'
                    && $this->input('content_json.whatsapp.media.source') === 'upload'),
                'integer',
            ],

            'audience_json' => ['required', 'array'],
            'audience_json.version' => ['required', 'integer', 'in:1'],

            'rules_json' => ['nullable', 'array'],
            'rules_json.logic' => ['nullable', 'in:AND,OR'],
            'rules_json.conditions' => ['nullable', 'array'],
            'rules_json.conditions.*' => ['array'],
            'rules_json.conditions.*.field' => ['nullable', Rule::in(CampaignPatientRules::allowedFields())],
            'rules_json.conditions.*.op' => ['nullable', Rule::in(CampaignPatientRules::allowedOperators())],
            'rules_json.conditions.*.value' => ['nullable'],

            'schedule_mode' => [Rule::requiredIf(fn () => $this->isAutomatedType()), 'in:period,indefinite'],
            'starts_at' => [Rule::requiredIf(fn () => $this->isAutomatedType()), 'date'],
            'ends_at' => [
                Rule::requiredIf(fn () => $this->isAutomatedType() && $this->isPeriodMode()),
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
            'weekdays' => [Rule::requiredIf(fn () => $this->isAutomatedType()), 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:0,6'],
            'times' => [Rule::requiredIf(fn () => $this->isAutomatedType()), 'array', 'min:1'],
            'times.*' => ['date_format:H:i', 'distinct'],
            'timezone' => [Rule::requiredIf(fn () => $this->isAutomatedType()), 'string', 'timezone'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $requestedChannels = $this->requestedChannels();
            $availableChannels = app(CampaignChannelGate::class)->availableChannels();

            foreach ($requestedChannels as $channel) {
                if (!in_array($channel, $availableChannels, true)) {
                    $validator->errors()->add('channels', self::CHANNEL_UNAVAILABLE_MESSAGE);
                    break;
                }
            }

            if (in_array('email', $requestedChannels, true)) {
                $emailContent = $this->input('content_json.email');
                if (!is_array($emailContent)) {
                    $validator->errors()->add('content_json.email', 'O conteÃºdo de email Ã© obrigatÃ³rio.');
                } else {
                    $hasBodyHtml = $this->isFilled($emailContent['body_html'] ?? null);
                    $hasBodyText = $this->isFilled($emailContent['body_text'] ?? null);

                    if (!$hasBodyHtml && !$hasBodyText) {
                        $validator->errors()->add('content_json.email.body_html', 'Informe body_html ou body_text para o canal email.');
                    }

                    $attachments = $emailContent['attachments'] ?? [];
                    if (is_array($attachments)) {
                        foreach ($attachments as $index => $attachment) {
                            if (!is_array($attachment)) {
                                continue;
                            }

                            $source = strtolower(trim((string) ($attachment['source'] ?? '')));
                            if ($source === 'upload' && !$this->isFilled($attachment['asset_id'] ?? null)) {
                                $validator->errors()->add(
                                    'content_json.email.attachments.' . $index . '.asset_id',
                                    'Anexo invÃ¡lido: asset_id Ã© obrigatÃ³rio.'
                                );
                            }
                        }
                    }
                }
            }

            if (in_array('whatsapp', $requestedChannels, true)) {
                $this->validateWhatsAppPayload($validator);
            }

            if ($this->input('rules_json') !== null) {
                $rulesValidation = CampaignPatientRules::validateAndNormalize($this->input('rules_json'));
                foreach ($rulesValidation['errors'] as $key => $message) {
                    $validator->errors()->add($key, $message);
                }
            }
        });
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome da campanha Ã© obrigatÃ³rio.',
            'name.max' => 'O nome da campanha deve ter no mÃ¡ximo 150 caracteres.',
            'type.required' => 'O tipo da campanha Ã© obrigatÃ³rio.',
            'type.in' => 'O tipo da campanha deve ser manual ou automated.',
            'channels.required' => 'Selecione ao menos um canal.',
            'channels.array' => 'Os canais devem ser enviados em formato de lista.',
            'channels.min' => 'Selecione ao menos um canal.',
            'channels.*.in' => 'Canal invÃ¡lido. Use email e/ou whatsapp.',
            'content_json.required' => 'O conteÃºdo da campanha Ã© obrigatÃ³rio.',
            'content_json.array' => 'content_json deve ser um objeto vÃ¡lido.',
            'content_json.version.required' => 'content_json.version Ã© obrigatÃ³rio.',
            'content_json.version.in' => 'content_json.version deve ser 1.',
            'audience_json.required' => 'A audiÃªncia da campanha Ã© obrigatÃ³ria.',
            'audience_json.array' => 'audience_json deve ser um objeto vÃ¡lido.',
            'audience_json.version.required' => 'audience_json.version Ã© obrigatÃ³rio.',
            'audience_json.version.in' => 'audience_json.version deve ser 1.',
            'rules_json.array' => 'Regras devem ser enviadas em formato de objeto.',
            'rules_json.logic.in' => 'A lÃ³gica das regras deve ser AND ou OR.',
            'rules_json.conditions.array' => 'As condiÃ§Ãµes devem ser enviadas em lista.',
            'rules_json.conditions.*.field.in' => 'Campo de regra nÃ£o permitido.',
            'rules_json.conditions.*.op.in' => 'Operador de regra nÃ£o permitido.',
            'schedule_mode.required' => 'O modo da programaÃ§Ã£o Ã© obrigatÃ³rio para campanhas agendadas.',
            'schedule_mode.in' => 'O modo da programaÃ§Ã£o deve ser period ou indefinite.',
            'starts_at.required' => 'A data de inÃ­cio Ã© obrigatÃ³ria para campanhas agendadas.',
            'starts_at.date' => 'A data de inÃ­cio Ã© invÃ¡lida.',
            'ends_at.required' => 'A data de fim Ã© obrigatÃ³ria quando o modo for perÃ­odo.',
            'ends_at.date' => 'A data de fim Ã© invÃ¡lida.',
            'ends_at.after_or_equal' => 'A data de fim deve ser maior ou igual Ã  data de inÃ­cio.',
            'weekdays.required' => 'Selecione pelo menos um dia da semana.',
            'weekdays.array' => 'Os dias da semana devem estar em formato de lista.',
            'weekdays.min' => 'Selecione pelo menos um dia da semana.',
            'weekdays.*.integer' => 'Dia da semana invÃ¡lido.',
            'weekdays.*.between' => 'Dia da semana invÃ¡lido. Use valores de 0 a 6.',
            'times.required' => 'Adicione pelo menos um horÃ¡rio.',
            'times.array' => 'Os horÃ¡rios devem estar em formato de lista.',
            'times.min' => 'Adicione pelo menos um horÃ¡rio.',
            'times.*.date_format' => 'Cada horÃ¡rio deve estar no formato HH:MM.',
            'times.*.distinct' => 'NÃ£o repita horÃ¡rios.',
            'timezone.required' => 'O timezone da campanha Ã© obrigatÃ³rio.',
            'timezone.timezone' => 'O timezone informado Ã© invÃ¡lido.',
            'content_json.email.attachments.*.asset_id.integer' => 'Anexo invÃ¡lido: asset_id deve ser numÃ©rico.',
            'content_json.email.attachments.*.source.in' => 'Anexo invÃ¡lido: source deve ser upload.',
            'content_json.whatsapp.media.asset_id.integer' => 'O asset_id deve ser numÃ©rico quando source for upload.',
            'content_json.whatsapp.composition_mode.in' => 'A composiÃ§Ã£o da mensagem deve ser manual ou template.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function requestedChannels(): array
    {
        $channels = $this->input('channels', []);
        if (!is_array($channels)) {
            return [];
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $channelValue = strtolower(trim((string) $channel));
            if ($channelValue === '') {
                continue;
            }

            if (!in_array($channelValue, $normalized, true)) {
                $normalized[] = $channelValue;
            }
        }

        return $normalized;
    }

    private function hasChannel(string $channel): bool
    {
        return in_array($channel, $this->requestedChannels(), true);
    }

    private function isAutomatedType(): bool
    {
        return strtolower(trim((string) $this->input('type', 'manual'))) === 'automated';
    }

    private function isPeriodMode(): bool
    {
        return strtolower(trim((string) $this->input('schedule_mode', 'period'))) === 'period';
    }

    private function isWhatsappManualMode(): bool
    {
        if (!$this->hasChannel('whatsapp')) {
            return false;
        }

        return $this->resolveWhatsappCompositionMode() === 'manual';
    }

    /**
     * @param mixed $weekdays
     * @return array<int, int>
     */
    private function normalizeWeekdays(mixed $weekdays): array
    {
        $values = Arr::wrap($weekdays);
        $normalized = [];

        foreach ($values as $value) {
            if (!is_numeric($value)) {
                continue;
            }

            $day = (int) $value;
            if ($day < 0 || $day > 6) {
                continue;
            }

            if (!in_array($day, $normalized, true)) {
                $normalized[] = $day;
            }
        }

        sort($normalized);

        return $normalized;
    }

    /**
     * @param mixed $times
     * @return array<int, string>
     */
    private function normalizeTimes(mixed $times): array
    {
        $values = Arr::wrap($times);
        $normalized = [];

        foreach ($values as $value) {
            $time = $this->normalizeTime((string) $value);
            if ($time === null) {
                continue;
            }

            if (!in_array($time, $normalized, true)) {
                $normalized[] = $time;
            }
        }

        sort($normalized);

        return $normalized;
    }

    private function normalizeTime(string $value): ?string
    {
        $trimmed = trim($value);
        if (preg_match('/^\d{2}:\d{2}$/', $trimmed) !== 1) {
            return null;
        }

        [$hourRaw, $minuteRaw] = explode(':', $trimmed, 2);
        $hour = (int) $hourRaw;
        $minute = (int) $minuteRaw;

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function resolveTenantTimezone(): string
    {
        $fallback = (string) config('app.timezone', 'America/Sao_Paulo');
        $rawTimezone = function_exists('tenant_setting')
            ? (string) tenant_setting('timezone', $fallback)
            : $fallback;

        $timezone = trim($rawTimezone);
        if ($timezone === '') {
            return $fallback;
        }

        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    private function isFilled($value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    /**
     * @param array<string,mixed> $contentJson
     * @return array<string,mixed>
     */
    private function normalizeWhatsAppContentPayload(array $contentJson): array
    {
        $whatsapp = is_array($contentJson['whatsapp'] ?? null) ? $contentJson['whatsapp'] : [];
        $provider = $this->effectiveWhatsAppProvider();
        $isOfficialProvider = $this->isOfficialWhatsAppProvider();

        $compositionMode = strtolower(trim((string) ($whatsapp['composition_mode'] ?? '')));
        if (!in_array($compositionMode, ['manual', 'template'], true)) {
            $compositionMode = $isOfficialProvider ? 'template' : 'manual';
        }

        $templateType = strtolower(trim((string) ($whatsapp['template_type'] ?? '')));
        if ($compositionMode === 'template') {
            if (!in_array($templateType, ['official', 'unofficial'], true)) {
                $templateType = $isOfficialProvider ? 'official' : 'unofficial';
            }
        } else {
            $templateType = '';
        }

        $whatsapp['provider'] = $provider;
        $whatsapp['composition_mode'] = $compositionMode;
        if ($templateType !== '') {
            $whatsapp['template_type'] = $templateType;
        } else {
            unset($whatsapp['template_type']);
        }

        $whatsapp = $this->stripIncompatibleWhatsAppFields(
            $whatsapp,
            $provider,
            $compositionMode,
            $templateType
        );

        $contentJson['whatsapp'] = $whatsapp;

        return $contentJson;
    }

    private function validateWhatsAppPayload($validator): void
    {
        $whatsapp = $this->input('content_json.whatsapp');
        if (!is_array($whatsapp)) {
            $validator->errors()->add('content_json.whatsapp', 'O conteÃºdo de WhatsApp Ã© obrigatÃ³rio.');
            return;
        }

        $provider = strtolower(trim((string) ($whatsapp['provider'] ?? '')));
        $effectiveProvider = $this->effectiveWhatsAppProvider();
        if ($provider !== $effectiveProvider) {
            $validator->errors()->add('content_json.whatsapp.provider', 'O provider efetivo de campanhas Ã© diferente do provider informado.');
        }

        $compositionMode = $this->resolveWhatsappCompositionMode();
        $templateType = strtolower(trim((string) ($whatsapp['template_type'] ?? '')));

        if ($this->isOfficialWhatsAppProvider()) {
            if ($compositionMode !== 'template') {
                $validator->errors()->add('content_json.whatsapp.composition_mode', self::OFFICIAL_TEMPLATE_REQUIRED_MESSAGE);
            }

            if ($templateType !== 'official') {
                $validator->errors()->add('content_json.whatsapp.template_type', self::OFFICIAL_TEMPLATE_REQUIRED_MESSAGE);
            }

            $officialTemplateId = trim((string) ($whatsapp['official_template_id'] ?? ''));
            if ($officialTemplateId === '') {
                $validator->errors()->add('content_json.whatsapp.official_template_id', 'Selecione um template oficial aprovado.');
                return;
            }

            if (!$this->resolveApprovedOfficialTemplate($officialTemplateId)) {
                $validator->errors()->add('content_json.whatsapp.official_template_id', 'O template oficial selecionado Ã© invÃ¡lido ou nÃ£o estÃ¡ aprovado.');
            }
            $this->assertNoManualFieldsForOfficialProvider($validator);
            $this->assertNoUnofficialTemplateFieldsForOfficialProvider($validator);

            return;
        }

        if ($compositionMode === 'template') {
            if ($templateType !== 'unofficial') {
                $validator->errors()->add('content_json.whatsapp.template_type', 'Selecione um template nÃ£o oficial para o modo template.');
            }

            $templateId = $this->normalizeNullableInt($whatsapp['template_id'] ?? null);
            if (!$templateId) {
                $validator->errors()->add('content_json.whatsapp.template_id', 'Selecione um template nÃ£o oficial ativo.');
                return;
            }

            if (!$this->resolveActiveCampaignTemplate($templateId)) {
                $validator->errors()->add('content_json.whatsapp.template_id', 'O template nÃ£o oficial selecionado Ã© invÃ¡lido ou estÃ¡ inativo.');
            }
            $this->assertNoOfficialTemplateFieldsForUnofficialProvider($validator);
            $this->assertNoManualFieldsForUnofficialTemplateMode($validator);

            return;
        }

        $this->assertNoTemplateFieldsForUnofficialManualMode($validator);

        $messageType = strtolower(trim((string) ($whatsapp['message_type'] ?? '')));
        if ($messageType === 'text' && !$this->isFilled($whatsapp['text'] ?? null)) {
            $validator->errors()->add('content_json.whatsapp.text', 'O texto Ã© obrigatÃ³rio quando message_type for text.');
        }

        if ($messageType === 'media') {
            $media = $whatsapp['media'] ?? null;
            if (!is_array($media)) {
                $validator->errors()->add('content_json.whatsapp.media', 'O bloco media Ã© obrigatÃ³rio quando message_type for media.');
                return;
            }

            $source = strtolower(trim((string) ($media['source'] ?? '')));
            if ($source === 'url' && !$this->isFilled($media['url'] ?? null)) {
                $validator->errors()->add('content_json.whatsapp.media.url', 'A URL Ã© obrigatÃ³ria quando source for url.');
            }

            if ($source === 'upload' && !$this->isFilled($media['asset_id'] ?? null)) {
                $validator->errors()->add('content_json.whatsapp.media.asset_id', 'O asset_id Ã© obrigatÃ³rio quando source for upload.');
            }
        }
    }

    private function resolveWhatsappCompositionMode(): string
    {
        $rawMode = strtolower(trim((string) $this->input('content_json.whatsapp.composition_mode', '')));
        if (in_array($rawMode, ['manual', 'template'], true)) {
            return $rawMode;
        }

        return $this->isOfficialWhatsAppProvider() ? 'template' : 'manual';
    }

    /**
     * @param array<string,mixed> $contentJson
     */
    private function captureRawWhatsAppInput(array $contentJson): void
    {
        $raw = $contentJson['whatsapp'] ?? null;
        $this->rawWhatsAppInput = is_array($raw) ? $raw : [];
    }

    /**
     * @param array<string,mixed> $whatsapp
     * @return array<string,mixed>
     */
    private function stripIncompatibleWhatsAppFields(
        array $whatsapp,
        string $provider,
        string $compositionMode,
        string $templateType
    ): array {
        if ($compositionMode === 'manual') {
            unset($whatsapp['template_type'], $whatsapp['template_id'], $whatsapp['official_template_id']);
            return $whatsapp;
        }

        unset($whatsapp['message_type'], $whatsapp['text'], $whatsapp['media']);

        if ($provider === 'whatsapp_business') {
            unset($whatsapp['template_id']);
            return $whatsapp;
        }

        if ($templateType === 'unofficial') {
            unset($whatsapp['official_template_id']);
            return $whatsapp;
        }

        unset($whatsapp['template_id']);

        return $whatsapp;
    }

    private function assertNoManualFieldsForOfficialProvider($validator): void
    {
        if ($this->rawHasFilled('message_type')) {
            $validator->errors()->add('content_json.whatsapp.message_type', 'Mensagem manual nao e permitida quando o provider efetivo e WhatsApp Oficial.');
        }

        if ($this->rawHasFilled('text')) {
            $validator->errors()->add('content_json.whatsapp.text', 'Texto manual nao e permitido quando o provider efetivo e WhatsApp Oficial.');
        }

        if ($this->rawHasFilled('media')) {
            $validator->errors()->add('content_json.whatsapp.media', 'Midia manual nao e permitida quando o provider efetivo e WhatsApp Oficial.');
        }
    }

    private function assertNoUnofficialTemplateFieldsForOfficialProvider($validator): void
    {
        if ($this->rawHasFilled('template_id')) {
            $validator->errors()->add('content_json.whatsapp.template_id', 'Template nao oficial nao pode ser informado quando o provider efetivo e WhatsApp Oficial.');
        }
    }

    private function assertNoOfficialTemplateFieldsForUnofficialProvider($validator): void
    {
        if ($this->rawHasFilled('official_template_id')) {
            $validator->errors()->add('content_json.whatsapp.official_template_id', 'Template oficial nao pode ser informado para provider nao oficial.');
        }
    }

    private function assertNoManualFieldsForUnofficialTemplateMode($validator): void
    {
        if ($this->rawHasFilled('message_type') || $this->rawHasFilled('text') || $this->rawHasFilled('media')) {
            $validator->errors()->add('content_json.whatsapp.composition_mode', 'Campos de mensagem manual nao sao aceitos quando a composicao esta em template.');
        }
    }

    private function assertNoTemplateFieldsForUnofficialManualMode($validator): void
    {
        if ($this->rawHasFilled('template_id')) {
            $validator->errors()->add('content_json.whatsapp.template_id', 'Template nao oficial nao pode ser informado quando a composicao esta em mensagem manual.');
        }

        if ($this->rawHasFilled('official_template_id')) {
            $validator->errors()->add('content_json.whatsapp.official_template_id', 'Template oficial nao pode ser informado quando a composicao esta em mensagem manual.');
        }

        $rawTemplateType = strtolower(trim((string) data_get($this->rawWhatsAppInput, 'template_type', '')));
        if ($rawTemplateType !== '') {
            $validator->errors()->add('content_json.whatsapp.template_type', 'template_type nao pode ser informado quando a composicao esta em mensagem manual.');
        }
    }

    private function rawHasFilled(string $path): bool
    {
        return $this->isFilled(data_get($this->rawWhatsAppInput, $path));
    }

    private function isOfficialWhatsAppProvider(): bool
    {
        return app(CampaignTemplateProviderResolver::class)->isOfficialWhatsApp();
    }

    private function effectiveWhatsAppProvider(): string
    {
        return app(CampaignTemplateProviderResolver::class)->resolveWhatsAppProvider();
    }

    private function resolveApprovedOfficialTemplate(string $officialTemplateId): ?WhatsAppOfficialTemplate
    {
        $tenantId = trim((string) (tenant()?->id ?? ''));
        if ($tenantId === '') {
            return null;
        }

        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->approved()
            ->find($officialTemplateId);
    }

    private function resolveActiveCampaignTemplate(int $templateId): ?CampaignTemplate
    {
        return CampaignTemplate::query()
            ->forWhatsApp()
            ->unofficial()
            ->where('is_active', true)
            ->find($templateId);
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        $normalized = (int) $raw;
        return $normalized > 0 ? $normalized : null;
    }
}
