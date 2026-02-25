<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreCampaignRequest;
use App\Http\Requests\Tenant\UpdateCampaignRequest;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRun;
use App\Models\Tenant\User;
use App\Services\Tenant\CampaignChannelGate;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    private const MODULE_DISABLED_MESSAGE = 'Campanhas indisponíveis: configure sua API de Email e/ou WhatsApp em Integrações.';

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
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('perPage', $request->input('limit', 10))));
        $moduleEnabled = app(CampaignChannelGate::class)->availableChannels() !== [];

        $query = Campaign::query();

        $search = $this->extractSearchTerm($request);
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
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function create(CampaignChannelGate $gate)
    {
        return view('tenant.campaigns.create', [
            'availableChannels' => $gate->availableChannels(),
        ]);
    }

    public function store(StoreCampaignRequest $request)
    {
        $validated = $request->validated();

        $channels = $this->extractChannels($request);
        $type = (string) ($validated['type'] ?? $request->input('type', 'manual'));
        $createdBy = auth('tenant')->id() ?? auth()->id();

        $campaign = Campaign::create([
            'name' => $validated['name'],
            'type' => $type,
            'status' => 'draft',
            'channels_json' => $channels,
            'content_json' => $this->extractJsonPayload($request, $validated, 'content_json'),
            'audience_json' => $this->extractJsonPayload($request, $validated, 'audience_json'),
            'automation_json' => $this->extractAutomationPayload($request, $validated, $type),
            'scheduled_at' => $request->input('scheduled_at') ?: null,
            'created_by' => $createdBy,
        ]);

        return redirect()
            ->route('tenant.campaigns.show', [
                'slug' => $this->resolveSlug($request),
                'campaign' => $campaign->id,
            ])
            ->with('success', 'Campanha criada com sucesso.');
    }

    public function show(Campaign $campaign, CampaignChannelGate $gate)
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
        ]);
    }

    public function edit(Campaign $campaign, CampaignChannelGate $gate)
    {
        return view('tenant.campaigns.edit', [
            'campaign' => $campaign,
            'availableChannels' => $gate->availableChannels(),
        ]);
    }

    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        $validated = $request->validated();
        $type = (string) ($validated['type'] ?? $request->input('type', 'manual'));

        $campaign->update([
            'name' => $validated['name'],
            'type' => $type,
            'channels_json' => $this->extractChannels($request),
            'content_json' => $this->extractJsonPayload($request, $validated, 'content_json'),
            'audience_json' => $this->extractJsonPayload($request, $validated, 'audience_json'),
            'automation_json' => $this->extractAutomationPayload($request, $validated, $type),
            'scheduled_at' => $request->input('scheduled_at') ?: null,
        ]);

        return redirect()
            ->route('tenant.campaigns.show', [
                'slug' => $this->resolveSlug($request),
                'campaign' => $campaign->id,
            ])
            ->with('success', 'Campanha atualizada com sucesso.');
    }

    public function destroy(Campaign $campaign)
    {
        $slug = request()->route('slug') ?: tenant()->subdomain;

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

    private function extractSearchTerm(Request $request): string
    {
        $search = $request->input('search');

        if (is_array($search)) {
            return trim((string) ($search['value'] ?? ''));
        }

        return trim((string) $search);
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

        $sort = $request->input('sort');

        if (is_array($sort) && isset($sort['column'], $sort['direction'])) {
            $column = (string) $sort['column'];
            $direction = strtolower((string) $sort['direction']) === 'asc' ? 'asc' : 'desc';

            if (isset($sortable[$column])) {
                $query->orderBy($sortable[$column], $direction);
                return;
            }
        }

        if (is_string($sort) && $sort !== '') {
            $column = $sort;
            $direction = strtolower((string) $request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

            if (str_contains($sort, ':')) {
                [$column, $directionPart] = array_pad(explode(':', $sort, 2), 2, 'asc');
                $direction = strtolower($directionPart) === 'desc' ? 'desc' : 'asc';
            } elseif (str_starts_with($sort, '-')) {
                $column = ltrim($sort, '-');
                $direction = 'desc';
            }

            if (isset($sortable[$column])) {
                $query->orderBy($sortable[$column], $direction);
                return;
            }
        }

        $query->orderByDesc('created_at');
    }

    private function formatType(string $type): string
    {
        return match (strtolower($type)) {
            'manual' => 'Manual',
            'automated' => 'Automática',
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

    private function resolveSlug(Request $request): string
    {
        return (string) ($request->route('slug') ?: tenant()->subdomain);
    }

    /**
     * @return array{0:?Carbon,1:?string}
     */
    private function resolveNextAutomationRun(Campaign $campaign): array
    {
        $automation = is_array($campaign->automation_json) ? $campaign->automation_json : [];
        $scheduleType = strtolower(trim((string) data_get($automation, 'schedule.type', '')));
        $scheduleTime = trim((string) data_get($automation, 'schedule.time', ''));
        $timezone = trim((string) ($automation['timezone'] ?? ''));

        if ($scheduleType !== 'daily' || !preg_match('/^\d{2}:\d{2}$/', $scheduleTime)) {
            return [null, null];
        }

        if ($timezone === '') {
            $timezone = (string) config('campaigns.automation.default_timezone', 'America/Campo_Grande');
        }

        try {
            $localNow = Carbon::now($timezone);
            [$hour, $minute] = array_pad(explode(':', $scheduleTime, 2), 2, '00');

            $nextLocal = $localNow->copy()->setTime((int) $hour, (int) $minute, 0);
            if ($nextLocal->lessThanOrEqualTo($localNow)) {
                $nextLocal->addDay();
            }

            return [$nextLocal, $timezone];
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
