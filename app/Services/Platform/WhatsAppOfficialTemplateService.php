<?php

namespace App\Services\Platform;

use App\Exceptions\WhatsAppMetaApiException;
use App\Exceptions\WhatsAppMetaConfigurationException;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\WhatsApp\MetaCloudTemplateApiService;
use App\Support\WhatsAppOfficialTemplateValidator;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppOfficialTemplateService
{
    public function __construct(
        private readonly MetaCloudTemplateApiService $metaApiService
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTemplate(array $data, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive(isset($data['provider']) ? (string) $data['provider'] : null);
        $actorId = $this->normalizeActorId($actorId);

        $provider = WhatsAppOfficialTemplate::PROVIDER;
        $key = (string) $data['key'];
        $requestedVersion = isset($data['version']) ? (int) $data['version'] : null;
        $version = $this->resolveVersionForCreate($provider, $key, $requestedVersion);

        $template = new WhatsAppOfficialTemplate($data);
        $template->provider = $provider;
        $template->version = $version;
        $template->status = (string) ($data['status'] ?? WhatsAppOfficialTemplate::STATUS_DRAFT);
        $template->created_by = $actorId;
        $template->updated_by = $actorId;
        $template->save();

        Log::info('wa_official_template_created', $this->context($template));

        return $template;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTemplate(WhatsAppOfficialTemplate $template, array $data, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);

        if ($template->isDirectlyEditable()) {
            $template->fill($this->sanitizeUpdatableData($data));
            $template->updated_by = $actorId;
            $template->save();

            Log::info('wa_official_template_updated_directly', $this->context($template));
            return $template;
        }

        if ($template->requiresVersioningForEdit()) {
            $newVersion = $this->duplicateVersion($template, $actorId, $data);

            Log::info('wa_official_template_auto_versioned_from_approved', array_merge(
                $this->context($template),
                ['new_version' => $newVersion->version]
            ));

            return $newVersion;
        }

        throw new DomainException(
            'Template não pode ser editado no status atual. Edite apenas drafts/rejected ou crie nova versão.'
        );
    }

    /**
     * @param array<string, mixed> $overrides
     */
    public function duplicateVersion(
        WhatsAppOfficialTemplate $template,
        ?string $actorId = null,
        array $overrides = []
    ): WhatsAppOfficialTemplate {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);

        $nextVersion = $this->nextVersion($template->provider, $template->key);
        $data = [
            'tenant_id' => $template->tenant_id,
            'key' => $template->key,
            'meta_template_name' => $template->meta_template_name,
            'provider' => $template->provider,
            'category' => $template->category,
            'language' => $template->language,
            'header_text' => $template->header_text,
            'body_text' => $template->body_text,
            'footer_text' => $template->footer_text,
            'buttons' => $template->buttons,
            'variables' => $template->variables,
            'sample_variables' => $template->sample_variables,
        ];
        $data = array_merge($data, $this->sanitizeUpdatableData($overrides));
        $data['key'] = $template->key;
        $data['provider'] = WhatsAppOfficialTemplate::PROVIDER;

        $newTemplate = new WhatsAppOfficialTemplate($data);
        $newTemplate->provider = WhatsAppOfficialTemplate::PROVIDER;
        $newTemplate->version = $nextVersion;
        $newTemplate->status = WhatsAppOfficialTemplate::STATUS_DRAFT;
        $newTemplate->meta_template_id = null;
        $newTemplate->meta_waba_id = null;
        $newTemplate->meta_response = null;
        $newTemplate->last_synced_at = null;
        $newTemplate->created_by = $actorId;
        $newTemplate->updated_by = $actorId;
        $newTemplate->save();

        Log::info('wa_official_template_version_duplicated', array_merge(
            $this->context($newTemplate),
            ['source_template_id' => $template->id]
        ));

        return $newTemplate;
    }

    public function archiveTemplate(WhatsAppOfficialTemplate $template, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);

        $template->status = WhatsAppOfficialTemplate::STATUS_ARCHIVED;
        $template->updated_by = $actorId;
        $template->save();

        Log::info('wa_official_template_archived', $this->context($template));
        return $template;
    }

    /**
     * @throws WhatsAppMetaConfigurationException
     * @throws WhatsAppMetaApiException
     */
    public function submitToMeta(WhatsAppOfficialTemplate $template, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);
        if ($template->status === WhatsAppOfficialTemplate::STATUS_ARCHIVED) {
            throw new DomainException('Template arquivado não pode ser enviado para a Meta.');
        }

        $this->assertTemplateReadyForMetaSubmission($template);

        $payload = $this->buildMetaPayload($template);
        $context = $this->context($template);

        $response = $this->metaApiService->createTemplate($payload, $context);
        $remoteStatus = strtoupper((string) ($response['status'] ?? $response['data']['status'] ?? 'PENDING'));

        $template->status = $this->mapMetaStatusToLocal($remoteStatus);
        $template->meta_template_id = (string) ($response['id'] ?? $response['template_id'] ?? $template->meta_template_id);
        $template->meta_waba_id = $this->metaApiService->getWabaId();
        $template->meta_response = $response;
        $template->last_synced_at = Carbon::now();
        $template->updated_by = $actorId;
        $template->save();

        Log::info('wa_official_template_submitted_to_meta', array_merge(
            $this->context($template),
            ['meta_status' => $remoteStatus]
        ));

        return $template;
    }

    /**
     * @throws WhatsAppMetaConfigurationException
     * @throws WhatsAppMetaApiException
     */
    public function republishAsNewTemplate(WhatsAppOfficialTemplate $template, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);

        if ($template->status === WhatsAppOfficialTemplate::STATUS_ARCHIVED) {
            throw new DomainException('Template arquivado não pode ser republicado na Meta.');
        }

        $this->assertTemplateReadyForMetaSubmission($template);

        $previousRemoteId = $template->meta_template_id ? (string) $template->meta_template_id : null;
        $previousRemoteStatus = (string) $template->status;
        $previousMetaResponse = $template->meta_response;

        // Descarta o vinculo remoto antigo apenas no contexto desta nova submissao.
        $template->meta_template_id = null;
        $template->meta_waba_id = null;
        $template->meta_response = null;
        $template->last_synced_at = null;
        $template->status = WhatsAppOfficialTemplate::STATUS_DRAFT;

        Log::info('wa_official_template_republish_requested', array_merge(
            $this->context($template),
            [
                'previous_remote_template_id' => $previousRemoteId,
                'previous_status' => $previousRemoteStatus,
                'previous_meta_snapshot_present' => is_array($previousMetaResponse),
            ]
        ));

        $republishedTemplate = $this->submitToMeta($template, $actorId);

        Log::info('wa_official_template_republished_to_meta', array_merge(
            $this->context($republishedTemplate),
            [
                'previous_remote_template_id' => $previousRemoteId,
                'new_remote_template_id' => $republishedTemplate->meta_template_id,
            ]
        ));

        return $republishedTemplate;
    }

    /**
     * @throws WhatsAppMetaConfigurationException
     * @throws WhatsAppMetaApiException
     */
    public function syncStatus(WhatsAppOfficialTemplate $template, ?string $actorId = null): WhatsAppOfficialTemplate
    {
        $this->assertOfficialProviderActive((string) $template->provider);
        $actorId = $this->normalizeActorId($actorId);

        $response = $this->metaApiService->fetchTemplateByNameAndLanguage(
            $template->meta_template_name,
            $template->language
        );

        $remoteTemplate = $this->findRemoteTemplate(
            (array) ($response['data'] ?? []),
            $template->meta_template_name,
            $template->language
        );

        $template->meta_response = $response;
        $template->meta_waba_id = $this->metaApiService->getWabaId();
        $template->last_synced_at = Carbon::now();
        $template->updated_by = $actorId;

        if ($remoteTemplate !== null) {
            $remoteStatus = strtoupper((string) ($remoteTemplate['status'] ?? 'PENDING'));
            $template->status = $this->mapMetaStatusToLocal($remoteStatus);
            $template->meta_template_id = (string) ($remoteTemplate['id'] ?? $template->meta_template_id);
        }

        $template->save();

        Log::info('wa_official_template_status_synced', array_merge(
            $this->context($template),
            ['remote_template_found' => $remoteTemplate !== null]
        ));

        return $template;
    }

    public function resolveApprovedTemplate(string $key): ?WhatsAppOfficialTemplate
    {
        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forPlatformBaseline()
            ->byKey($key)
            ->approved()
            ->orderByDesc('version')
            ->first();
    }

    private function assertOfficialProviderActive(?string $providerFallback = null): void
    {
        $provider = $this->resolveActiveProvider($providerFallback);

        if (!in_array($provider, ['whatsapp_business', 'business'], true)) {
            throw new DomainException(
                'Provider ativo incompatível para módulo de templates oficiais. Configure WHATSAPP_PROVIDER=whatsapp_business.'
            );
        }
    }

    private function resolveActiveProvider(?string $providerFallback = null): string
    {
        $runtimeProvider = strtolower(trim((string) config('services.whatsapp.runtime_provider', '')));
        $forceRuntimeProvider = (bool) config('services.whatsapp.force_runtime_provider', false);
        if ($forceRuntimeProvider && $runtimeProvider !== '') {
            return $runtimeProvider;
        }

        $provider = function_exists('sysconfig')
            ? strtolower(trim((string) sysconfig('WHATSAPP_PROVIDER', '')))
            : '';
        if ($provider !== '') {
            return $provider;
        }

        $fallback = strtolower(trim((string) $providerFallback));
        if ($fallback !== '') {
            return $fallback;
        }

        $configured = strtolower(trim((string) config('services.whatsapp.provider', 'whatsapp_business')));
        return $configured !== '' ? $configured : 'whatsapp_business';
    }

    private function nextVersion(string $provider, string $key): int
    {
        $maxVersion = (int) WhatsAppOfficialTemplate::query()
            ->where('provider', $provider)
            ->where('key', $key)
            ->max('version');

        return max(1, $maxVersion + 1);
    }

    private function resolveVersionForCreate(string $provider, string $key, ?int $requestedVersion = null): int
    {
        if ($requestedVersion !== null && $requestedVersion > 0) {
            $exists = WhatsAppOfficialTemplate::query()
                ->where('provider', $provider)
                ->where('key', $key)
                ->where('version', $requestedVersion)
                ->exists();

            if (!$exists) {
                return $requestedVersion;
            }
        }

        return $this->nextVersion($provider, $key);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeUpdatableData(array $data): array
    {
        $allowed = [
            'key',
            'meta_template_name',
            'category',
            'language',
            'header_text',
            'body_text',
            'footer_text',
            'buttons',
            'variables',
            'sample_variables',
        ];

        $payload = ['provider' => WhatsAppOfficialTemplate::PROVIDER];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    /**
     * @param array<int, array<string, mixed>> $remoteData
     * @return array<string, mixed>|null
     */
    private function findRemoteTemplate(array $remoteData, string $name, string $language): ?array
    {
        $targetName = strtolower(trim($name));
        $targetLanguage = strtolower(trim($language));

        foreach ($remoteData as $item) {
            $itemName = strtolower(trim((string) ($item['name'] ?? '')));
            $itemLanguage = strtolower(trim((string) ($item['language'] ?? $item['locale'] ?? '')));

            if ($itemName === $targetName && $itemLanguage === $targetLanguage) {
                return $item;
            }
        }

        return null;
    }

    private function mapMetaStatusToLocal(string $status): string
    {
        return match ($status) {
            'APPROVED' => WhatsAppOfficialTemplate::STATUS_APPROVED,
            'REJECTED' => WhatsAppOfficialTemplate::STATUS_REJECTED,
            'ARCHIVED' => WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            'PAUSED', 'DISABLED' => WhatsAppOfficialTemplate::STATUS_ARCHIVED,
            'PENDING', 'IN_REVIEW', 'PENDING_REVIEW' => WhatsAppOfficialTemplate::STATUS_PENDING,
            default => WhatsAppOfficialTemplate::STATUS_PENDING,
        };
    }

    private function assertTemplateReadyForMetaSubmission(WhatsAppOfficialTemplate $template): void
    {
        $metaTemplateName = strtolower(trim((string) $template->meta_template_name));
        if ($metaTemplateName === '' || preg_match('/^[a-z0-9_]+$/', $metaTemplateName) !== 1) {
            throw new DomainException(
                'Template incompleto para envio: Nome Meta inválido. Use apenas letras minúsculas, números e underscore.'
            );
        }

        $language = trim((string) $template->language);
        if ($language === '') {
            throw new DomainException('Template incompleto para envio: idioma obrigatorio.');
        }

        $category = strtoupper(trim((string) $template->category));
        if (!in_array($category, ['UTILITY', 'SECURITY', 'AUTHENTICATION'], true)) {
            throw new DomainException('Template incompleto para envio: categoria invalida.');
        }

        $bodyText = trim((string) $template->body_text);
        if ($bodyText === '') {
            throw new DomainException('Template incompleto para envio: body_text obrigatorio.');
        }

        $placeholderErrors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
            (string) $template->body_text,
            $template->variables
        );
        foreach (['body_text', 'variables'] as $field) {
            if (isset($placeholderErrors[$field])) {
                throw new DomainException((string) $placeholderErrors[$field]);
            }
        }

        $sampleErrors = WhatsAppOfficialTemplateValidator::validateSampleVariablesConsistency(
            (string) $template->body_text,
            $template->sample_variables,
            true
        );
        if (isset($sampleErrors['sample_variables'])) {
            throw new DomainException((string) $sampleErrors['sample_variables']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMetaPayload(WhatsAppOfficialTemplate $template): array
    {
        $metaCategory = $this->mapCategoryForMeta((string) $template->category);

        return [
            'name' => $template->meta_template_name,
            'category' => $metaCategory,
            'language' => $template->language,
            'components' => $metaCategory === 'AUTHENTICATION'
                ? $this->buildAuthenticationComponents($template)
                : $this->buildUtilityComponents($template),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildUtilityComponents(WhatsAppOfficialTemplate $template): array
    {
        $components = [];

        if (trim((string) $template->header_text) !== '') {
            $components[] = [
                'type' => 'HEADER',
                'format' => 'TEXT',
                'text' => (string) $template->header_text,
            ];
        }

        $components[] = [
            'type' => 'BODY',
            'text' => (string) $template->body_text,
        ];
        $bodyPlaceholders = array_map(
            'strval',
            WhatsAppOfficialTemplateValidator::extractNumericPlaceholders((string) $template->body_text)
        );
        $normalizedSamples = WhatsAppOfficialTemplateValidator::normalizeSampleVariables($template->sample_variables);
        if ($bodyPlaceholders !== []) {
            $bodyExample = [];
            foreach ($bodyPlaceholders as $placeholderKey) {
                $bodyExample[] = (string) ($normalizedSamples[$placeholderKey] ?? '');
            }

            $components[count($components) - 1]['example'] = [
                'body_text' => [$bodyExample],
            ];
        }

        if (trim((string) $template->footer_text) !== '') {
            $components[] = [
                'type' => 'FOOTER',
                'text' => (string) $template->footer_text,
            ];
        }

        if (is_array($template->buttons) && $template->buttons !== []) {
            $components[] = [
                'type' => 'BUTTONS',
                'buttons' => $template->buttons,
            ];
        }

        return $components;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildAuthenticationComponents(WhatsAppOfficialTemplate $template): array
    {
        $expirationMinutes = $this->resolveAuthExpirationMinutes($template->sample_variables);

        return [
            [
                'type' => 'BODY',
                'add_security_recommendation' => true,
            ],
            [
                'type' => 'FOOTER',
                'code_expiration_minutes' => $expirationMinutes,
            ],
            [
                'type' => 'BUTTONS',
                'buttons' => [
                    [
                        'type' => 'OTP',
                        'otp_type' => 'COPY_CODE',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<int|string, mixed>|null $sampleVariables
     */
    private function resolveAuthExpirationMinutes(?array $sampleVariables): int
    {
        if (!is_array($sampleVariables)) {
            return 10;
        }

        foreach (['3', 'expires_in_minutes'] as $key) {
            if (!array_key_exists($key, $sampleVariables)) {
                continue;
            }

            $minutes = (int) trim((string) $sampleVariables[$key]);
            if ($minutes > 0) {
                return min(90, $minutes);
            }
        }

        return 10;
    }

    /**
     * @return array<string, mixed>
     */
    private function context(WhatsAppOfficialTemplate $template): array
    {
        return [
            'tenant_id' => $template->tenant_id ? (string) $template->tenant_id : null,
            'provider' => $template->provider,
            'key' => $template->key,
            'meta_template_name' => $template->meta_template_name,
            'category' => $template->category,
            'meta_category' => $this->mapCategoryForMeta((string) $template->category),
            'language' => $template->language,
            'version' => $template->version,
            'status' => $template->status,
            'sample_variables_count' => count((array) ($template->sample_variables ?? [])),
        ];
    }

    private function normalizeActorId(?string $actorId): ?string
    {
        $value = trim((string) $actorId);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            return null;
        }

        return strtolower($value);
    }

    private function mapCategoryForMeta(string $category): string
    {
        $normalized = strtoupper(trim($category));

        return match ($normalized) {
            'SECURITY' => 'AUTHENTICATION',
            'UTILITY' => 'UTILITY',
            default => 'UTILITY',
        };
    }
}
