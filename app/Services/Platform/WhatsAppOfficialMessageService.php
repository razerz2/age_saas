<?php

namespace App\Services\Platform;

use App\Exceptions\WhatsAppMetaApiException;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Services\WhatsApp\PhoneNormalizer;
use App\Services\WhatsApp\WhatsAppBusinessProvider;
use DomainException;
use Illuminate\Support\Facades\Log;

class WhatsAppOfficialMessageService
{
    public function __construct(
        private readonly WhatsAppOfficialTemplateResolver $resolver,
        private readonly WhatsAppBusinessProvider $provider
    ) {
    }

    public function sendByKey(
        string $key,
        ?string $phone,
        array $variables,
        array $context = []
    ): bool {
        $normalizedKey = trim($key);
        $activeProvider = $this->activeProvider();
        if ($normalizedKey === '') {
            Log::warning('platform_whatsapp_official_send_skipped', array_merge($context, [
                'reason' => 'empty_key',
                'provider' => $activeProvider,
            ]));
            return false;
        }

        $normalizedPhone = $this->normalizePhone($phone);
        if ($normalizedPhone === null) {
            Log::warning('platform_whatsapp_official_send_skipped', array_merge($context, [
                'key' => $normalizedKey,
                'reason' => 'missing_recipient_phone',
                'provider' => $activeProvider,
            ]));
            return false;
        }

        if (!$this->isOfficialProviderEnabled($activeProvider)) {
            Log::warning('platform_whatsapp_official_send_skipped', array_merge($context, [
                'key' => $normalizedKey,
                'reason' => 'provider_incompatible',
                'provider' => $activeProvider,
            ]));
            return false;
        }

        $template = $this->resolver->resolveApprovedByKey($normalizedKey);
        if (!$template) {
            $latest = WhatsAppOfficialTemplate::query()
                ->officialProvider()
                ->byKey($normalizedKey)
                ->orderByDesc('version')
                ->first();

            Log::warning('platform_whatsapp_official_send_skipped', array_merge($context, [
                'key' => $normalizedKey,
                'reason' => $latest ? 'template_not_approved' : 'template_not_found',
                'provider' => $activeProvider,
                'latest_status' => $latest?->status,
                'latest_version' => $latest?->version,
            ]));
            return false;
        }

        try {
            $metaCategory = $this->templateMetaCategory($template);
            $orderedParameters = [];
            $buttonComponents = [];
            $remoteBodyParametersExpected = null;
            $remoteButtonParametersExpected = null;
            if ($metaCategory === 'AUTHENTICATION') {
                $authenticationPayload = $this->resolveAuthenticationPayload($template, $variables);
                $orderedParameters = $authenticationPayload['body_parameters'];
                $buttonComponents = $authenticationPayload['button_components'];
                $remoteBodyParametersExpected = $authenticationPayload['remote_body_params_expected'];
                $remoteButtonParametersExpected = $authenticationPayload['remote_button_params_expected'];
                $result = $this->provider->sendAuthenticationTemplateMessageDetailed(
                    $normalizedPhone,
                    (string) $template->meta_template_name,
                    (string) ($template->language ?: 'pt_BR'),
                    $orderedParameters,
                    $buttonComponents
                );
                $sent = (bool) ($result['success'] ?? false);
            } else {
                $orderedParameters = $this->orderedTemplateParameters($template, $variables);
                $sent = $this->provider->sendTemplateMessage(
                    $normalizedPhone,
                    (string) $template->meta_template_name,
                    (string) ($template->language ?: 'pt_BR'),
                    $orderedParameters
                );
            }

            if ($sent) {
                Log::info('platform_whatsapp_official_sent', array_merge($context, [
                    'key' => $normalizedKey,
                    'template_id' => (string) $template->id,
                    'template_name' => (string) $template->meta_template_name,
                    'template_version' => (int) $template->version,
                    'status' => (string) $template->status,
                    'provider' => $activeProvider,
                    'variables_count' => count($orderedParameters),
                    'button_components_count' => count($buttonComponents),
                    'meta_category' => $metaCategory,
                    'remote_body_params_expected' => $remoteBodyParametersExpected,
                    'remote_button_params_expected' => $remoteButtonParametersExpected,
                ]));
            } else {
                Log::warning('platform_whatsapp_official_send_failed', array_merge($context, [
                    'key' => $normalizedKey,
                    'template_id' => (string) $template->id,
                    'template_name' => (string) $template->meta_template_name,
                    'template_version' => (int) $template->version,
                    'status' => (string) $template->status,
                    'provider' => $activeProvider,
                    'reason' => 'provider_send_failed',
                    'meta_category' => $metaCategory,
                    'remote_body_params_expected' => $remoteBodyParametersExpected,
                    'remote_button_params_expected' => $remoteButtonParametersExpected,
                ]));
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::warning('platform_whatsapp_official_send_failed', array_merge($context, [
                'key' => $normalizedKey,
                'reason' => 'build_or_send_exception',
                'template_id' => (string) $template->id,
                'template_name' => (string) $template->meta_template_name,
                'template_version' => (int) $template->version,
                'status' => (string) $template->status,
                'provider' => $activeProvider,
                'error' => $e->getMessage(),
            ]));

            return false;
        }
    }

    /**
     * @param array<string, mixed> $variables
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function sendManualTest(
        WhatsAppOfficialTemplate $template,
        ?string $phone,
        array $variables,
        array $context = []
    ): array {
        $activeProvider = $this->activeProvider();
        $normalizedPhone = $this->normalizePhone($phone);

        if ($template->provider !== WhatsAppOfficialTemplate::PROVIDER) {
            $this->logManualTest('blocked', $template, $phone, array_merge($context, [
                'reason' => 'template_provider_incompatible',
                'provider' => $activeProvider,
            ]));
            throw new DomainException('Template invalido para teste oficial: provider incompatível.');
        }

        if ($template->status !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            $this->logManualTest('blocked', $template, $phone, array_merge($context, [
                'reason' => 'template_not_approved',
                'provider' => $activeProvider,
            ]));
            throw new DomainException('Teste manual bloqueado: apenas templates com status APPROVED podem ser enviados.');
        }

        if ($normalizedPhone === null) {
            $this->logManualTest('blocked', $template, $phone, array_merge($context, [
                'reason' => 'missing_recipient_phone',
                'provider' => $activeProvider,
            ]));
            throw new DomainException('Informe um numero de destino valido para o teste.');
        }

        if (!$this->isOfficialProviderEnabled($activeProvider)) {
            $this->logManualTest('blocked', $template, $phone, array_merge($context, [
                'reason' => 'provider_incompatible',
                'provider' => $activeProvider,
            ]));
            throw new DomainException('Provider ativo incompatível. Configure WHATSAPP_PROVIDER=whatsapp_business.');
        }

        $metaCategory = $this->templateMetaCategory($template);
        $orderedParameters = [];
        $buttonComponents = [];
        $remoteBodyParametersExpected = null;
        $remoteButtonParametersExpected = null;

        if ($metaCategory === 'AUTHENTICATION') {
            $authenticationPayload = $this->resolveAuthenticationPayload($template, $variables);
            $orderedParameters = $authenticationPayload['body_parameters'];
            $buttonComponents = $authenticationPayload['button_components'];
            $remoteBodyParametersExpected = $authenticationPayload['remote_body_params_expected'];
            $remoteButtonParametersExpected = $authenticationPayload['remote_button_params_expected'];
            $result = $this->provider->sendAuthenticationTemplateMessageDetailed(
                $normalizedPhone,
                (string) $template->meta_template_name,
                (string) ($template->language ?: 'pt_BR'),
                $orderedParameters,
                $buttonComponents
            );
        } else {
            $orderedParameters = $this->orderedTemplateParameters($template, $variables);
            $result = $this->provider->sendTemplateMessageDetailed(
                $normalizedPhone,
                (string) $template->meta_template_name,
                (string) ($template->language ?: 'pt_BR'),
                $orderedParameters
            );
        }

        if ((bool) ($result['success'] ?? false) !== true) {
            $metaError = is_array($result['meta_error'] ?? null) ? $result['meta_error'] : [];
            $httpStatus = is_numeric($result['http_status'] ?? null) ? (int) $result['http_status'] : 0;

            $exception = new WhatsAppMetaApiException(
                'Falha ao enviar teste de template oficial.',
                $httpStatus,
                $metaError,
                (string) ($result['response_summary'] ?? '')
            );

            $this->logManualTest('failed', $template, $phone, array_merge($context, [
                'provider' => $activeProvider,
                'http_status' => $httpStatus,
                'meta_error' => $metaError,
                'response_summary' => $result['response_summary'] ?? null,
            ]));

            throw $exception;
        }

        $this->logManualTest('sent', $template, $phone, array_merge($context, [
            'provider' => $activeProvider,
            'http_status' => $result['http_status'] ?? null,
            'response_summary' => $result['response_summary'] ?? null,
            'variables_count' => count($orderedParameters),
            'button_components_count' => count($buttonComponents),
            'meta_category' => $metaCategory,
            'remote_body_params_expected' => $remoteBodyParametersExpected,
            'remote_button_params_expected' => $remoteButtonParametersExpected,
        ]));

        return $result;
    }

    /**
     * @param array<string, mixed> $variables
     * @return array{
     *     body_parameters: array<int, string>,
     *     button_components: array<int, array<string, mixed>>,
     *     remote_body_params_expected: ?int,
     *     remote_button_params_expected: ?int
     * }
     */
    private function resolveAuthenticationPayload(WhatsAppOfficialTemplate $template, array $variables): array
    {
        $remoteBodyParametersExpected = $this->resolveRemoteBodyParametersExpected($template);
        $bodyParameters = $this->resolveAuthenticationParameters(
            $template,
            $variables,
            $remoteBodyParametersExpected
        );

        $buttonRequirements = $this->resolveRemoteButtonRequirements($template);
        $buttonComponents = $this->resolveAuthenticationButtonComponents(
            $template,
            $variables,
            $bodyParameters,
            $buttonRequirements
        );

        $remoteButtonParametersExpected = 0;
        foreach ($buttonRequirements as $requirement) {
            $remoteButtonParametersExpected += (int) ($requirement['parameters_expected'] ?? 0);
        }

        return [
            'body_parameters' => $bodyParameters,
            'button_components' => $buttonComponents,
            'remote_body_params_expected' => $remoteBodyParametersExpected,
            'remote_button_params_expected' => $remoteButtonParametersExpected,
        ];
    }

    private function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $normalized = trim($phone);
        return $normalized === '' ? null : $normalized;
    }

