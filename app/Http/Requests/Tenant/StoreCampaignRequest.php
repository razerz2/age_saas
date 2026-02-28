<?php

namespace App\Http\Requests\Tenant;

use App\Services\Tenant\CampaignChannelGate;
use App\Support\Tenant\CampaignPatientRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    private const CHANNEL_UNAVAILABLE_MESSAGE = 'Canal indisponível: configure a integração correspondente em Integrações.';

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

        if (!$this->isAutomatedType()) {
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
            'content_json.whatsapp.provider' => [Rule::requiredIf(fn () => $this->hasChannel('whatsapp')), 'in:waha'],
            'content_json.whatsapp.message_type' => [Rule::requiredIf(fn () => $this->hasChannel('whatsapp')), 'in:text,media'],
            'content_json.whatsapp.text' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp') && $this->input('content_json.whatsapp.message_type') === 'text'),
                'string',
            ],
            'content_json.whatsapp.media' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp') && $this->input('content_json.whatsapp.message_type') === 'media'),
                'array',
            ],
            'content_json.whatsapp.media.kind' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp') && $this->input('content_json.whatsapp.message_type') === 'media'),
                'in:image,video,document,audio',
            ],
            'content_json.whatsapp.media.source' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp') && $this->input('content_json.whatsapp.message_type') === 'media'),
                'in:url,upload',
            ],
            'content_json.whatsapp.media.url' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
                    && $this->input('content_json.whatsapp.message_type') === 'media'
                    && $this->input('content_json.whatsapp.media.source') === 'url'),
                'url',
            ],
            'content_json.whatsapp.media.asset_id' => [
                Rule::requiredIf(fn () => $this->hasChannel('whatsapp')
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
                    $validator->errors()->add('content_json.email', 'O conteúdo de email é obrigatório.');
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
                                    'Anexo inválido: asset_id é obrigatório.'
                                );
                            }
                        }
                    }
                }
            }

            if (in_array('whatsapp', $requestedChannels, true)) {
                $whatsapp = $this->input('content_json.whatsapp');
                if (!is_array($whatsapp)) {
                    $validator->errors()->add('content_json.whatsapp', 'O conteúdo de WhatsApp é obrigatório.');
                    return;
                }

                $messageType = strtolower(trim((string) ($whatsapp['message_type'] ?? '')));
                if ($messageType === 'text' && !$this->isFilled($whatsapp['text'] ?? null)) {
                    $validator->errors()->add('content_json.whatsapp.text', 'O texto é obrigatório quando message_type for text.');
                }

                if ($messageType === 'media') {
                    $media = $whatsapp['media'] ?? null;
                    if (!is_array($media)) {
                        $validator->errors()->add('content_json.whatsapp.media', 'O bloco media é obrigatório quando message_type for media.');
                        return;
                    }

                    $source = strtolower(trim((string) ($media['source'] ?? '')));
                    if ($source === 'url' && !$this->isFilled($media['url'] ?? null)) {
                        $validator->errors()->add('content_json.whatsapp.media.url', 'A URL é obrigatória quando source for url.');
                    }

                    if ($source === 'upload' && !$this->isFilled($media['asset_id'] ?? null)) {
                        $validator->errors()->add('content_json.whatsapp.media.asset_id', 'O asset_id é obrigatório quando source for upload.');
                    }
                }
            }

            if ($this->isAutomatedType() && $this->input('rules_json') !== null) {
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
            'name.required' => 'O nome da campanha é obrigatório.',
            'name.max' => 'O nome da campanha deve ter no máximo 150 caracteres.',
            'type.required' => 'O tipo da campanha é obrigatório.',
            'type.in' => 'O tipo da campanha deve ser manual ou automated.',
            'channels.required' => 'Selecione ao menos um canal.',
            'channels.array' => 'Os canais devem ser enviados em formato de lista.',
            'channels.min' => 'Selecione ao menos um canal.',
            'channels.*.in' => 'Canal inválido. Use email e/ou whatsapp.',
            'content_json.required' => 'O conteúdo da campanha é obrigatório.',
            'content_json.array' => 'content_json deve ser um objeto válido.',
            'content_json.version.required' => 'content_json.version é obrigatório.',
            'content_json.version.in' => 'content_json.version deve ser 1.',
            'audience_json.required' => 'A audiência da campanha é obrigatória.',
            'audience_json.array' => 'audience_json deve ser um objeto válido.',
            'audience_json.version.required' => 'audience_json.version é obrigatório.',
            'audience_json.version.in' => 'audience_json.version deve ser 1.',
            'rules_json.array' => 'Regras devem ser enviadas em formato de objeto.',
            'rules_json.logic.in' => 'A lógica das regras deve ser AND ou OR.',
            'rules_json.conditions.array' => 'As condições devem ser enviadas em lista.',
            'rules_json.conditions.*.field.in' => 'Campo de regra não permitido.',
            'rules_json.conditions.*.op.in' => 'Operador de regra não permitido.',
            'schedule_mode.required' => 'O modo da programação é obrigatório para campanhas agendadas.',
            'schedule_mode.in' => 'O modo da programação deve ser period ou indefinite.',
            'starts_at.required' => 'A data de início é obrigatória para campanhas agendadas.',
            'starts_at.date' => 'A data de início é inválida.',
            'ends_at.required' => 'A data de fim é obrigatória quando o modo for período.',
            'ends_at.date' => 'A data de fim é inválida.',
            'ends_at.after_or_equal' => 'A data de fim deve ser maior ou igual à data de início.',
            'weekdays.required' => 'Selecione pelo menos um dia da semana.',
            'weekdays.array' => 'Os dias da semana devem estar em formato de lista.',
            'weekdays.min' => 'Selecione pelo menos um dia da semana.',
            'weekdays.*.integer' => 'Dia da semana inválido.',
            'weekdays.*.between' => 'Dia da semana inválido. Use valores de 0 a 6.',
            'times.required' => 'Adicione pelo menos um horário.',
            'times.array' => 'Os horários devem estar em formato de lista.',
            'times.min' => 'Adicione pelo menos um horário.',
            'times.*.date_format' => 'Cada horário deve estar no formato HH:MM.',
            'times.*.distinct' => 'Não repita horários.',
            'timezone.required' => 'O timezone da campanha é obrigatório.',
            'timezone.timezone' => 'O timezone informado é inválido.',
            'content_json.email.attachments.*.asset_id.integer' => 'Anexo inválido: asset_id deve ser numérico.',
            'content_json.email.attachments.*.source.in' => 'Anexo inválido: source deve ser upload.',
            'content_json.whatsapp.media.asset_id.integer' => 'O asset_id deve ser numérico quando source for upload.',
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
}
