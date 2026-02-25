<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignRecipientController extends Controller
{
    public function index(Campaign $campaign, Request $request)
    {
        $runId = $this->normalizeNullableInt($request->input('run_id', $request->input('campaign_run_id')));

        return view('tenant.campaigns.recipients.index', [
            'campaign' => $campaign,
            'selectedRunId' => $runId,
        ]);
    }

    public function gridData(Campaign $campaign, Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('perPage', $request->input('limit', 10))));

        $query = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id);

        $runId = $this->normalizeNullableInt($request->input('run_id', $request->input('campaign_run_id')));
        if ($runId !== null) {
            $query->where('campaign_run_id', $runId);
        }

        $search = $this->extractSearchTerm($request);
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('channel', 'like', '%' . $search . '%')
                    ->orWhere('destination', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhere('error_message', 'like', '%' . $search . '%');
            });
        }

        $this->applySort($request, $query);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $data = [];

        foreach ($paginator->items() as $recipient) {
            $data[] = [
                'channel' => e($this->formatChannel((string) $recipient->channel)),
                'destination' => e((string) $recipient->destination),
                'status_badge' => $this->renderStatusBadge($recipient),
                'sent_at' => $recipient->sent_at ? $recipient->sent_at->format('d/m/Y H:i') : '-',
                'error_message' => e($this->formatError((string) ($recipient->error_message ?? ''))),
                'actions' => $this->renderActions($campaign, $recipient),
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

    private function renderStatusBadge(CampaignRecipient $recipient): string
    {
        if (view()->exists('tenant.campaigns.recipients.partials.status_badge')) {
            return view('tenant.campaigns.recipients.partials.status_badge', [
                'recipient' => $recipient,
            ])->render();
        }

        return e((string) $recipient->status);
    }

    private function renderActions(Campaign $campaign, CampaignRecipient $recipient): string
    {
        if (view()->exists('tenant.campaigns.recipients.partials.actions')) {
            return view('tenant.campaigns.recipients.partials.actions', [
                'campaign' => $campaign,
                'recipient' => $recipient,
            ])->render();
        }

        return '-';
    }

    private function formatChannel(string $channel): string
    {
        $channel = strtolower(trim($channel));

        return match ($channel) {
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($channel),
        };
    }

    private function formatError(string $message): string
    {
        $message = trim($message);
        if ($message === '') {
            return '-';
        }

        return Str::limit($message, 120);
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
            'channel' => 'channel',
            'destination' => 'destination',
            'status' => 'status',
            'status_badge' => 'status',
            'sent_at' => 'sent_at',
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

        $query->orderByDesc('id');
    }

    private function normalizeNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '' || !ctype_digit($raw)) {
            return null;
        }

        return (int) $raw;
    }
}