    private function isOfficialProviderEnabled(?string $provider = null): bool
    {
        $provider = $provider ?? $this->activeProvider();
        return in_array($provider, ['whatsapp_business', 'business'], true);
    }

    private function activeProvider(): string
    {
        $provider = function_exists('sysconfig')
            ? (string) sysconfig('WHATSAPP_PROVIDER', config('services.whatsapp.provider', 'whatsapp_business'))
            : (string) config('services.whatsapp.provider', 'whatsapp_business');

        $provider = strtolower(trim($provider));
        return $provider !== '' ? $provider : 'whatsapp_business';
    }

    /**
     * @return array<int, string>
     */
    private function orderedTemplateParameters(WhatsAppOfficialTemplate $template, array $variables): array
    {
        $variableMap = $this->normalizeTemplateVariableMap((array) $template->variables);
        if ($variableMap === []) {
            return [];
        }

        $input = [];
        foreach ($variables as $name => $value) {
            if (!is_string($name) && !is_int($name)) {
                continue;
            }

            $input[(string) $name] = $this->stringifyVariableValue($value);
        }

        $ordered = [];
        $missing = [];

        foreach ($variableMap as $placeholder => $variableName) {
            if (!array_key_exists($variableName, $input)) {
                $missing[] = $variableName;
                continue;
            }

            $ordered[] = $input[$variableName];
        }

        if ($missing !== []) {
            throw new DomainException('Variaveis obrigatorias ausentes: ' . implode(', ', $missing));
        }

        return $ordered;
    }

