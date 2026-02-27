<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignRecipientController extends Controller
{
    use HandlesGridRequests;

    public function index(string $slug, Campaign $campaign, Request $request)
    {
        $runId = $this->normalizeNullableInt($request->input('run_id', $request->input('campaign_run_id')));

        return view('tenant.campaigns.recipients.index', [
            'campaign' => $campaign,
            'selectedRunId' => $runId,
        ]);
    }

    public function gridData(string $slug, Campaign $campaign, Request $request): JsonResponse
    {
        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        $query = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id);

        $runId = $this->normalizeNullableInt($request->input('run_id', $request->input('campaign_run_id')));
        if ($runId !== null) {
            $query->where('campaign_run_id', $runId);
        }

        $search = $this->gridSearch($request);
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
            'meta' => $this->gridMeta($paginator),
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

    private function applySort(Request $request, $query): void
    {
        $sortable = [
            'id' => 'id',
            'channel' => 'channel',
            'destination' => 'destination',
            'status' => 'status',
            'status_badge' => 'status',
            'sent_at' => 'sent_at',
            'created_at' => 'created_at',
        ];

        $sort = $this->gridSort($request, $sortable, 'id', 'desc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'id') {
            $query->orderByDesc('id');
        }
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
