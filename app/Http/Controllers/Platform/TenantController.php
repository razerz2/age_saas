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
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        $data = $request->all();

        try {
            DB::beginTransaction();

            // 🔹 Cria o tenant base
            $tenant = Tenant::create($data);

            // 🔹 Cria a localização (opcional)
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

            // 🔹 Provisiona o banco do tenant
            TenantProvisioner::createDatabase($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant criado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erro ao criar tenant', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocorreu um erro ao criar o tenant.']);
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

    public function update(Request $request, Tenant $tenant)
    {
        try {
            DB::beginTransaction();

            // 🔹 Atualiza dados principais do tenant
            TenantProvisioner::updateTenant($tenant, $request->all());

            // 🔹 Atualiza/cria localização
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

            if ($tenant->localizacao) {
                $tenant->localizacao->update($dadosLocalizacao);
            } elseif ($request->filled('endereco')) {
                $tenant->localizacao()->create($dadosLocalizacao);
            }

            DB::commit();

            return redirect()
                ->route('platform.tenants.index')
                ->with('success', 'Tenant atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar tenant', [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Erro ao atualizar tenant.']);
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            TenantProvisioner::destroyTenant($tenant);

            return redirect()
                ->route('Platform.tenants.index')
                ->with('success', 'Tenant removido com sucesso, incluindo banco e usuário.');
        } catch (\Throwable $e) {
            Log::error('Erro ao excluir tenant', [
                'tenant' => $tenant->id,
                'erro'   => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['general' => 'Erro ao excluir tenant.']);
        }
    }
}