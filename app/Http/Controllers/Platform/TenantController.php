<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantLocalizacao;
use App\Models\Platform\Pais;
use App\Models\Platform\Estado;
use App\Models\Platform\Cidade;
use App\Models\Platform\SystemSetting;
use App\Services\TenantProvisioner;
use App\Services\AsaasService;
use App\Http\Requests\TenantRequest;

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
        return view('platform.tenants.show', compact('tenant'));
    }

    public function store(TenantRequest $request, AsaasService $asaas)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $tenant = Tenant::create($validated);

            // 🔹 Cria localização, se informada
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

            // 🔹 Cria cliente no Asaas
            $asaasResponse = $asaas->createCustomer($tenant->toArray());

            if (isset($asaasResponse['id'])) {
                // ✅ Sucesso
                $tenant->update([
                    'asaas_customer_id' => $asaasResponse['id'],
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => null,
                ]);
            } else {
                // ⚠️ Falha na resposta
                $tenant->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'failed',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => json_encode($asaasResponse),
                ]);
            }

            DB::commit();

            // 🔹 Cria banco do tenant
            TenantProvisioner::createDatabase($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant criado e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if (isset($tenant)) {
                $tenant->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'failed',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => $e->getMessage(),
                ]);
            }

            Log::error('❌ Erro ao criar tenant no Asaas', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->withErrors(['general' => 'Erro ao criar tenant.']);
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

    public function update(TenantRequest $request, Tenant $tenant, AsaasService $asaas)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            // 🔹 Atualiza dados locais (tabela principal)
            TenantProvisioner::updateTenant($tenant, $validated);

            // 🔹 Atualiza ou cria localização
            $dadosLocalizacao = [
                'endereco'    => $request->endereco,
                'n_endereco'  => $request->n_endereco,
                'complemento' => $request->complemento,
                'bairro'      => $request->bairro,
                'cep'         => $request->cep,
                'pais_id'     => $request->pais_id,
                'estado_id'   => $request->estado_id,
                'cidade_id'   => $request->cidade_id,
            ];

            $tenant->localizacao
                ? $tenant->localizacao->update($dadosLocalizacao)
                : $tenant->localizacao()->create($dadosLocalizacao);

            // 🔹 Sincroniza com o Asaas
            if ($tenant->asaas_customer_id) {
                $asaasResponse = $asaas->updateCustomer($tenant->asaas_customer_id, $tenant->toArray());
            } else {
                $asaasResponse = $asaas->createCustomer($tenant->toArray());
                if (isset($asaasResponse['id'])) {
                    $tenant->asaas_customer_id = $asaasResponse['id'];
                }
            }

            // 🔹 Atualiza status da sincronização
            if (isset($asaasResponse['id']) && empty($asaasResponse['error'])) {
                // ✅ Sucesso
                $tenant->update([
                    'asaas_synced' => true,
                    'asaas_sync_status' => 'success',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => null,
                ]);
            } else {
                // ⚠️ Falha (mantém dados locais e loga erro)
                $tenant->update([
                    'asaas_synced' => false,
                    'asaas_sync_status' => 'failed',
                    'asaas_last_sync_at' => now(),
                    'asaas_last_error' => json_encode($asaasResponse, JSON_UNESCAPED_UNICODE),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant atualizado e sincronizado com o Asaas.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // 🔹 Marca falha da sincronização
            $tenant->update([
                'asaas_synced' => false,
                'asaas_sync_status' => 'failed',
                'asaas_last_sync_at' => now(),
                'asaas_last_error' => $e->getMessage(),
            ]);

            Log::error('❌ Erro ao atualizar tenant', [
                'tenant_id' => $tenant->id,
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Erro ao atualizar tenant.']);
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
}
