<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FinancialCharge;
use App\Models\Platform\Tenant;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Exibe página de pagamento pública
     * GET /t/{tenant}/pagamento/{charge}
     */
    public function show(string $slug, FinancialCharge $charge)
    {
        // Verificar se o módulo está habilitado
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(404);
        }

        // Verificar se a cobrança pertence ao tenant atual
        $tenant = Tenant::where('subdomain', $slug)->first();
        if (!$tenant) {
            abort(404);
        }

        // Verificar se a cobrança existe e está pendente
        if ($charge->status !== 'pending') {
            if ($charge->status === 'paid') {
                return redirect()->route('tenant.payment.success', [
                    'slug' => $slug,
                    'charge' => $charge->id
                ]);
            } elseif ($charge->status === 'expired' || $charge->isOverdue()) {
                return redirect()->route('tenant.payment.error', [
                    'slug' => $slug,
                    'charge' => $charge->id
                ])->with('error', 'Esta cobrança está expirada.');
            } else {
                return redirect()->route('tenant.payment.error', [
                    'slug' => $slug,
                    'charge' => $charge->id
                ])->with('error', 'Esta cobrança não está mais disponível para pagamento.');
            }
        }

        // Gerar link de pagamento se não existir
        if (!$charge->payment_link && $charge->asaas_charge_id) {
            $billingService = app(BillingService::class);
            $paymentLink = $billingService->generatePaymentLink($charge);
            if (!$paymentLink) {
                Log::error('Não foi possível gerar link de pagamento', [
                    'charge_id' => $charge->id,
                ]);
                return redirect()->route('tenant.payment.error', [
                    'slug' => $slug,
                    'charge' => $charge->id
                ])->with('error', 'Erro ao gerar link de pagamento. Por favor, entre em contato com a clínica.');
            }
            $charge->refresh();
        }

        // Carregar relacionamentos
        $charge->load(['patient', 'appointment.doctor.user', 'appointment.calendar']);

        return view('tenant.payment.show', compact('charge', 'tenant'));
    }

    /**
     * Página de sucesso após pagamento
     * GET /t/{tenant}/pagamento/{charge}/sucesso
     */
    public function success(string $slug, FinancialCharge $charge)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(404);
        }

        $tenant = Tenant::where('subdomain', $slug)->first();
        if (!$tenant) {
            abort(404);
        }

        $charge->load(['patient', 'appointment.doctor.user']);

        return view('tenant.payment.success', compact('charge', 'tenant'));
    }

    /**
     * Página de erro/expirado
     * GET /t/{tenant}/pagamento/{charge}/erro
     */
    public function error(string $slug, FinancialCharge $charge)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(404);
        }

        $tenant = Tenant::where('subdomain', $slug)->first();
        if (!$tenant) {
            abort(404);
        }

        $charge->load(['patient', 'appointment.doctor.user']);

        return view('tenant.payment.error', compact('charge', 'tenant'));
    }
}

