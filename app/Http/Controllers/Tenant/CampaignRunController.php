<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignRunController extends Controller
{
    public function index(Campaign $campaign)
    {
        return view('tenant.campaigns.runs.index', [
            'campaign' => $campaign,
        ]);
    }

    public function gridData(Campaign $campaign, Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('perPage', $request->input('limit', 10))));

        $query = CampaignRun::query()
            ->where('campaign_id', $campaign->id);

        $search = $this->extractSearchTerm($request);
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('id', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%');
            });
        }

        $this->applySort($request, $query);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $data = [];

        foreach ($paginator->items() as $run) {
            $totals = is_array($run->totals_json) ? $run->totals_json : [];
            $totalsSummary = sprintf(
                'total:%d · sent:%d · error:%d · skipped:%d · pending:%d',
                (int) ($totals['total'] ?? 0),
                (int) ($totals['success'] ?? 0),
                (int) ($totals['error'] ?? 0),
                (int) ($totals['skipped'] ?? 0),
                (int) ($totals['pending'] ?? 0),
            );

            $data[] = [
                'id' => (string) $run->id,
                'status_badge' => $this->renderStatusBadge($run),
                'started_at' => $run->started_at ? $run->started_at->format('d/m/Y H:i') : '-',
                'finished_at' => $run->finished_at ? $run->finished_at->format('d/m/Y H:i') : '-',
                'totals' => $totalsSummary,
                'actions' => $this->renderActions($campaign, $run),
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

    private function renderStatusBadge(CampaignRun $run): string
    {
        if (view()->exists('tenant.campaigns.runs.partials.status_badge')) {
            return view('tenant.campaigns.runs.partials.status_badge', [
                'run' => $run,
            ])->render();
        }

        return e((string) $run->status);
    }

    private function renderActions(Campaign $campaign, CampaignRun $run): string
    {
        if (view()->exists('tenant.campaigns.runs.partials.actions')) {
            return view('tenant.campaigns.runs.partials.actions', [
                'campaign' => $campaign,
                'run' => $run,
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
            'id' => 'id',
            'status' => 'status',
            'status_badge' => 'status',
            'started_at' => 'started_at',
            'finished_at' => 'finished_at',
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
}
