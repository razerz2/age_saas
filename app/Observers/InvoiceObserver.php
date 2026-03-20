<?php

namespace App\Observers;

use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Services\AsaasService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceObserver
{
    public function creating(Invoices $invoice): void
    {
        try {
            if ($invoice->provider !== 'asaas' || ! empty($invoice->provider_id)) {
                return;
            }

            /** @var Subscription|null $subscription */
            $subscription = $invoice->subscription()->with(['tenant', 'plan'])->first();

            if (! $subscription || ! $subscription->tenant || ! $subscription->plan) {
                Log::warning('InvoiceObserver: assinatura/tenant/plano ausentes; ignorando envio ao Asaas.', [
                    'invoice_id' => $invoice->id,
                ]);
                return;
            }

            if ($subscription->plan->isTest()) {
                Log::info('InvoiceObserver: invoice ignorada para plano de teste.', [
                    'subscription_id' => $subscription->id,
                ]);
                return;
            }

            if (
                $subscription->payment_method === 'CREDIT_CARD'
                && $subscription->auto_renew
                && ! empty($subscription->asaas_subscription_id)
            ) {
                Log::info('InvoiceObserver: assinatura automatica ja existe no Asaas; nao cria payment manual.', [
                    'subscription_id' => $subscription->id,
                    'asaas_subscription_id' => $subscription->asaas_subscription_id,
                ]);
                return;
            }

            $billingType = match ($subscription->payment_method) {
                'PIX' => 'PIX',
                'BOLETO' => 'BOLETO',
                default => 'PIX',
            };

            $asaas = new AsaasService();
            $payload = [
                'customer' => $subscription->tenant->asaas_customer_id,
                'billingType' => $billingType,
                'value' => ($invoice->amount_cents ?? $subscription->plan->price_cents) / 100,
                'dueDate' => ($invoice->due_date ?? Carbon::today()->addDays(5))->toDateString(),
                'description' => $invoice->description ?? "Assinatura do plano {$subscription->plan->name}",
                'externalReference' => $invoice->external_reference ?? (string) Str::uuid(),
            ];

            if (empty($subscription->tenant->asaas_customer_id)) {
                $existing = $asaas->searchCustomer($subscription->tenant->email);
                if (! empty($existing['data'][0]['id'] ?? null)) {
                    $subscription->tenant->update(['asaas_customer_id' => $existing['data'][0]['id']]);
                    $payload['customer'] = $existing['data'][0]['id'];
                } else {
                    $customer = $asaas->createCustomer([
                        'trade_name' => $subscription->tenant->trade_name,
                        'legal_name' => $subscription->tenant->legal_name,
                        'email' => $subscription->tenant->email,
                        'phone' => $subscription->tenant->phone,
                        'document' => $subscription->tenant->document,
                        'id' => $subscription->tenant->id,
                    ]);

                    if (! empty($customer['id'])) {
                        $subscription->tenant->update(['asaas_customer_id' => $customer['id']]);
                        $payload['customer'] = $customer['id'];
                    } else {
                        Log::error('InvoiceObserver: falha ao criar customer no Asaas.', $customer ?? []);
                        return;
                    }
                }
            }

            $payment = $asaas->createPayment($payload);

            if (! empty($payment['id'])) {
                $invoice->provider_id = $payment['id'];
                $invoice->payment_link = $payment['invoiceUrl'] ?? ($payment['bankSlipUrl'] ?? null);
                $invoice->status = $invoice->status ?? 'pending';
                $invoice->due_date = $payload['dueDate'];

                Log::info('InvoiceObserver: payment criado no Asaas.', ['payment_id' => $payment['id']]);
            } else {
                Log::error('InvoiceObserver: falha ao criar payment no Asaas.', $payment ?? []);
            }
        } catch (\Throwable $e) {
            Log::error("InvoiceObserver: erro ao enviar invoice ao Asaas: {$e->getMessage()}");
        }
    }
}