    /**
     * @param  array<string, mixed>  $map
     * @return array<string, string>
     */
    private function normalizeTemplateVariableMap(array $map): array
    {
        $normalized = [];
        foreach ($map as $placeholder => $variableName) {
            if (!is_string($placeholder) && !is_int($placeholder)) {
                continue;
            }

            if (!is_string($variableName) && !is_int($variableName)) {
                continue;
            }

            $placeholderKey = (string) $placeholder;
            $name = trim((string) $variableName);
            if ($placeholderKey === '' || $name === '') {
                continue;
            }

            $normalized[$placeholderKey] = $name;
        }

        uksort($normalized, static function (string $a, string $b): int {
            return (int) $a <=> (int) $b;
        });

        return $normalized;
    }

    private function stringifyVariableValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return trim((string) json_encode($value, JSON_UNESCAPED_UNICODE));
    }

    private function templateMetaCategory(WhatsAppOfficialTemplate $template): string
    {
        $category = strtoupper(trim((string) $template->category));
        if (in_array($category, ['SECURITY', 'AUTHENTICATION'], true)) {
            return 'AUTHENTICATION';
        }

        return 'UTILITY';
    }

    private function resolveRemoteBodyParametersExpected(WhatsAppOfficialTemplate $template): ?int
    {
        $remoteTemplate = $this->extractRemoteTemplateSnapshot($template);
        if ($remoteTemplate === null) {
            return null;
        }

        $components = (array) ($remoteTemplate['components'] ?? []);
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper(trim((string) ($component['type'] ?? '')));
            if ($type !== 'BODY') {
                continue;
            }

            $text = (string) ($component['text'] ?? '');
            preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);
            $placeholders = array_values(array_unique(array_map('strval', $matches[1] ?? [])));

            return count($placeholders);
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<int, string>
     */
    private function resolveAuthenticationParameters(
        WhatsAppOfficialTemplate $template,
        array $variables,
        ?int $remoteBodyParametersExpected
    ): array {
        if ($remoteBodyParametersExpected === null || $remoteBodyParametersExpected <= 0) {
            return [];
        }

        $input = $this->normalizeInputVariables($variables);

        $localVariableMap = $this->normalizeTemplateVariableMap((array) $template->variables);
        $localSampleMap = $this->normalizeTemplateVariableMap((array) $template->sample_variables);
        $remoteExampleValues = $this->resolveRemoteBodyExampleValues($template);

        $resolved = [];
        if ($remoteBodyParametersExpected === 1) {
            $single = $this->resolveSingleAuthenticationParameter(
                $input,
                $localVariableMap,
                $localSampleMap,
                $remoteExampleValues
            );

            if ($single === '') {
                throw new DomainException(
                    'Template AUTHENTICATION aprovado exige 1 parametro de BODY. Informe a variavel "code" no teste manual.'
                );
            }

            return [$single];
        }

        $fallbackOrdered = $this->orderedTemplateParameters($template, $variables);
        $resolved = array_slice($fallbackOrdered, 0, $remoteBodyParametersExpected);
        if (count($resolved) < $remoteBodyParametersExpected) {
            throw new DomainException(
                'Template AUTHENTICATION aprovado exige ' . $remoteBodyParametersExpected . ' parametros de BODY.'
            );
        }

        return $resolved;
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $localVariableMap
     * @param array<string, string> $localSampleMap
     * @param array<int, string> $remoteExampleValues
     */
    private function resolveSingleAuthenticationParameter(
        array $input,
        array $localVariableMap,
        array $localSampleMap,
        array $remoteExampleValues
    ): string {
        foreach (['code', 'otp_code', 'verification_code'] as $candidate) {
            if (isset($input[$candidate]) && trim((string) $input[$candidate]) !== '') {
                return trim((string) $input[$candidate]);
            }
        }

        $remoteExample = trim((string) ($remoteExampleValues[0] ?? ''));
        $resolvedFromExample = $this->resolveValueByRemoteExample(
            $remoteExample,
            $input,
            $localVariableMap,
            $localSampleMap
        );
        if ($resolvedFromExample !== '') {
            return $resolvedFromExample;
        }

        foreach ($input as $value) {
            $normalized = trim((string) $value);
            if ($normalized !== '') {
                return $normalized;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, string>
     */
    private function normalizeInputVariables(array $variables): array
    {
        $input = [];
        foreach ($variables as $name => $value) {
            if (!is_string($name) && !is_int($name)) {
                continue;
            }

            $key = trim((string) $name);
            if ($key === '') {
                continue;
            }

            $input[$key] = $this->stringifyVariableValue($value);
        }

        return $input;
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $localVariableMap
     * @param array<string, string> $localSampleMap
     */
    private function resolveValueByRemoteExample(
        string $remoteExample,
        array $input,
        array $localVariableMap,
        array $localSampleMap
    ): string {
        $remoteExample = trim($remoteExample);
        if ($remoteExample !== '') {
            foreach ($localSampleMap as $placeholder => $sampleValue) {
                if (trim((string) $sampleValue) !== $remoteExample) {
                    continue;
                }

                $variableName = $localVariableMap[$placeholder] ?? null;
                if ($variableName !== null && isset($input[$variableName]) && trim((string) $input[$variableName]) !== '') {
                    return trim((string) $input[$variableName]);
                }
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $variables
     * @param array<int, string> $bodyParameters
     * @param array<int, array<string, mixed>> $buttonRequirements
     * @return array<int, array<string, mixed>>
     */
    private function resolveAuthenticationButtonComponents(
        WhatsAppOfficialTemplate $template,
        array $variables,
        array $bodyParameters,
        array $buttonRequirements
    ): array {
        if ($buttonRequirements === []) {
            return [];
        }

        $input = $this->normalizeInputVariables($variables);
        $localVariableMap = $this->normalizeTemplateVariableMap((array) $template->variables);
        $localSampleMap = $this->normalizeTemplateVariableMap((array) $template->sample_variables);
        $remoteBodyExampleValues = $this->resolveRemoteBodyExampleValues($template);
        $orderedLocalValues = $this->orderedAvailableInputValuesByTemplateMap($localVariableMap, $input);

        $components = [];
        foreach ($buttonRequirements as $requirement) {
            $expected = max(0, (int) ($requirement['parameters_expected'] ?? 0));
            if ($expected === 0) {
                continue;
            }

            $resolved = $this->resolveAuthenticationButtonParameterValues(
                $expected,
                $input,
                $localVariableMap,
                $localSampleMap,
                $remoteBodyExampleValues,
                (array) ($requirement['example_values'] ?? []),
                $bodyParameters,
                $orderedLocalValues
            );

            if (count($resolved) < $expected) {
                throw new DomainException(
                    'Template AUTHENTICATION aprovado exige '
                    . $expected
                    . ' parametro(s) no BUTTON index '
                    . (string) ($requirement['index'] ?? '0')
                    . '.'
                );
            }

            $components[] = [
                'sub_type' => (string) ($requirement['sub_type'] ?? 'url'),
                'index' => (string) ($requirement['index'] ?? '0'),
                'parameters' => $resolved,
            ];
        }

        return $components;
    }

    /**
     * @param array<string, string> $input
     * @param array<string, string> $localVariableMap
     * @param array<string, string> $localSampleMap
     * @param array<int, string> $remoteBodyExampleValues
     * @param array<int, mixed> $remoteButtonExampleValues
     * @param array<int, string> $bodyParameters
     * @param array<int, string> $orderedLocalValues
     * @return array<int, string>
     */
    private function resolveAuthenticationButtonParameterValues(
        int $expected,
        array $input,
        array $localVariableMap,
        array $localSampleMap,
        array $remoteBodyExampleValues,
        array $remoteButtonExampleValues,
        array $bodyParameters,
        array $orderedLocalValues
    ): array {
        if ($expected <= 0) {
            return [];
        }

        $firstBodyValue = trim((string) ($bodyParameters[0] ?? ''));
        if ($expected === 1) {
            $single = $this->resolveSingleAuthenticationParameter(
                $input,
                $localVariableMap,
                $localSampleMap,
                $remoteBodyExampleValues
            );

            if ($single === '' && $firstBodyValue !== '') {
                $single = $firstBodyValue;
            }

            if ($single === '') {
                foreach ($remoteButtonExampleValues as $exampleValue) {
                    $resolvedFromExample = $this->resolveValueByRemoteExample(
                        (string) $exampleValue,
                        $input,
                        $localVariableMap,
                        $localSampleMap
                    );
                    if ($resolvedFromExample !== '') {
                        $single = $resolvedFromExample;
                        break;
                    }
                }
            }

            return $single !== '' ? [$single] : [];
        }

        $pool = [];
        if ($firstBodyValue !== '') {
            $pool[] = $firstBodyValue;
        }

        foreach ($orderedLocalValues as $value) {
            $normalized = trim((string) $value);
            if ($normalized !== '') {
                $pool[] = $normalized;
            }
        }

        foreach ($remoteButtonExampleValues as $exampleValue) {
            $resolvedFromExample = $this->resolveValueByRemoteExample(
                (string) $exampleValue,
                $input,
                $localVariableMap,
                $localSampleMap
            );
            if ($resolvedFromExample !== '') {
                $pool[] = $resolvedFromExample;
            }
        }

        foreach ($input as $value) {
            $normalized = trim((string) $value);
            if ($normalized !== '') {
                $pool[] = $normalized;
            }
        }

        $uniqueValues = [];
        foreach ($pool as $value) {
            if (!in_array($value, $uniqueValues, true)) {
                $uniqueValues[] = $value;
            }
        }

        return array_slice($uniqueValues, 0, $expected);
    }

    /**
     * @param array<string, string> $templateVariableMap
     * @param array<string, string> $input
     * @return array<int, string>
     */
    private function orderedAvailableInputValuesByTemplateMap(array $templateVariableMap, array $input): array
    {
        $ordered = [];
        foreach ($templateVariableMap as $variableName) {
            if (!array_key_exists($variableName, $input)) {
                continue;
            }

            $value = trim((string) $input[$variableName]);
            if ($value !== '') {
                $ordered[] = $value;
            }
        }

        return $ordered;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveRemoteButtonRequirements(WhatsAppOfficialTemplate $template): array
    {
        $remoteTemplate = $this->extractRemoteTemplateSnapshot($template);
        if ($remoteTemplate === null) {
            return [];
        }

        $requirements = [];
        $components = (array) ($remoteTemplate['components'] ?? []);
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper(trim((string) ($component['type'] ?? '')));
            if ($type !== 'BUTTONS') {
                continue;
            }

            $buttons = (array) ($component['buttons'] ?? []);
            foreach ($buttons as $buttonIndex => $button) {
                if (!is_array($button)) {
                    continue;
                }

                $parametersExpected = $this->resolveRemoteButtonParametersExpected($button);
                if ($parametersExpected <= 0) {
                    continue;
                }

                $buttonType = strtoupper(trim((string) ($button['type'] ?? '')));
                $subType = strtolower(trim((string) ($button['sub_type'] ?? $buttonType)));
                if ($subType === '') {
                    continue;
                }

                $requirements[] = [
                    'index' => (string) ($button['index'] ?? $buttonIndex),
                    'sub_type' => $subType,
                    'parameters_expected' => $parametersExpected,
                    'example_values' => $this->resolveRemoteButtonExampleValues($button),
                ];
            }
        }

        return $requirements;
    }

    /**
     * @param array<string, mixed> $button
     */
    private function resolveRemoteButtonParametersExpected(array $button): int
    {
        $placeholders = [];
        foreach (['url', 'text', 'payload'] as $field) {
            $content = trim((string) ($button[$field] ?? ''));
            if ($content === '') {
                continue;
            }

            preg_match_all('/\{\{(\d+)\}\}/', $content, $matches);
            foreach ((array) ($matches[1] ?? []) as $placeholder) {
                $placeholders[] = (string) $placeholder;
            }
        }

        $uniquePlaceholders = array_values(array_unique($placeholders));
        if ($uniquePlaceholders !== []) {
            return count($uniquePlaceholders);
        }

        $exampleValues = $this->resolveRemoteButtonExampleValues($button);
        return count($exampleValues);
    }

    /**
     * @param array<string, mixed> $button
     * @return array<int, string>
     */
    private function resolveRemoteButtonExampleValues(array $button): array
    {
        $example = $button['example'] ?? null;
        if ($example === null) {
            return [];
        }

        $values = [];
        if (is_array($example)) {
            if (array_key_exists('url', $example)) {
                $values = array_merge($values, $this->flattenStringValues((array) $example['url']));
            }
            if (array_key_exists('text', $example)) {
                $values = array_merge($values, $this->flattenStringValues((array) $example['text']));
            }
            if (array_key_exists('payload', $example)) {
                $values = array_merge($values, $this->flattenStringValues((array) $example['payload']));
            }

            if ($values === []) {
                $values = $this->flattenStringValues($example);
            }
        } elseif (is_scalar($example)) {
            $values[] = trim((string) $example);
        }

        return array_values(array_filter(array_map('trim', $values), static fn (string $value): bool => $value !== ''));
    }

    /**
     * @param array<int|string, mixed> $payload
     * @return array<int, string>
     */
    private function flattenStringValues(array $payload): array
    {
        $values = [];
        foreach ($payload as $item) {
            if (is_array($item)) {
                $values = array_merge($values, $this->flattenStringValues($item));
                continue;
            }

            if (is_scalar($item)) {
                $values[] = trim((string) $item);
            }
        }

        return $values;
    }

    /**
     * @return array<int, string>
     */
    private function resolveRemoteBodyExampleValues(WhatsAppOfficialTemplate $template): array
    {
        $remoteTemplate = $this->extractRemoteTemplateSnapshot($template);
        if ($remoteTemplate === null) {
            return [];
        }

        $components = (array) ($remoteTemplate['components'] ?? []);
        foreach ($components as $component) {
            if (!is_array($component)) {
                continue;
            }

            $type = strtoupper(trim((string) ($component['type'] ?? '')));
            if ($type !== 'BODY') {
                continue;
            }

            $examples = (array) ($component['example']['body_text'][0] ?? []);
            return array_values(array_map(static fn ($value): string => trim((string) $value), $examples));
        }

        return [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractRemoteTemplateSnapshot(WhatsAppOfficialTemplate $template): ?array
    {
        $response = $template->meta_response;
        if (!is_array($response)) {
            return null;
        }

        $rows = (array) ($response['data'] ?? []);
        if ($rows === []) {
            return null;
        }

        $name = strtolower(trim((string) $template->meta_template_name));
        $language = strtolower(trim((string) $template->language));

        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $rowName = strtolower(trim((string) ($row['name'] ?? '')));
            $rowLanguage = strtolower(trim((string) ($row['language'] ?? $row['locale'] ?? '')));
            if ($rowName === $name && $rowLanguage === $language) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function logManualTest(
        string $result,
        WhatsAppOfficialTemplate $template,
        ?string $phone,
        array $context = []
    ): void {
        $logPayload = array_merge([
            'template_key' => (string) $template->key,
            'template_id' => (string) $template->id,
            'template_status' => (string) $template->status,
            'destination' => $phone ? PhoneNormalizer::maskPhone($phone) : null,
            'result' => $result,
        ], $context);

        if ($result === 'sent') {
            Log::info('platform_whatsapp_official_manual_test', $logPayload);
            return;
        }

        Log::warning('platform_whatsapp_official_manual_test', $logPayload);
    }
}
