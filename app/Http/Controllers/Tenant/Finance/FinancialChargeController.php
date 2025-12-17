<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialCharge;
use App\Services\Billing\BillingService;
use App\Services\TenantNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FinancialChargeController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Lista todas as cobranças
     */
    public function index(Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $query = FinancialCharge::with(['patient', 'appointment.doctor.user']);

        // Filtrar por médico via appointment
        $user = Auth::guard('tenant')->user();
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->whereHas('appointment', function($q) use ($doctor) {
                    $q->where('doctor_id', $doctor->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!empty($allowedDoctorIds)) {
                $query->whereHas('appointment', function($q) use ($allowedDoctorIds) {
                    $q->whereIn('doctor_id', $allowedDoctorIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filtros opcionais
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('origin')) {
            $query->where('origin', $request->origin);
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $charges = $query->orderBy('due_date', 'desc')->orderBy('created_at', 'desc')->paginate(30);

        return view('tenant.finance.charges.index', compact('charges'));
    }

    /**
     * Exibe detalhes da cobrança
     */
    public function show(FinancialCharge $charge)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Verificar acesso por role
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Tenant\Doctor::where('user_id', $user->id)->first();
            if (!$doctor || !$charge->appointment || $charge->appointment->doctor_id !== $doctor->id) {
                abort(403, 'Acesso negado.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!$charge->appointment || !in_array($charge->appointment->doctor_id, $allowedDoctorIds)) {
                abort(403, 'Acesso negado.');
            }
        }

        $charge->load(['patient', 'appointment.doctor.user', 'appointment.calendar', 'transaction']);

        return view('tenant.finance.charges.show', compact('charge'));
    }

    /**
     * Cancela uma cobrança
     */
    public function cancel(FinancialCharge $charge)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Apenas admin pode cancelar
        if ($user->role !== 'admin') {
            abort(403, 'Apenas administradores podem cancelar cobranças.');
        }

        if ($charge->status === 'paid') {
            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('error', 'Não é possível cancelar uma cobrança já paga.');
        }

        try {
            // Cancelar via BillingService
            app(\App\Services\Billing\BillingService::class)->cancelCharge($charge);

            Log::info('Cobrança cancelada', [
                'charge_id' => $charge->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('success', 'Cobrança cancelada com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao cancelar cobrança', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('error', 'Erro ao cancelar cobrança. Tente novamente.');
        }
    }

    /**
     * Reenvia link de pagamento
     */
    public function resendLink(FinancialCharge $charge)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Admin e user autorizado podem reenviar
        if ($user->role === 'doctor') {
            abort(403, 'Acesso negado.');
        }

        if ($charge->status !== 'pending') {
            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('error', 'Apenas cobranças pendentes podem ter o link reenviado.');
        }

        // Gerar link se não existir
        if (!$charge->payment_link && $charge->asaas_charge_id) {
            $billingService = app(BillingService::class);
            $paymentLink = $billingService->generatePaymentLink($charge);
            if ($paymentLink) {
                $charge->refresh();
            }
        }

        if (!$charge->payment_link) {
            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('error', 'Não foi possível gerar o link de pagamento.');
        }

        // Enviar link
        try {
            TenantNotificationService::sendPaymentLink($charge);

            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('success', 'Link de pagamento reenviado com sucesso.');
        } catch (\Throwable $e) {
            Log::error('Erro ao reenviar link de pagamento', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('tenant.finance.charges.show', [
                'slug' => tenant()->subdomain,
                'charge' => $charge->id
            ])->with('error', 'Erro ao reenviar link. Tente novamente.');
        }
    }
}

