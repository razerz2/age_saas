<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\StartCampaignJob;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Patient;
use App\Services\Tenant\CampaignChannelGate;
use App\Services\Tenant\CampaignDeliveryService;
use App\Services\Tenant\CampaignStarter;
use DomainException;
use Illuminate\Http\JsonResponse;
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
        $selectedPatient = null;

        $validator = Validator::make($request->all(), [
            'channel' => ['nullable', 'in:email,whatsapp'],
            'destination' => ['nullable', 'string', 'max:255'],
            'patient_id' => ['nullable', 'uuid'],
            'overrides' => ['nullable', 'array'],
        ], [
            'channel.in' => 'Canal de teste invalido.',
            'patient_id.uuid' => 'Paciente de teste invalido.',
        ]);

        $validator->after(function ($validator) use ($request, $campaignChannels, &$selectedPatient) {
            if ($campaignChannels === []) {
                $validator->errors()->add('channel', 'Esta campanha nao possui canais configurados para envio.');
                return;
            }

            $selectedChannel = strtolower(trim((string) $request->input('channel', '')));
            if (count($campaignChannels) > 1 && $selectedChannel === '') {
                $validator->errors()->add('channel', 'Selecione o canal para enviar o teste.');
            }

            if ($selectedChannel !== '' && !in_array($selectedChannel, $campaignChannels, true)) {
                $validator->errors()->add('channel', 'Canal nao configurado nesta campanha.');
            }

            $channelForValidation = $selectedChannel !== ''
                ? $selectedChannel
                : ($campaignChannels[0] ?? null);

            $selectedPatientId = trim((string) $request->input('patient_id', ''));
            if ($selectedPatientId !== '') {
                $selectedPatient = Patient::query()
                    ->where('id', $selectedPatientId)
                    ->first(['id', 'full_name', 'cpf', 'email', 'phone']);

                if (!$selectedPatient) {
                    $validator->errors()->add('patient_id', 'Paciente selecionado nao foi encontrado.');
                    return;
                }

                if ($channelForValidation === 'email' && !$this->isValidEmail($selectedPatient->email ?? null)) {
                    $validator->errors()->add('patient_id', 'O paciente selecionado nao possui e-mail cadastrado.');
                }

                if ($channelForValidation === 'whatsapp' && !$this->hasPhone($selectedPatient->phone ?? null)) {
                    $validator->errors()->add('patient_id', 'O paciente selecionado nao possui telefone/WhatsApp cadastrado.');
                }

                return;
            }

            $destination = trim((string) $request->input('destination', ''));
            if ($destination === '') {
                $validator->errors()->add('destination', 'Informe o destino do teste.');
                return;
            }

            if ($channelForValidation === 'email' && filter_var($destination, FILTER_VALIDATE_EMAIL) === false) {
                $validator->errors()->add('destination', 'Informe um email valido para o teste.');
            }

            if ($channelForValidation === 'whatsapp' && $destination === '') {
                $validator->errors()->add('destination', 'Informe um telefone valido para o teste.');
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

        $overrides = $request->input('overrides', []);
        $overrides = is_array($overrides) ? $overrides : [];
        $destination = trim((string) $request->input('destination', ''));
        $vars = $overrides;
        $meta = [];

        $selectedPatientId = trim((string) $request->input('patient_id', ''));
        if ($selectedPatientId !== '') {
            $selectedPatient = $selectedPatient instanceof Patient
                ? $selectedPatient
                : Patient::query()->where('id', $selectedPatientId)->first(['id', 'full_name', 'cpf', 'email', 'phone']);

            if ($selectedPatient) {
                $destination = $channel === 'email'
                    ? (string) $this->normalizeEmail($selectedPatient->email ?? null)
                    : (string) $this->normalizePhone($selectedPatient->phone ?? null);

                $patientVars = $this->buildTestVarsFromPatient($selectedPatient);
                $vars = array_replace_recursive($patientVars, $overrides);
                $meta['test_recipient_mode'] = 'patient';
                $meta['test_patient_id'] = (string) $selectedPatient->id;
                $meta['test_patient_name'] = trim((string) ($selectedPatient->full_name ?? ''));
            }
        } else {
            $meta['test_recipient_mode'] = 'manual';
        }

        $result = $this->deliveryService->sendTest(
            $campaign,
            $channel,
            $destination,
            $vars,
            $meta
        );

        if ($result['success']) {
            return back()->with('success', 'Teste de campanha enviado com sucesso.');
        }

        return back()->with('warning', $result['error_message'] ?? 'Falha ao enviar teste da campanha.');
    }

    public function searchTestPatients(string $slug, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $queryText = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 10);

        $query = Patient::query()->orderBy('full_name');

        if ($queryText !== '') {
            $pattern = '%' . mb_strtolower($queryText) . '%';

            $query->where(function ($builder) use ($pattern) {
                $builder
                    ->whereRaw('LOWER(full_name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(cpf) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(phone) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]);
            });
        }

        $patients = $query
            ->limit($limit)
            ->get(['id', 'full_name', 'cpf', 'email', 'phone'])
            ->map(function (Patient $patient): array {
                return [
                    'id' => (string) $patient->id,
                    'name' => trim((string) ($patient->full_name ?? '')),
                    'cpf' => trim((string) ($patient->cpf ?? '')),
                    'email' => trim((string) ($patient->email ?? '')),
                    'phone' => trim((string) ($patient->phone ?? '')),
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $patients,
        ]);
    }

    public function start(string $slug, Campaign $campaign, Request $request, CampaignChannelGate $gate): RedirectResponse
    {
        if (!$this->isManualCampaign($campaign)) {
            return back()->with('warning', 'Esta acao esta disponivel apenas para campanhas manuais.');
        }

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
        if (!$this->isManualCampaign($campaign)) {
            return back()->with('warning', 'Esta acao esta disponivel apenas para campanhas manuais.');
        }

        $validator = Validator::make($request->all(), [
            'scheduled_at' => ['required', 'date'],
        ], [
            'scheduled_at.required' => 'Informe data e horario para agendamento.',
            'scheduled_at.date' => 'Data/hora de agendamento invalida.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $scheduledAtRaw = (string) $request->input('scheduled_at', '');
            try {
                $scheduledAt = Carbon::parse($scheduledAtRaw);
                if ($scheduledAt->lt(now()->subMinute())) {
                    $validator->errors()->add('scheduled_at', 'O agendamento deve ser no presente ou futuro.');
                }
            } catch (\Throwable) {
                $validator->errors()->add('scheduled_at', 'Data/hora de agendamento invalida.');
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

    private function isManualCampaign(Campaign $campaign): bool
    {
        return strtolower(trim((string) $campaign->type)) === 'manual';
    }

    private function isValidEmail(mixed $value): bool
    {
        return $this->normalizeEmail($value) !== null;
    }

    private function hasPhone(mixed $value): bool
    {
        return $this->normalizePhone($value) !== null;
    }

    private function normalizeEmail(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $email = trim((string) $value);
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $email;
    }

    private function normalizePhone(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $phone = trim((string) $value);
        return $phone !== '' ? $phone : null;
    }

    /**
     * @return array<string,mixed>
     */
    private function buildTestVarsFromPatient(Patient $patient): array
    {
        $fullName = trim((string) ($patient->full_name ?? ''));
        $firstName = $this->extractFirstName($fullName);
        $email = $this->normalizeEmail($patient->email ?? null);
        $phone = $this->normalizePhone($patient->phone ?? null);
        $tenant = tenant();
        $clinicName = trim((string) ($tenant?->trade_name ?? $tenant?->legal_name ?? ''));
        $clinicPhone = trim((string) ($tenant?->phone ?? ''));
        $clinicEmail = trim((string) ($tenant?->email ?? ''));
        $clinicAddress = trim((string) ($tenant?->address ?? ''));
        $slug = trim((string) ($tenant?->subdomain ?? ''));
        $publicBookingUrl = $slug !== '' ? url('/workspace/' . $slug . '/agendamento/identificar') : null;

        return [
            'patient' => [
                'id' => (string) $patient->id,
                'name' => $fullName !== '' ? $fullName : null,
                'full_name' => $fullName !== '' ? $fullName : null,
                'first_name' => $firstName,
                'cpf' => trim((string) ($patient->cpf ?? '')) ?: null,
                'email' => $email,
                'phone' => $phone,
            ],
            'clinic' => [
                'name' => $clinicName !== '' ? $clinicName : null,
                'phone' => $clinicPhone !== '' ? $clinicPhone : null,
                'email' => $clinicEmail !== '' ? $clinicEmail : null,
                'address' => $clinicAddress !== '' ? $clinicAddress : null,
            ],
            'links' => [
                'public_booking' => $publicBookingUrl,
                'portal' => null,
                'whatsapp' => null,
            ],
            'now' => [
                'date' => now()->toDateString(),
            ],
        ];
    }

    private function extractFirstName(string $fullName): ?string
    {
        $normalized = trim($fullName);
        if ($normalized === '') {
            return null;
        }

        $parts = preg_split('/\s+/', $normalized);
        $first = is_array($parts) ? trim((string) ($parts[0] ?? '')) : '';

        return $first !== '' ? $first : null;
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
