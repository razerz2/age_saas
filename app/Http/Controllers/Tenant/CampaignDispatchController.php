<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\StartCampaignJob;
use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignDeliveryService;
use App\Services\Tenant\CampaignStarter;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CampaignDispatchController extends Controller
{
    public function __construct(
        private readonly CampaignStarter $starter,
        private readonly CampaignDeliveryService $deliveryService
    ) {
    }

    public function sendTest(string $slug, Campaign $campaign, Request $request, CampaignChannelGate $gate): RedirectResponse
    {
        $campaignChannels = $this->normalizeChannels($campaign->channels_json);

        $validator = Validator::make($request->all(), [
            'channel' => ['nullable', 'in:email,whatsapp'],
            'destination' => ['required', 'string', 'max:255'],
            'overrides' => ['nullable', 'array'],
        ], [
            'destination.required' => 'Informe o destino do teste.',
            'channel.in' => 'Canal de teste inválido.',
        ]);

        $validator->after(function ($validator) use ($request, $campaignChannels) {
            if ($campaignChannels === []) {
                $validator->errors()->add('channel', 'Esta campanha não possui canais configurados para envio.');
                return;
            }

            $selectedChannel = strtolower(trim((string) $request->input('channel', '')));

            if (count($campaignChannels) > 1 && $selectedChannel === '') {
                $validator->errors()->add('channel', 'Selecione o canal para enviar o teste.');
            }

            if ($selectedChannel !== '' && !in_array($selectedChannel, $campaignChannels, true)) {
                $validator->errors()->add('channel', 'Canal não configurado nesta campanha.');
            }

            $destination = trim((string) $request->input('destination', ''));
            $channelForValidation = $selectedChannel !== ''
                ? $selectedChannel
                : ($campaignChannels[0] ?? null);

            if ($channelForValidation === 'email' && filter_var($destination, FILTER_VALIDATE_EMAIL) === false) {
                $validator->errors()->add('destination', 'Informe um email válido para o teste.');
            }

            if ($channelForValidation === 'whatsapp' && $destination === '') {
                $validator->errors()->add('destination', 'Informe um telefone válido para o teste.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $channel = strtolower(trim((string) $request->input('channel', '')));
        if ($channel === '') {
            $channel = $campaignChannels[0] ?? '';
        }

        try {
            $gate->assertChannelsEnabled([$channel]);
        } catch (DomainException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        $destination = trim((string) $request->input('destination'));
        $overrides = $request->input('overrides', []);
        $overrides = is_array($overrides) ? $overrides : [];

        $result = $this->deliveryService->sendTest(
            $campaign,
            $channel,
            $destination,
            $overrides
        );

        if ($result['success']) {
            return back()->with('success', 'Teste de campanha enviado com sucesso.');
        }

        return back()->with('warning', $result['error_message'] ?? 'Falha ao enviar teste da campanha.');
    }

    public function start(string $slug, Campaign $campaign, Request $request, CampaignChannelGate $gate): RedirectResponse
    {
        try {
            $gate->assertChannelsEnabled($this->normalizeChannels($campaign->channels_json));
        } catch (DomainException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        if (strtolower((string) $campaign->status) === 'draft') {
            $campaign->status = 'active';
        }
        $campaign->scheduled_at = null;
        $campaign->save();

        $initiatedBy = auth('tenant')->id() ?? auth()->id();
        $result = $this->starter->startCampaign($campaign, is_int($initiatedBy) ? $initiatedBy : null, 'manual', true);

        return redirect()
            ->route('tenant.campaigns.runs.index', [
                'slug' => $request->route('slug'),
                'campaign' => $campaign->id,
            ])
            ->with($result['created'] ? 'success' : 'warning', $result['message']);
    }

    public function schedule(string $slug, Campaign $campaign, Request $request, CampaignChannelGate $gate): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => ['required', 'date'],
        ], [
            'scheduled_at.required' => 'Informe data e horário para agendamento.',
            'scheduled_at.date' => 'Data/hora de agendamento inválida.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $scheduledAtRaw = (string) $request->input('scheduled_at', '');
            try {
                $scheduledAt = Carbon::parse($scheduledAtRaw);
                if ($scheduledAt->lt(now()->subMinute())) {
                    $validator->errors()->add('scheduled_at', 'O agendamento deve ser no presente ou futuro.');
                }
            } catch (\Throwable) {
                $validator->errors()->add('scheduled_at', 'Data/hora de agendamento inválida.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $gate->assertChannelsEnabled($this->normalizeChannels($campaign->channels_json));
        } catch (DomainException $exception) {
            return back()->with('warning', $exception->getMessage());
        }

        $scheduledAt = Carbon::parse((string) $request->input('scheduled_at'));
        $campaign->scheduled_at = $scheduledAt;
        if (strtolower((string) $campaign->status) === 'draft') {
            $campaign->status = 'active';
        }
        $campaign->save();

        $tenantId = tenant()?->id;
        $tenantId = is_string($tenantId) ? trim($tenantId) : '';
        if ($tenantId !== '') {
            $pendingDispatch = StartCampaignJob::dispatch($tenantId, (int) $campaign->id)->delay($scheduledAt);
            $queue = (string) config('campaigns.queue', 'campaigns');
            if ($queue !== '' && method_exists($pendingDispatch, 'onQueue')) {
                $pendingDispatch->onQueue($queue);
            }
            if (method_exists($pendingDispatch, 'afterCommit')) {
                $pendingDispatch->afterCommit();
            }
        }

        return back()->with('success', 'Campanha agendada com sucesso.');
    }

    public function pause(string $slug, Campaign $campaign): RedirectResponse
    {
        $campaign->status = 'paused';
        $campaign->save();

        return back()->with('success', 'Campanha pausada.');
    }

    public function resume(string $slug, Campaign $campaign): RedirectResponse
    {
        $campaign->status = 'active';
        $campaign->save();

        return back()->with('success', 'Campanha retomada.');
    }

    /**
     * @param mixed $channels
     * @return array<int,string>
     */
    private function normalizeChannels(mixed $channels): array
    {
        if (!is_array($channels)) {
            return [];
        }

        $normalized = [];
        foreach ($channels as $channel) {
            $value = strtolower(trim((string) $channel));
            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            if (in_array($value, ['email', 'whatsapp'], true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }
}
