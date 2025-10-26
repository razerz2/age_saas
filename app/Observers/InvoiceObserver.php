<?php

namespace App\Observers;

use App\Models\Platform\Invoices;
use App\Models\Platform\Subscription;
use App\Services\AsaasService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InvoiceObserver
{
    public function creating(Invoices $invoice): void
    {
        try {
            // Só processa Asaas e só se ainda não tiver sido enviado
            if ($invoice->provider !== 'asaas' || !empty($invoice->provider_id)) {
                return;
            }

            // Carrega a assinatura (pra saber o método)
            /** @var Subscription|null $subscription */
            $subscription = $invoice->subscription()->with(['tenant', 'plan'])->first();

            if (!$subscription || !$subscription->tenant || !$subscription->plan) {
                Log::warning("⚠️ InvoiceObserver: assinatura/tenant/plano ausentes; ignorando envio ao Asaas.", ['invoice_id' => $invoice->id]);
                return;
            }

            // Caso especial: assinatura controlada pelo Asaas (cartão + auto_renew + asaas_subscription_id)
            if ($subscription->payment_method === 'CREDIT_CARD'
                && $subscription->auto_renew
                && !empty($subscription->asaas_subscription_id)) {
                Log::info("ℹ️ InvoiceObserver: assinatura automática já existe no Asaas; não cria payment manual.", [
                    'subscription_id' => $subscription->id,
                    'asaas_subscription_id' => $subscription->asaas_subscription_id,
                ]);
                return;
            }

            // Define billingType a partir do método escolhido
            $billingType = match ($subscription->payment_method) {
                'PIX' => 'PIX',
                'BOLETO' => 'BOLETO',
                default => 'PIX', // fallback seguro
            };

            // Monta payload para o Asaas
            $asaas = new AsaasService();
            $payload = [
                'customer'          => $subscription->tenant->asaas_customer_id,
                'billingType'       => $billingType,
                'value'             => ($invoice->amount_cents ?? $subscription->plan->price_cents) / 100,
                'dueDate'           => ($invoice->due_date ?? Carbon::today()->addDays(5))->toDateString(),
                'description'       => $invoice->description ?? "Assinatura do plano {$subscription->plan->name}",
                'externalReference' => $invoice->external_reference ?? (string) Str::uuid(),
            ];

            // Se não houver customer no Asaas, cria/recupera antes
            if (empty($subscription->tenant->asaas_customer_id)) {
                $existing = $asaas->searchCustomer($subscription->tenant->email);
                if (!empty($existing['data'][0]['id'] ?? null)) {
                    $subscription->tenant->update(['asaas_customer_id' => $existing['data'][0]['id']]);
                } else {
                    $customer = $asaas->createCustomer([
                        'trade_name' => $subscription->tenant->trade_name,
                        'legal_name' => $subscription->tenant->legal_name,
                        'email'      => $subscription->tenant->email,
                        'phone'      => $subscription->tenant->phone,
                        'document'   => $subscription->tenant->document,
                        'id'         => $subscription->tenant->id,
                    ]);
                    if (!empty($customer['id'])) {
                        $subscription->tenant->update(['asaas_customer_id' => $customer['id']]);
                        $payload['customer'] = $customer['id'];
                    } else {
                        Log::error('❌ InvoiceObserver: falha ao criar customer no Asaas.', $customer ?? []);
                        return;
                    }
                }
            }

            // Envia para o Asaas
            $payment = $asaas->createPayment($payload);

            if (!empty($payment['id'])) {
                // Preenche campos da invoice **antes** de salvar
                $invoice->provider_id  = $payment['id'];
                $invoice->payment_link = $payment['invoiceUrl'] ?? ($payment['bankSlipUrl'] ?? null);
                $invoice->status       = $invoice->status ?? 'pending';
                $invoice->due_date     = $payload['dueDate']; // garante due_date
                // (demais campos permanecem como estão)
                Log::info("✅ InvoiceObserver: payment criado no Asaas.", ['payment_id' => $payment['id']]);
            } else {
                Log::error('❌ InvoiceObserver: falha ao criar payment no Asaas.', $payment ?? []);
            }

        } catch (\Throwable $e) {
            Log::error("💥 InvoiceObserver: erro ao enviar invoice ao Asaas: {$e->getMessage()}");
        }
    }
}
