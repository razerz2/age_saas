<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\Pais;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Models\Platform\SystemSetting;
use App\Services\TenantProvisioner;
use App\Services\SystemSettingsService;
use App\Services\AsaasService;
use App\Http\Requests\TenantRequest;
use App\Mail\TenantAdminCredentialsMail;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::orderBy('legal_name')->get();
        return view('platform.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $defaultCountryId = sysconfig('country_id');
        $paises = Pais::orderBy('nome')->get();
        return view('platform.tenants.create', compact('paises', 'defaultCountryId'));
    }

    public function show($id)
    {
        $tenant = Tenant::with('localizacao.pais', 'localizacao.estado', 'localizacao.cidade')->findOrFail($id);
        
        // Buscar informações do usuário admin do tenant
        $adminUser = null;
        // Buscar senha da sessão (se foi criada recentemente) ou usar null
        $adminPassword = session('tenant_admin_password', null);
        
        try {
            // Configurar conexão do tenant temporariamente
            config([
                'database.connections.tenant.host'     => $tenant->db_host,
                'database.connections.tenant.port'     => $tenant->db_port,
                'database.connections.tenant.database' => $tenant->db_name,
                'database.connections.tenant.username' => $tenant->db_username,
                'database.connections.tenant.password' => $tenant->db_password,
            ]);
            
            DB::purge('tenant');
            DB::reconnect('tenant');
            
            // Buscar usuário admin (geralmente o primeiro usuário criado ou com email admin@{subdomain}.com)
            $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
            $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
            $adminEmail = "admin@{$sanitizedSubdomain}.com";
            
            $adminUser = DB::connection('tenant')
                ->table('users')
                ->where('email', $adminEmail)
                ->orWhere('name', 'Administrador')
                ->first();
                
        } catch (\Throwable $e) {
            Log::warning('Não foi possível buscar usuário admin do tenant', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
        
        // Gerar link de acesso
        $loginUrl = url("/t/{$tenant->subdomain}/login");
        
        return view('platform.tenants.show', compact('tenant', 'adminUser', 'adminPassword', 'loginUrl'));
    }

    public function store(TenantRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // 🔹 Gera os dados do banco automaticamente (sem salvar ainda)
            $dbConfig = TenantProvisioner::prepareDatabaseConfig(
                $validated['legal_name'],
                $validated['trade_name'] ?? null
            );

            // 🔹 Junta as infos do banco no array antes de salvar
            $validated = array_merge($validated, $dbConfig);

            // 🔹 Cria o tenant completo no banco principal
            $tenant = Tenant::create($validated);

            // 🔹 Cria a localização se houver
            if ($request->filled('endereco')) {
                TenantLocalizacao::create([
                    'tenant_id'   => $tenant->id,
                    'endereco'    => $request->endereco,
                    'n_endereco'  => $request->n_endereco,
                    'complemento' => $request->complemento,
                    'bairro'      => $request->bairro,
                    'cep'         => $request->cep,
                    'pais_id'     => $request->pais_id,
                    'estado_id'   => $request->estado_id,
                    'cidade_id'   => $request->cidade_id,
                ]);
            }

            DB::commit();

            // 🚀 Cria o banco físico e roda as migrations (retorna a senha gerada)
            $adminPassword = TenantProvisioner::createDatabase($tenant);

            // 📧 Enviar email com credenciais se SMTP estiver configurado
            $systemSettingsService = new SystemSettingsService();
            if ($systemSettingsService->emailIsConfigured()) {
                try {
                    // Limpar subdomínio para formato válido de domínio
                    $sanitizedSubdomain = preg_replace('/[^a-z0-9\-]/', '', Str::slug($tenant->subdomain));
                    $sanitizedSubdomain = !empty($sanitizedSubdomain) ? $sanitizedSubdomain : 'tenant';
                    $adminEmail = "admin@{$sanitizedSubdomain}.com";
                    $loginUrl = url("/t/{$tenant->subdomain}/login");

                    Mail::to($tenant->email)->send(
                        new TenantAdminCredentialsMail(
                            $tenant,
                            $loginUrl,
                            $adminEmail,
                            $adminPassword
                        )
                    );

                    Log::info("📧 Email com credenciais enviado para tenant {$tenant->id}", [
                        'email' => $tenant->email
                    ]);
                } catch (\Throwable $e) {
                    // Não falhar a criação do tenant se o email falhar
                    Log::error("❌ Erro ao enviar email com credenciais", [
                        'tenant_id' => $tenant->id,
                        'erro' => $e->getMessage()
                    ]);
                }
            } else {
                Log::info("⚠️ Email não enviado: SMTP não configurado para tenant {$tenant->id}");
            }

            // 🔄 Sincroniza com Asaas
            $this->syncWithAsaas($tenant);

            // Salvar senha temporariamente na sessão para exibir na view de show
            session()->flash('tenant_admin_password', $adminPassword);

            return redirect()
                ->route('Platform.tenants.show', $tenant->id)
                ->with('success', '✅ Tenant criado com sucesso e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('❌ Erro ao criar tenant', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao criar tenant. Consulte o log para mais detalhes.']);
        }
    }

    public function edit(Tenant $tenant)
    {
        $paises = Pais::orderBy('nome')->get();
        $localizacao = $tenant->localizacao;

        $estados = $localizacao
            ? Estado::where('pais_id', $localizacao->pais_id)->orderBy('nome_estado')->get()
            : collect();

        $cidades = $localizacao
            ? Cidade::where('estado_id', $localizacao->estado_id)->orderBy('nome_cidade')->get()
            : collect();

        return view('platform.tenants.edit', compact('tenant', 'paises', 'estados', 'cidades', 'localizacao'));
    }

    public function update(TenantRequest $request, Tenant $tenant)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            // 🔒 Removemos qualquer tentativa de alterar campos de banco
            unset(
                $validated['db_host'],
                $validated['db_port'],
                $validated['db_name'],
                $validated['db_username'],
                $validated['db_password']
            );

            // 🔹 Atualiza apenas dados empresariais
            $tenant->update($validated);

            // 🔹 Atualiza ou cria a localização
            $tenant->localizacao()
                ->updateOrCreate(['tenant_id' => $tenant->id], [
                    'tenant_id'   => $tenant->id,
                    'endereco'    => $request->endereco,
                    'n_endereco'  => $request->n_endereco,
                    'complemento' => $request->complemento,
                    'bairro'      => $request->bairro,
                    'cep'         => $request->cep,
                    'pais_id'     => $request->pais_id,
                    'estado_id'   => $request->estado_id,
                    'cidade_id'   => $request->cidade_id,
                ]);

            DB::commit();

            // 🔄 Sincroniza com Asaas apenas se os dados empresariais foram atualizados
            $this->syncWithAsaas($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', '✅ Tenant atualizado e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Erro ao atualizar tenant', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['general' => 'Erro ao atualizar tenant. Consulte o log para mais detalhes.']);
        }
    }


    public function destroy(Tenant $tenant, AsaasService $asaas)
    {
        try {
            // 🔹 Exclui cliente no Asaas, se existir
            if ($tenant->asaas_customer_id) {
                $asaasResponse = $asaas->deleteCustomer($tenant->asaas_customer_id);

                if ((isset($asaasResponse['deleted']) && $asaasResponse['deleted'] === true) || empty($asaasResponse['error'])) {
                    // ✅ Exclusão bem-sucedida
                    $tenant->update([
                        'asaas_synced' => true,
                        'asaas_sync_status' => 'success',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => null,
                    ]);
                } else {
                    // ⚠️ Falha na exclusão (API respondeu erro)
                    $tenant->update([
                        'asaas_synced' => false,
                        'asaas_sync_status' => 'failed',
                        'asaas_last_sync_at' => now(),
                        'asaas_last_error' => json_encode($asaasResponse, JSON_UNESCAPED_UNICODE),
                    ]);

                    Log::warning('⚠️ Falha ao excluir cliente no Asaas', [
                        'tenant_id' => $tenant->id,
                        'asaas_response' => $asaasResponse,
                    ]);
                }
            }

            // 🔹 Remove banco de dados e registros locais
            TenantProvisioner::destroyTenant($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant removido e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            // ❌ Falha geral (ex: timeout, exceção interna)
            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            Log::error('❌ Erro ao excluir tenant', [
                'tenant_id' => $tenant->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['general' => 'Erro ao excluir tenant.']);
        }
    }

    public function syncWithAsaas(Tenant $tenant)
    {
        try {
            $asaas = new AsaasService();

            // 🔹 Status inicial: pendente
            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'pending',
                'asaas_last_sync_at' => now(),
            ]);

            // 🔹 Se não tiver cliente no Asaas, tenta localizar por e-mail
            if (!$tenant->asaas_customer_id) {
                $searchResponse = $asaas->searchCustomer($tenant->email);

                if (!empty($searchResponse['data'][0]['id'])) {
                    $tenant->update(['asaas_customer_id' => $searchResponse['data'][0]['id']]);
                } else {
                    $createResponse = $asaas->createCustomer($tenant->toArray());

                    if (empty($createResponse) || !isset($createResponse['id'])) {
                        // Falha sem exceção → marcar como pendente
                        $tenant->update([
                            'asaas_synced' => false,
                            'asaas_sync_status' => 'pending',
                            'asaas_last_sync_at' => now(),
                            'asaas_last_error' => 'Não foi possível criar cliente (resposta vazia ou inválida do Asaas).',
                        ]);

                        Log::warning("⚠️ Tenant {$tenant->trade_name}: resposta inválida ao criar cliente Asaas.");
                        return back()->withErrors(['general' => 'Não foi possível sincronizar com o Asaas no momento. Tente novamente.']);
                    }

                    $tenant->update(['asaas_customer_id' => $createResponse['id']]);
                }
            } else {
                // 🔹 Cliente já existe → atualiza dados
                $updateResponse = $asaas->updateCustomer($tenant->asaas_customer_id, $tenant->toArray());
                if (isset($updateResponse['error'])) {
                    throw new \Exception('Erro ao atualizar cliente no Asaas: ' . json_encode($updateResponse));
                }
            }

            // 🔹 Se chegou até aqui, sucesso
            $tenant->update([
                'asaas_synced' => true,
                'asaas_sync_status' => 'success',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => null,
            ]);

            Log::info("✅ Tenant {$tenant->trade_name} sincronizado com o Asaas com sucesso.");

            return redirect()->back()->with('success', 'Tenant sincronizado com sucesso no Asaas!');
        } catch (\Throwable $e) {
            // 🔹 Exceções → erro real
            Log::error("❌ Erro ao sincronizar tenant {$tenant->id}: {$e->getMessage()}");

            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'Erro ao sincronizar com o Asaas.']);
        }
    }
}
