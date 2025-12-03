<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Services\Platform\PreTenantProcessorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PreTenantController extends Controller
{
    /**
     * Lista pré-cadastros com filtros
     */
    public function index(Request $request)
    {
        $query = PreTenant::with(['plan', 'pais', 'estado', 'cidade'])
            ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $preTenants = $query->get();

        return view('platform.pre_tenants.index', compact('preTenants'));
    }

    /**
     * Visualiza detalhes do pré-cadastro
     */
    public function show(PreTenant $preTenant)
    {
        $preTenant->load(['plan', 'pais', 'estado', 'cidade', 'logs']);

        return view('platform.pre_tenants.show', compact('preTenant'));
    }

    /**
     * Aprova manualmente (força criação do tenant)
     */
    public function approve(PreTenant $preTenant)
    {
        try {
            if ($preTenant->isPaid()) {
                return redirect()
                    ->route('Platform.pre_tenants.show', $preTenant->id)
                    ->with('warning', 'Este pré-cadastro já foi processado.');
            }

            $processor = new PreTenantProcessorService();
            $processor->processPaid($preTenant);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_approval',
                'payload' => ['message' => 'Aprovado manualmente pelo administrador'],
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->with('success', 'Pré-cadastro aprovado e tenant criado com sucesso!');

        } catch (\Throwable $e) {
            Log::error('Erro ao aprovar pré-cadastro manualmente', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao processar pré-cadastro: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancela pré-cadastro
     */
    public function cancel(PreTenant $preTenant)
    {
        try {
            $preTenant->markAsCanceled();

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'manual_cancellation',
                'payload' => ['message' => 'Cancelado manualmente pelo administrador'],
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->with('success', 'Pré-cadastro cancelado com sucesso!');

        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar pré-cadastro', [
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('Platform.pre_tenants.show', $preTenant->id)
                ->withErrors(['error' => 'Erro ao cancelar pré-cadastro: ' . $e->getMessage()]);
        }
    }
}
