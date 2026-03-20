<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\TenantRequest;
use App\Mail\TenantAdminCredentialsMail;
use App\Models\Platform\Cidade;
use App\Models\Platform\Estado;
use App\Models\Platform\Plan;
use App\Models\Platform\Tenant;
use App\Services\AsaasService;
use App\Services\Platform\TenantCreatorService;
use App\Services\Platform\WhatsAppOfficialMessageService;
use App\Services\SystemSettingsService;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public const CREATED_PENDING_COMMERCIAL_MESSAGE = 'Tenant criada com sucesso, porem o acesso esta bloqueado ate que um plano e uma assinatura validos sejam definidos.';
    public const CREATED_ELIGIBLE_MESSAGE = 'Tenant criada com sucesso e apta para acesso.';
    private const BRAZIL_COUNTRY_ID = 31;

    protected $tenantCreator;
    protected WhatsAppOfficialMessageService $officialWhatsApp;

    public function __construct(
        TenantCreatorService $tenantCreator,
        WhatsAppOfficialMessageService $officialWhatsApp
    )
    {
        $this->tenantCreator = $tenantCreator;
        $this->officialWhatsApp = $officialWhatsApp;
    }

    public function index()
    {
        $tenants = Tenant::with(['activeSubscriptionRelation.plan'])
            ->orderBy('legal_name')
            ->get();

        return view('platform.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('name')->get();

        return view('platform.tenants.create', compact('plans'));
    }

    public function show($id)
    {
        $tenant = Tenant::with(
            'localizacao.estado',
            'localizacao.cidade',
            'admin',
            'activeSubscriptionRelation.plan'
        )->findOrFail($id);

        $adminUser = null;
        $tenantAdmin = $tenant->admin;
        $adminPassword = $tenantAdmin?->password ?? session('tenant_admin_password', null);
        $adminEmail = $tenantAdmin?->email;
        $loginUrl = $tenantAdmin?->login_url ?? url("/customer/{$tenant->subdomain}/login");

        try {
            config([
                'database.connections.tenant.host' => $tenant->db_host,
                'database.connections.tenant.port' => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');

            $emailToSearch = $adminEmail;
            if (! $emailToSearch) {
                $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
                $sanitizedSubdomain = ! empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
                $emailToSearch = "admin@{$sanitizedSubdomain}.com";
            }

            $adminUser = DB::connection('tenant')
                ->table('users')
                ->where('email', $emailToSearch)
                ->orWhere('name', 'Administrador')
                ->first();
        } catch (\Throwable $e) {
            Log::warning('Nao foi possivel buscar usuario admin do tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        return view('platform.tenants.show', compact('tenant', 'adminUser', 'adminPassword', 'loginUrl', 'tenantAdmin'));
    }

    public function store(TenantRequest $request)
    {
        try {
            $data = $request->validated();
            $tenant = $this->tenantCreator->create($data);

            $this->syncWithAsaas($tenant);

            if (! $tenant->isEligibleForAccess()) {
                return redirect()
                    ->route('Platform.tenants.show', $tenant->id)
                    ->with('warning', self::CREATED_PENDING_COMMERCIAL_MESSAGE)
                    ->with('tenant_needs_commercial_regularization', true);
            }

            return redirect()
                ->route('Platform.tenants.show', $tenant->id)
                ->with('success', self::CREATED_ELIGIBLE_MESSAGE);
        } catch (\Throwable $e) {
            Log::error('Erro ao criar tenant no controller', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao criar tenant: '.$e->getMessage()]);
        }
    }

    public function edit(Tenant $tenant)
    {
        $tenant->loadMissing(['activeSubscriptionRelation.plan']);

        $localizacao = $tenant->localizacao;

        $estados = $localizacao
            ? Estado::where('pais_id', self::BRAZIL_COUNTRY_ID)->orderBy('nome_estado')->get()
            : collect();

        $cidades = $localizacao
            ? Cidade::where('estado_id', $localizacao->estado_id)->orderBy('nome_cidade')->get()
            : collect();

        return view('platform.tenants.edit', compact('tenant', 'estados', 'cidades', 'localizacao'));
    }

    public function update(TenantRequest $request, Tenant $tenant)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            unset(
                $validated['db_host'],
                $validated['db_port'],
                $validated['db_name'],
                $validated['db_username'],
                $validated['db_password']
            );

            $tenant->update($validated);

            $tenant->localizacao()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'tenant_id' => $tenant->id,
                    'endereco' => $request->endereco,
                    'n_endereco' => $request->n_endereco,
                    'complemento' => $request->complemento,
                    'bairro' => $request->bairro,
                    'cep' => $request->cep,
                    'pais_id' => self::BRAZIL_COUNTRY_ID,
                    'estado_id' => $request->estado_id,
                    'cidade_id' => $request->cidade_id,
                ]
            );

            DB::commit();

            $this->syncWithAsaas($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant atualizado e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar tenant', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao atualizar tenant. Consulte o log para mais detalhes.']);
        }
    }

    public function destroy(Tenant $tenant, AsaasService $asaas)
    {
        try {
            if ($tenant->asaas_customer_id) {
                $asaasResponse = $asaas->deleteCustomer($tenant->asaas_customer_id);

                if ((isset($asaasResponse['deleted']) && $asaasResponse['deleted'] === true) || empty($asaasResponse['error'])) {
                    $tenant->update([
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                } else {
                    $tenant->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => json_encode($asaasResponse, JSON_UNESCAPED_UNICODE),
                    ]);

                    Log::warning('Falha ao excluir cliente no Asaas', [
                        'tenant_id' => $tenant->id,
                        'asaas_response' => $asaasResponse,
                    ]);
                }
            }

            TenantProvisioner::destroyTenant($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant removido e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            Log::error('Erro ao excluir tenant', [
                'tenant_id' => $tenant->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao excluir tenant.']);
        }
    }

    public function syncWithAsaas(Tenant $tenant)
    {
        try {
            $asaas = new AsaasService();

            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'pending',
                'asaas_last_sync_at' => now(),
            ]);

            if (! $tenant->asaas_customer_id) {
                $searchResponse = $asaas->searchCustomer($tenant->email);

                if (! empty($searchResponse['data'][0]['id'])) {
                    $tenant->update(['asaas_customer_id' => $searchResponse['data'][0]['id']]);
                } else {
                    $createResponse = $asaas->createCustomer($tenant->toArray());

                    if (empty($createResponse) || ! isset($createResponse['id'])) {
                        $tenant->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'pending',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => 'Nao foi possivel criar cliente (resposta vazia ou invalida do Asaas).',
                        ]);

                        Log::warning("Tenant {$tenant->trade_name}: resposta invalida ao criar cliente Asaas.");

                        return back()->withErrors(['general' => 'Nao foi possivel sincronizar com o Asaas no momento. Tente novamente.']);
                    }

                    $tenant->update(['asaas_customer_id' => $createResponse['id']]);
                }
            } else {
                $updateResponse = $asaas->updateCustomer($tenant->asaas_customer_id, $tenant->toArray());
                if (isset($updateResponse['error'])) {
                    throw new \Exception('Erro ao atualizar cliente no Asaas: '.json_encode($updateResponse));
                }
            }

            $tenant->update([
                'asaas_synced' => true,
                'asaas_sync_status' => 'success',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => null,
            ]);

            Log::info("Tenant {$tenant->trade_name} sincronizado com o Asaas com sucesso.");

            return redirect()->back()->with('success', 'Tenant sincronizado com sucesso no Asaas!');
        } catch (\Throwable $e) {
            Log::error("Erro ao sincronizar tenant {$tenant->id}: {$e->getMessage()}");

            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Erro ao sincronizar com o Asaas.']);
        }
    }

    public function sendCredentials(Tenant $tenant)
    {
        try {
            $tenantAdmin = $tenant->admin;

            if (! $tenantAdmin) {
                return back()->withErrors(['general' => 'Credenciais do admin nao encontradas para este tenant.']);
            }

            if (! $tenant->email) {
                return back()->withErrors(['general' => 'Email do tenant nao esta configurado.']);
            }

            $adminEmail = $tenantAdmin->email;
            $adminPassword = $tenantAdmin->password;
            $loginUrl = $tenantAdmin->login_url ?? url("/t/{$tenant->subdomain}/login");

            $systemSettingsService = new SystemSettingsService();
            if (! $systemSettingsService->emailIsConfigured()) {
                return back()->withErrors(['general' => 'SMTP nao esta configurado. Configure o email antes de enviar credenciais.']);
            }

            Mail::to($tenant->email)->send(
                new TenantAdminCredentialsMail(
                    $tenant,
                    $loginUrl,
                    $adminEmail,
                    $adminPassword
                )
            );

            Log::info("Credenciais reenviadas para tenant {$tenant->id}", [
                'tenant_id' => $tenant->id,
                'email' => $tenant->email,
                'admin_email' => $adminEmail,
            ]);

            $this->officialWhatsApp->sendByKey(
                'credentials.resent',
                $tenant->phone,
                [
                    'customer_name' => $tenant->trade_name,
                    'tenant_name' => $tenant->trade_name,
                    'login_url' => $loginUrl,
                    'delivery_channel' => 'email',
                ],
                [
                    'controller' => static::class,
                    'tenant_id' => (string) $tenant->id,
                    'event' => 'credentials.resent',
                ]
            );

            return back()->with('success', 'Credenciais enviadas com sucesso para '.$tenant->email.'!');
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar credenciais do tenant', [
                'tenant_id' => $tenant->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao enviar credenciais. Consulte o log para mais detalhes.']);
        }
    }
}
