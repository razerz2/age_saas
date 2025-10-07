<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Services\TenantProvisioner;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::latest()->paginate(10);
        return view('platform.tenants.index', compact('tenants'));
    }

    public function create()
    {
        return view('platform.tenants.create');
    }

    public function show($id)
    {
        $tenant = Tenant::findOrFail($id);
        return view('Platform.tenants.show', compact('tenant'));
    }


    public function store(Request $request)
    {
        ##dd($request->all());

        /*$data = $request->validate([
            'legal_name'  => 'required|string|max:255',
            'trade_name'  => 'nullable|string|max:255',
            'document'    => 'nullable|string|max:30',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:20',
            'subdomain'   => 'required|string|unique:tenants,subdomain',
            'db_host'     => 'required|string',
            'db_port'     => 'required|integer',
            'db_name'     => 'required|string|unique:tenants,db_name',
            'db_user'     => 'required|string',
            'db_password_enc' => 'required|string',
            'status'      => 'required|in:active,suspended,trial,cancelled',
            'trial_ends_at' => 'nullable|date',
        ]);
        */

        $data = $request->all();

        try {
            DB::beginTransaction();
            $tenant = Tenant::create($data);
            DB::commit();

            // 🔹 Aqui você pode chamar o provisionamento do banco do tenant
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
                ->withErrors(['general' => 'Ocorreu um erro ao criar o tenant. Verifique os dados ou tente novamente.']);
        }
    }


    public function edit(Tenant $tenant)
    {
        return view('platform.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'legal_name'      => 'required|string|max:255',
            'trade_name'      => 'nullable|string|max:255',
            'document'        => 'nullable|string|max:30',
            'email'           => 'nullable|email',
            'phone'           => 'nullable|string|max:20',
            'subdomain'       => "required|string|unique:tenants,subdomain,{$tenant->id},id",
            'db_host'         => 'required|string',
            'db_port'         => 'required|integer',
            'db_name'         => "required|string|unique:tenants,db_name,{$tenant->id},id",
            'db_username'     => 'required|string',
            'db_password' => 'required|string',
            'status'          => 'required|in:active,suspended,trial,cancelled',
            'trial_ends_at'   => 'nullable|date',
        ]);

        try {
            TenantProvisioner::updateTenant($tenant, $data);

            return redirect()
                ->route('platform.tenants.index')
                ->with('success', 'Tenant atualizado com sucesso.');
        } catch (\Throwable $e) {
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
