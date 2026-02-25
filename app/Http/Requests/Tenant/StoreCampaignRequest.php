<?php

namespace App\Http\Requests\Tenant;

use App\Services\Tenant\CampaignChannelGate;
use Illuminate\Foundation\Http\FormRequest;
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

        if (!is_array($rawChannels)) {
            return;
        }

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

            'automation_json' => ['nullable', 'array', 'required_if:type,automated'],
            'automation_json.version' => ['required_if:type,automated', 'integer', 'in:1'],
            'automation_json.trigger' => ['required_if:type,automated', 'in:birthday,inactive_patients'],
            'automation_json.schedule' => ['required_if:type,automated', 'array'],
            'automation_json.schedule.type' => ['required_if:type,automated', 'in:daily'],
            'automation_json.schedule.time' => ['required_if:type,automated', 'date_format:H:i'],
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
            'automation_json.required_if' => 'automation_json é obrigatório para campanhas automated.',
            'automation_json.version.required_if' => 'automation_json.version é obrigatório para campanhas automated.',
            'automation_json.version.in' => 'automation_json.version deve ser 1.',
            'automation_json.trigger.required_if' => 'automation_json.trigger é obrigatório para campanhas automated.',
            'automation_json.trigger.in' => 'automation_json.trigger deve ser birthday ou inactive_patients.',
            'automation_json.schedule.required_if' => 'automation_json.schedule é obrigatório para campanhas automated.',
            'automation_json.schedule.type.required_if' => 'automation_json.schedule.type é obrigatório para campanhas automated.',
            'automation_json.schedule.type.in' => 'automation_json.schedule.type deve ser daily.',
            'automation_json.schedule.time.required_if' => 'automation_json.schedule.time é obrigatório para campanhas automated.',
            'automation_json.schedule.time.date_format' => 'automation_json.schedule.time deve estar no formato HH:MM.',
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
