<?php

namespace App\Http\Middleware\Tenant;

use App\Services\Tenant\CampaignChannelGate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class EnsureCampaignModuleEnabled
{
    private const BLOCKED_MESSAGE = 'Nenhum canal de campanha está configurado. Configure os canais na aba Campanhas ou reutilize os canais de notificações.';

    public function __construct(
        private readonly CampaignChannelGate $campaignChannelGate
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->campaignChannelGate->availableChannels() !== []) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => self::BLOCKED_MESSAGE,
            ], 403);
        }

        $slug = (string) ($request->route('slug') ?? '');
        if ($slug !== '' && Route::has('tenant.campaigns.index')) {
            return redirect()
                ->route('tenant.campaigns.index', ['slug' => $slug])
                ->with('warning', self::BLOCKED_MESSAGE);
        }

        return redirect()
            ->back()
            ->with('warning', self::BLOCKED_MESSAGE);
    }
}
