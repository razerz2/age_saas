<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Http\Requests\Tenant\StoreCampaignRequest;
use App\Http\Requests\Tenant\UpdateCampaignRequest;
use App\Models\Platform\WhatsAppOfficialTemplate;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignTemplate;
use App\Models\Tenant\CampaignRun;
use App\Models\Tenant\Gender;
use App\Models\Tenant\User;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignTemplateProviderResolver;
use App\Support\Tenant\CampaignPatientRules;
use App\Support\Tenant\CampaignTemplateVariableCatalog;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CampaignController extends Controller
{
    use HandlesGridRequests;

    private const MODULE_DISABLED_MESSAGE = 'Nenhum canal de campanha está configurado. Configure os canais na aba Campanhas ou reutilize os canais de notificações.';

    public function index(Request $request, CampaignChannelGate $gate)
    {
        $availableChannels = $gate->availableChannels();
        $moduleEnabled = count($availableChannels) > 0;

        return view('tenant.campaigns.index', [
            'availableChannels' => $availableChannels,
            'moduleEnabled' => $moduleEnabled,
            'moduleWarning' => $moduleEnabled ? null : self::MODULE_DISABLED_MESSAGE,
        ]);
    }

    public function gridData(Request $request)
    {
        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);
        $moduleEnabled = app(CampaignChannelGate::class)->availableChannels() !== [];

        $query = Campaign::query();

        $search = $this->gridSearch($request);
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $this->applySort($request, $query);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = [];
        foreach ($paginator->items() as $campaign) {
            $statusHtml = $this->renderStatusCell($campaign);
            $actionsHtml = $this->renderActionsCell($campaign, $moduleEnabled);

            $channels = $this->formatChannels($campaign->channels_json);
            $scheduledAt = $campaign->scheduled_at ? $campaign->scheduled_at->format('d/m/Y H:i') : '-';
            $createdAt = $campaign->created_at ? $campaign->created_at->format('d/m/Y H:i') : '-';

            $data[] = [
                'name' => e((string) $campaign->name),
                'type' => e($this->formatType((string) $campaign->type)),
                'status_badge' => $statusHtml,
                'channels' => e($channels),
                'scheduled_at' => e($scheduledAt),
                'created_at' => e($createdAt),
                'actions' => $actionsHtml,
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => $this->gridMeta($paginator),
        ]);
    }

    public function create(CampaignChannelGate $gate, CampaignTemplateProviderResolver $providerResolver)
    {
        return view('tenant.campaigns.create', [
            'availableChannels' => $gate->availableChannels(),
            'campaignVariables' => $this->campaignTemplateVariables(),
            'tenantTimezone' => $this->resolveTenantTimezone(),
            'ruleFieldOptions' => CampaignPatientRules::fieldOptions(),
            'ruleOperatorOptions' => CampaignPatientRules::operatorOptions(),
            'ruleFieldOperators' => CampaignPatientRules::fieldOperators(),
            'ruleValueOptions' => $this->campaignRuleValueOptions(),
            ...$this->campaignWhatsAppTemplateFormContext($providerResolver, null),
        ]);
    }

    public function store(StoreCampaignRequest $request)
    {
        $validated = $request->validated();

        $channels = $this->extractChannels($request);
        $type = $this->normalizeCampaignType((string) ($validated['type'] ?? $request->input('type', 'manual')));
        $createdBy = auth('tenant')->id() ?? auth()->id();
        $status = $this->normalizeCampaignStatusForType($type);

        $campaign = Campaign::create($this->onlyExistingCampaignColumns([
            'name' => $validated['name'],
            'type' => $type,
            'status' => $status,
            'channels_json' => $channels,
            'content_json' => $this->extractJsonPayload($request, $validated, 'content_json'),
            'audience_json' => $this->extractJsonPayload($request, $validated, 'audience_json'),
            'automation_json' => $this->extractAutomationPayload($request, $validated, $type),
            'rules_json' => $this->extractRulesPayload($request),
            'schedule_mode' => $this->extractScheduleMode($request, $type),
            'starts_at' => $this->extractScheduleDate($request, 'starts_at', $type),
            'ends_at' => $this->extractScheduleDate($request, 'ends_at', $type),
            'schedule_weekdays' => $this->extractScheduleWeekdays($request, $type),
            'schedule_times' => $this->extractScheduleTimes($request, $type),
            'timezone' => $this->extractScheduleTimezone($request, $type),
            'scheduled_at' => $this->resolveScheduledAtForPersistence($type),
            'created_by' => $createdBy,
        ]));

        return redirect()
            ->route('tenant.campaigns.show', [
                'slug' => $this->resolveSlug($request),
                'campaign' => $campaign->id,
            ])
            ->with('success', 'Campanha criada com sucesso.');
    }

    public function show(string $slug, Campaign $campaign, CampaignChannelGate $gate)
    {
        $availableChannels = $gate->availableChannels();
        $moduleEnabled = count($availableChannels) > 0;
        $campaignChannels = $this->normalizeChannelsArray($campaign->channels_json);
        $unavailableChannels = array_values(array_diff($campaignChannels, $availableChannels));
        $createdByDisplay = null;
        $lastAutomationRun = null;
        $nextAutomationRun = null;
        $automationTimezone = null;

        if ($campaign->created_by) {
            $creator = User::query()
                ->select(['id', 'name', 'name_full'])
                ->find($campaign->created_by);

            $createdByDisplay = $creator?->display_name
                ?? $creator?->name_full
                ?? $creator?->name
                ?? ('ID ' . $campaign->created_by);
        }

        if (strtolower((string) $campaign->type) === 'automated') {
            $lastAutomationRun = CampaignRun::query()
                ->where('campaign_id', $campaign->id)
                ->orderByDesc('started_at')
                ->orderByDesc('id')
                ->first();

            [$nextAutomationRun, $automationTimezone] = $this->resolveNextAutomationRun($campaign);
        }

        return view('tenant.campaigns.show', [
            'campaign' => $campaign,
            'availableChannels' => $availableChannels,
            'moduleEnabled' => $moduleEnabled,
            'hasUnavailableChannels' => $unavailableChannels !== [],
            'unavailableChannels' => $unavailableChannels,
            'createdByDisplay' => $createdByDisplay,
            'lastAutomationRun' => $lastAutomationRun,
            'nextAutomationRun' => $nextAutomationRun,
            'automationTimezone' => $automationTimezone,
            'rulesSummary' => CampaignPatientRules::describeRules($campaign->rules_json),
        ]);
    }

    public function edit(
        string $slug,
        Campaign $campaign,
        CampaignChannelGate $gate,
        CampaignTemplateProviderResolver $providerResolver
    )
    {
        return view('tenant.campaigns.edit', [
            'campaign' => $campaign,
            'availableChannels' => $gate->availableChannels(),
            'campaignVariables' => $this->campaignTemplateVariables(),
            'tenantTimezone' => $this->resolveTenantTimezone(),
            'ruleFieldOptions' => CampaignPatientRules::fieldOptions(),
            'ruleOperatorOptions' => CampaignPatientRules::operatorOptions(),
            'ruleFieldOperators' => CampaignPatientRules::fieldOperators(),
            'ruleValueOptions' => $this->campaignRuleValueOptions(),
            ...$this->campaignWhatsAppTemplateFormContext($providerResolver, $campaign),
        ]);
    }

    public function update(UpdateCampaignRequest $request, string $slug, Campaign $campaign)
    {
        $validated = $request->validated();
        $type = $this->normalizeCampaignType((string) ($validated['type'] ?? $request->input('type', 'manual')));
        $status = $this->normalizeCampaignStatusForType($type, (string) $campaign->status);

        $campaign->update($this->onlyExistingCampaignColumns([
            'name' => $validated['name'],
            'type' => $type,
            'status' => $status,
            'channels_json' => $this->extractChannels($request),
            'content_json' => $this->extractJsonPayload($request, $validated, 'content_json'),
            'audience_json' => $this->extractJsonPayload($request, $validated, 'audience_json'),
            'automation_json' => $this->extractAutomationPayload($request, $validated, $type)
                ?? ($type === 'automated' ? $campaign->automation_json : null),
            'rules_json' => $this->extractRulesPayload($request),
            'schedule_mode' => $this->extractScheduleMode($request, $type),
            'starts_at' => $this->extractScheduleDate($request, 'starts_at', $type),
            'ends_at' => $this->extractScheduleDate($request, 'ends_at', $type),
            'schedule_weekdays' => $this->extractScheduleWeekdays($request, $type),
            'schedule_times' => $this->extractScheduleTimes($request, $type),
            'timezone' => $this->extractScheduleTimezone($request, $type),
            'scheduled_at' => $this->resolveScheduledAtForPersistence($type, $campaign),
        ]));

        return redirect()
            ->route('tenant.campaigns.show', [
                'slug' => $this->resolveSlug($request),
                'campaign' => $campaign->id,
            ])
            ->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(string $slug, Campaign $campaign)
    {
        $slug = $slug !== '' ? $slug : (tenant()->subdomain ?? '');

        $campaign->delete();

        return redirect()
            ->route('tenant.campaigns.index', ['slug' => $slug])
            ->with('success', 'Campanha removida com sucesso.');
    }

    private function renderStatusCell(Campaign $campaign): string
    {
        if (view()->exists('tenant.campaigns.partials.status_badge')) {
            return view('tenant.campaigns.partials.status_badge', [
                'campaign' => $campaign,
            ])->render();
        }

        return e($this->formatStatus($campaign->status));
    }

    private function renderActionsCell(Campaign $campaign, bool $moduleEnabled): string
    {
        if (view()->exists('tenant.campaigns.partials.actions')) {
            return view('tenant.campaigns.partials.actions', [
                'campaign' => $campaign,
                'moduleEnabled' => $moduleEnabled,
            ])->render();
        }

        return '-';
    }

    private function applySort(Request $request, $query): void
    {
        $sortable = [
            'name' => 'name',
            'type' => 'type',
            'status' => 'status',
            'status_badge' => 'status',
            'scheduled_at' => 'scheduled_at',
            'created_at' => 'created_at',
        ];

        $sort = $this->gridSort($request, $sortable, 'created_at', 'desc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'created_at') {
            $query->orderByDesc('created_at');
        }
    }

    private function normalizeCampaignType(string $type): string
    {
        $normalized = strtolower(trim($type));

        return in_array($normalized, ['manual', 'automated'], true)
            ? $normalized
            : 'manual';
    }

    private function normalizeCampaignStatusForType(string $type, ?string $currentStatus = null): string
    {
        $normalizedType = $this->normalizeCampaignType($type);
        $normalizedStatus = strtolower(trim((string) $currentStatus));
        $allowedStatuses = ['draft', 'active', 'paused', 'archived', 'blocked'];

        if (!in_array($normalizedStatus, $allowedStatuses, true)) {
            return $normalizedType === 'automated' ? 'active' : 'draft';
        }

        if ($normalizedType === 'automated' && $normalizedStatus === 'draft') {
            return 'active';
        }

        return $normalizedStatus;
    }

    private function resolveScheduledAtForPersistence(string $type, ?Campaign $campaign = null): ?Carbon
    {
        $normalizedType = $this->normalizeCampaignType($type);

        if ($normalizedType === 'manual') {
            if ($campaign && $this->normalizeCampaignType((string) $campaign->type) !== 'manual') {
                return null;
            }

            return $campaign?->scheduled_at;
        }

        if ($campaign && $this->normalizeCampaignType((string) $campaign->type) === 'automated') {
            return $campaign->scheduled_at;
        }

        return null;
    }

    private function formatType(string $type): string
    {
        return match (strtolower($type)) {
            'manual' => 'Manual',
            'automated' => 'Agendada',
            default => ucfirst($type),
        };
    }

    private function formatStatus(?string $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'draft' => 'Rascunho',
            'active' => 'Ativa',
            'paused' => 'Pausada',
            'archived' => 'Arquivada',
            'blocked' => 'Bloqueada',
            default => ucfirst($status),
        };
    }

    /**
     * @param mixed $channels
     */
    private function formatChannels($channels): string
    {
        $normalized = $this->normalizeChannelsArray($channels);
        if ($normalized === []) {
            return '-';
        }

        $labels = array_map(function (string $channel) {
            return match ($channel) {
                'email' => 'Email',
                'whatsapp' => 'WhatsApp',
                default => ucfirst($channel),
            };
        }, $normalized);

        return implode(', ', $labels);
    }

    /**
     * @param mixed $channels
     * @return array<int, string>
     */
    private function normalizeChannelsArray($channels): array
    {
        if (!is_array($channels)) {
            return [];
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $value = strtolower(trim((string) $channel));
            if ($value === '') {
                continue;
            }

            if (!in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function extractChannels(Request $request): array
    {
        $channels = $request->input('channels_json', $request->input('channels', []));
        return $this->normalizeChannelsArray($channels);
    }

    private function extractJsonPayload(Request $request, array $validated, string $key): array
    {
        $payload = $request->input($key, $validated[$key] ?? []);

        if (!is_array($payload)) {
            return [];
        }

        return $payload;
    }

    private function extractAutomationPayload(Request $request, array $validated, string $type): ?array
    {
        $payload = $request->input('automation_json', $validated['automation_json'] ?? null);

        if ($type === 'manual') {
            return null;
        }

        if (!is_array($payload) || $payload === []) {
            return null;
        }

        return $payload;
    }

    private function extractRulesPayload(Request $request): ?array
    {
        return CampaignPatientRules::normalizeRules($request->input('rules_json'));
    }

    private function extractScheduleMode(Request $request, string $type): ?string
    {
        if ($type !== 'automated') {
            return 'period';
        }

        $mode = strtolower(trim((string) $request->input('schedule_mode', 'period')));
        if (!in_array($mode, ['period', 'indefinite'], true)) {
            return 'period';
        }

        return $mode;
    }

    private function extractScheduleDate(Request $request, string $key, string $type): ?Carbon
    {
        if ($type !== 'automated') {
            return null;
        }

        $rawValue = trim((string) $request->input($key, ''));
        if ($rawValue === '') {
            return null;
        }

        $timezone = (string) $this->extractScheduleTimezone($request, $type);

        try {
            return Carbon::parse($rawValue, $timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, int>|null
     */
    private function extractScheduleWeekdays(Request $request, string $type): ?array
    {
        if ($type !== 'automated') {
            return null;
        }

        return $this->normalizeWeekdays($request->input('weekdays', []));
    }

    /**
     * @return array<int, string>|null
     */
    private function extractScheduleTimes(Request $request, string $type): ?array
    {
        if ($type !== 'automated') {
            return null;
        }

        return $this->normalizeTimes($request->input('times', []));
    }

    private function extractScheduleTimezone(Request $request, string $type): ?string
    {
        if ($type !== 'automated') {
            return null;
        }

        $timezone = trim((string) $request->input('timezone', ''));
        if ($timezone === '') {
            return $this->resolveTenantTimezone();
        }

        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Throwable) {
            return $this->resolveTenantTimezone();
        }
    }

    private function resolveSlug(Request $request): string
    {
        return (string) ($request->route('slug') ?: tenant()->subdomain);
    }

    /**
     * @param array<string,mixed> $attributes
     * @return array<string,mixed>
     */
    private function onlyExistingCampaignColumns(array $attributes): array
    {
        static $availableColumns = null;

        if (!is_array($availableColumns)) {
            try {
                $availableColumns = array_flip(Schema::connection('tenant')->getColumnListing('campaigns'));
            } catch (\Throwable) {
                return $attributes;
            }
        }

        $filtered = [];
        foreach ($attributes as $key => $value) {
            if (isset($availableColumns[$key])) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * @return array{0:?Carbon,1:?string}
     */
    private function resolveNextAutomationRun(Campaign $campaign): array
    {
        $timezone = $this->resolveCampaignTimezone($campaign);
        $mode = strtolower((string) ($campaign->schedule_mode ?? 'period'));
        if (!in_array($mode, ['period', 'indefinite'], true)) {
            $mode = 'period';
        }

        $weekdays = $this->normalizeWeekdays($campaign->schedule_weekdays);
        if ($weekdays === []) {
            $weekdays = [0, 1, 2, 3, 4, 5, 6];
        }

        $times = $this->normalizeTimes($campaign->schedule_times);
        if ($times === []) {
            $legacyTime = trim((string) data_get($campaign->automation_json, 'schedule.time', ''));
            $times = $legacyTime !== '' ? $this->normalizeTimes([$legacyTime]) : [];
        }

        if ($times === []) {
            return [null, $timezone];
        }

        try {
            $localNow = Carbon::now($timezone);
            $startsAt = $campaign->starts_at ? $campaign->starts_at->copy()->timezone($timezone) : null;
            $endsAt = $campaign->ends_at ? $campaign->ends_at->copy()->timezone($timezone) : null;

            $searchStart = $localNow->copy();
            if ($startsAt && $startsAt->greaterThan($searchStart)) {
                $searchStart = $startsAt->copy();
            }

            for ($offset = 0; $offset <= 370; $offset++) {
                $day = $searchStart->copy()->startOfDay()->addDays($offset);
                if (!in_array((int) $day->dayOfWeek, $weekdays, true)) {
                    continue;
                }

                foreach ($times as $time) {
                    [$hourRaw, $minuteRaw] = explode(':', $time, 2);
                    $candidate = $day->copy()->setTime((int) $hourRaw, (int) $minuteRaw, 0);

                    if ($candidate->lessThan($searchStart)) {
                        continue;
                    }

                    if ($startsAt && $candidate->lessThan($startsAt)) {
                        continue;
                    }

                    if ($mode === 'period' && $endsAt && $candidate->greaterThan($endsAt)) {
                        continue;
                    }

                    return [$candidate, $timezone];
                }

                if ($mode === 'period' && $endsAt && $day->endOfDay()->greaterThan($endsAt)) {
                    break;
                }
            }
        } catch (\Throwable) {
            return [null, $timezone];
        }

        return [null, $timezone];
    }

    private function resolveCampaignTimezone(Campaign $campaign): string
    {
        $timezone = trim((string) ($campaign->timezone ?? ''));
        if ($timezone === '') {
            $timezone = trim((string) data_get($campaign->automation_json, 'timezone', ''));
        }

        if ($timezone === '') {
            $timezone = $this->resolveTenantTimezone();
        }

        try {
            new \DateTimeZone($timezone);
            return $timezone;
        } catch (\Throwable) {
            return $this->resolveTenantTimezone();
        }
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

    /**
     * @return array<string, array<int, array{value:string,label:string}>>
     */
    private function campaignRuleValueOptions(): array
    {
        $genderOptions = Gender::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['abbreviation', 'name'])
            ->map(fn (Gender $gender) => [
                'value' => (string) $gender->abbreviation,
                'label' => (string) $gender->name,
            ])
            ->values()
            ->all();

        return [
            'is_active' => [
                ['value' => '1', 'label' => 'Ativo'],
                ['value' => '0', 'label' => 'Inativo'],
            ],
            'gender' => $genderOptions,
        ];
    }

    /**
     * @param mixed $weekdays
     * @return array<int, int>
     */
    private function normalizeWeekdays(mixed $weekdays): array
    {
        if (!is_array($weekdays)) {
            return [];
        }

        $normalized = [];
        foreach ($weekdays as $weekday) {
            if (!is_numeric($weekday)) {
                continue;
            }

            $day = (int) $weekday;
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
        if (!is_array($times)) {
            return [];
        }

        $normalized = [];
        foreach ($times as $time) {
            $normalizedTime = $this->normalizeTime((string) $time);
            if ($normalizedTime === null) {
                continue;
            }

            if (!in_array($normalizedTime, $normalized, true)) {
                $normalized[] = $normalizedTime;
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

    private function campaignTemplateVariables(): array
    {
        return app(CampaignTemplateVariableCatalog::class)->all();
    }

    /**
     * @return array{
     *     whatsappCampaignProvider:string,
     *     whatsappProviderType:string,
     *     officialCampaignTemplates:array<int,array<string,mixed>>,
     *     unofficialCampaignTemplates:array<int,array<string,mixed>>,
     *     showLegacyOfficialTemplateWarning:bool,
     *     whatsappTemplateWarnings:array<int,string>
     * }
     */
    private function campaignWhatsAppTemplateFormContext(
        CampaignTemplateProviderResolver $providerResolver,
        ?Campaign $campaign
    ): array {
        $provider = $providerResolver->resolveWhatsAppProvider();
        $isOfficialProvider = $providerResolver->isOfficialWhatsApp();

        return [
            'whatsappCampaignProvider' => $provider,
            'whatsappProviderType' => $isOfficialProvider ? 'official' : 'unofficial',
            'officialCampaignTemplates' => $this->resolveOfficialCampaignTemplates(),
            'unofficialCampaignTemplates' => $this->resolveUnofficialCampaignTemplates(),
            'showLegacyOfficialTemplateWarning' => $this->shouldShowLegacyOfficialTemplateWarning($campaign, $isOfficialProvider),
            'whatsappTemplateWarnings' => $this->resolveCampaignTemplateWarnings($campaign, $isOfficialProvider),
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function resolveOfficialCampaignTemplates(): array
    {
        $tenantId = trim((string) (tenant()?->id ?? ''));
        if ($tenantId === '') {
            return [];
        }

        return WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->approved()
            ->orderBy('key')
            ->orderBy('language')
            ->orderByDesc('version')
            ->get([
                'id',
                'key',
                'language',
                'category',
                'version',
                'status',
                'meta_template_name',
            ])
            ->map(static fn (WhatsAppOfficialTemplate $template): array => [
                'id' => (string) $template->id,
                'name' => (string) $template->key,
                'language' => (string) $template->language,
                'category' => (string) $template->category,
                'status' => (string) $template->status,
                'version' => (int) $template->version,
                'meta_template_name' => (string) $template->meta_template_name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function resolveUnofficialCampaignTemplates(): array
    {
        return CampaignTemplate::query()
            ->forWhatsApp()
            ->unofficial()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'content', 'is_active', 'updated_at'])
            ->map(static fn (CampaignTemplate $template): array => [
                'id' => (int) $template->id,
                'name' => (string) $template->name,
                'content' => (string) $template->content,
                'is_active' => (bool) $template->is_active,
                'updated_at' => $template->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    private function shouldShowLegacyOfficialTemplateWarning(?Campaign $campaign, bool $isOfficialProvider): bool
    {
        if (!$campaign || !$isOfficialProvider) {
            return false;
        }

        $channels = $this->normalizeChannelsArray($campaign->channels_json);
        if (!in_array('whatsapp', $channels, true)) {
            return false;
        }

        $whatsapp = data_get($campaign->content_json, 'whatsapp');
        if (!is_array($whatsapp)) {
            return false;
        }

        $compositionMode = strtolower(trim((string) ($whatsapp['composition_mode'] ?? '')));
        if ($compositionMode === 'template') {
            return false;
        }

        $hasLegacyText = trim((string) ($whatsapp['text'] ?? '')) !== '';
        $hasLegacyMessageType = trim((string) ($whatsapp['message_type'] ?? '')) !== '';
        $hasLegacyMedia = is_array($whatsapp['media'] ?? null) && (array) ($whatsapp['media'] ?? []) !== [];

        return $hasLegacyText || $hasLegacyMessageType || $hasLegacyMedia;
    }

    /**
     * @return array<int,string>
     */
    private function resolveCampaignTemplateWarnings(?Campaign $campaign, bool $isOfficialProvider): array
    {
        if (!$campaign) {
            return [];
        }

        $channels = $this->normalizeChannelsArray($campaign->channels_json);
        if (!in_array('whatsapp', $channels, true)) {
            return [];
        }

        $whatsapp = data_get($campaign->content_json, 'whatsapp');
        if (!is_array($whatsapp)) {
            return [];
        }

        $compositionMode = $this->resolveCampaignWhatsappCompositionMode($whatsapp, $isOfficialProvider);
        if ($compositionMode !== 'template') {
            return [];
        }

        if ($isOfficialProvider) {
            return $this->resolveOfficialTemplateWarnings($whatsapp);
        }

        return $this->resolveUnofficialTemplateWarnings($whatsapp);
    }

    /**
     * @param array<string,mixed> $whatsapp
     * @return array<int,string>
     */
    private function resolveOfficialTemplateWarnings(array $whatsapp): array
    {
        $officialTemplateId = trim((string) ($whatsapp['official_template_id'] ?? ''));
        if ($officialTemplateId === '') {
            return ['Selecione um template oficial aprovado para continuar.'];
        }

        $tenantId = trim((string) (tenant()?->id ?? ''));
        if ($tenantId === '') {
            return ['Nao foi possivel validar o template oficial para o tenant atual.'];
        }

        $template = WhatsAppOfficialTemplate::query()
            ->officialProvider()
            ->forTenant($tenantId)
            ->find($officialTemplateId);

        if (!$template) {
            return ['O template oficial salvo nesta campanha nao esta mais disponivel para este tenant. Selecione outro template aprovado.'];
        }

        if (strtolower((string) $template->status) !== WhatsAppOfficialTemplate::STATUS_APPROVED) {
            return ['O template oficial salvo nesta campanha nao esta aprovado no momento. Selecione outro template aprovado.'];
        }

        return [];
    }

    /**
     * @param array<string,mixed> $whatsapp
     * @return array<int,string>
     */
    private function resolveUnofficialTemplateWarnings(array $whatsapp): array
    {
        $templateId = $this->normalizeNullableInt($whatsapp['template_id'] ?? null);
        if (!$templateId) {
            return ['Selecione um template nao oficial ativo para continuar.'];
        }

        $template = CampaignTemplate::query()
            ->forWhatsApp()
            ->unofficial()
            ->find($templateId);

        if (!$template) {
            return ['O template nao oficial salvo nesta campanha foi removido. Selecione outro template ativo.'];
        }

        if (!$template->is_active) {
            return ['O template nao oficial salvo nesta campanha esta inativo. Selecione outro template ativo.'];
        }

        return [];
    }

    /**
     * @param array<string,mixed> $whatsapp
     */
    private function resolveCampaignWhatsappCompositionMode(array $whatsapp, bool $isOfficialProvider): string
    {
        $mode = strtolower(trim((string) ($whatsapp['composition_mode'] ?? '')));
        if (in_array($mode, ['manual', 'template'], true)) {
            return $mode;
        }

        $hasLegacyText = trim((string) ($whatsapp['text'] ?? '')) !== '';
        $hasLegacyMessageType = trim((string) ($whatsapp['message_type'] ?? '')) !== '';
        $hasLegacyMedia = is_array($whatsapp['media'] ?? null) && (array) ($whatsapp['media'] ?? []) !== [];

        if ($hasLegacyText || $hasLegacyMessageType || $hasLegacyMedia) {
            return 'manual';
        }

        return $isOfficialProvider ? 'template' : 'manual';
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
