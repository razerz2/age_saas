<?php

namespace App\Jobs\Tenant;

use App\Models\Platform\Tenant as PlatformTenant;
use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignStarter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;

class StartCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [30, 120, 300];

    public function __construct(
        private readonly string $tenantId,
        private readonly int $campaignId
    ) {
        $queue = (string) config('campaigns.queue', 'campaigns');
        if ($queue !== '') {
            $this->onQueue($queue);
        }
    }

    public function handle(CampaignStarter $starter): void
    {
        $tenant = PlatformTenant::find($this->tenantId);
        if (!$tenant) {
            Log::warning('campaign_start_job_tenant_not_found', [
                'tenant_id' => $this->tenantId,
                'campaign_id' => $this->campaignId,
            ]);
            return;
        }

        $tenant->makeCurrent();

        try {
            $campaign = Campaign::query()->find($this->campaignId);
            if (!$campaign) {
                Log::warning('campaign_start_job_campaign_not_found', [
                    'tenant_id' => $this->tenantId,
                    'campaign_id' => $this->campaignId,
                ]);
                return;
            }

            if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
                $delaySeconds = now()->diffInSeconds($campaign->scheduled_at, false);
                if ($delaySeconds > 5 && $this->job) {
                    $this->release($delaySeconds);
                    return;
                }
            }

            $result = $starter->startCampaign($campaign, null, 'scheduled', true);
            if ($result['created']) {
                $campaign->scheduled_at = null;
                if (strtolower((string) $campaign->status) === 'draft') {
                    $campaign->status = 'active';
                }
                $campaign->save();
            }
        } finally {
            SpatieTenant::forgetCurrent();
        }
    }
}
