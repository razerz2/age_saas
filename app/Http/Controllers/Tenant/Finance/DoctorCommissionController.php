<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\DoctorCommission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorCommissionController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Lista todas as comissões
     */
    public function index(Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $query = DoctorCommission::with(['doctor.user', 'transaction']);

        $user = Auth::guard('tenant')->user();

        // Filtrar por médico conforme role
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            // User normalmente não vê comissões
            abort(403, 'Acesso negado.');
        }

        // Filtros opcionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id') && $user->role === 'admin') {
            $query->where('doctor_id', $request->doctor_id);
        }

        $commissions = $query->orderBy('created_at', 'desc')->paginate(30);

        $doctors = null;
        if ($user->role === 'admin') {
            $doctors = \App\Models\Tenant\Doctor::with('user')
                ->whereHas('user', function($q) {
                    $q->where('status', 'active');
                })
                ->orderBy('id')
                ->get();
        }

        return view('tenant.finance.commissions.index', compact('commissions', 'doctors'));
    }

    /**
     * Exibe detalhes da comissão
     */
    public function show(DoctorCommission $commission)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Verificar acesso por role
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if (!$doctor || $commission->doctor_id !== $doctor->id) {
                abort(403, 'Acesso negado.');
            }
        } elseif ($user->role === 'user') {
            abort(403, 'Acesso negado.');
        }

        $commission->load(['doctor.user', 'transaction.appointment', 'transaction.patient']);

        return view('tenant.finance.commissions.show', compact('commission'));
    }

    /**
     * Marca comissão como paga
     */
    public function markPaid(DoctorCommission $commission)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Apenas admin pode marcar como paga
        if ($user->role !== 'admin') {
            abort(403, 'Apenas administradores podem marcar comissões como pagas.');
        }

        if ($commission->isPaid()) {
            return redirect()->route('tenant.finance.commissions.show', [
                'slug' => tenant()->subdomain,
                'commission' => $commission->id
            ])->with('error', 'Esta comissão já está marcada como paga.');
        }

        try {
            $commission->markAsPaid();

            Log::info('Comissão marcada como paga', [
                'commission_id' => $commission->id,
                'doctor_id' => $commission->doctor_id,
                'amount' => $commission->amount,
                'user_id' => $user->id,
            ]);

            return redirect()->route('tenant.finance.commissions.show', [
                'slug' => tenant()->subdomain,
                'commission' => $commission->id
            ])->with('success', 'Comissão marcada como paga com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao marcar comissão como paga', [
                'commission_id' => $commission->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.finance.commissions.show', [
                'slug' => tenant()->subdomain,
                'commission' => $commission->id
            ])->with('error', 'Erro ao marcar comissão como paga. Tente novamente.');
        }
    }
}

