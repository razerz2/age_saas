<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\CampaignRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignRunController extends Controller
{
    use HandlesGridRequests;

    public function index(string $slug, Campaign $campaign)
    {
        return view('tenant.campaigns.runs.index', [
            'campaign' => $campaign,
        ]);
    }

    public function gridData(string $slug, Campaign $campaign, Request $request): JsonResponse
    {
        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        $query = CampaignRun::query()
            ->where('campaign_id', $campaign->id);

        $search = $this->gridSearch($request);
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
            'meta' => $this->gridMeta($paginator),
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

        $sort = $this->gridSort($request, $sortable, 'id', 'desc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'id') {
            $query->orderByDesc('id');
        }
    }
}
