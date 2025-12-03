<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Services\AsaasService;

class PreRegisterController extends Controller
{
    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fantasy_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'document' => 'nullable|string|max:30',
            'plan_id' => 'required|uuid|exists:plans,id',
            'subdomain_suggested' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'zipcode' => 'nullable|string|max:20',
            'country_id' => 'nullable|integer|exists:paises,id_pais',
            'state_id' => 'nullable|integer|exists:estados,id_estado',
            'city_id' => 'nullable|integer|exists:cidades,id_cidade',
        ]);

        try {
            // Validar se subdomain está disponível
            $subdomain = $request->subdomain_suggested;
            if ($subdomain) {
                $subdomain = Str::slug($subdomain);
                $exists = Tenant::where('subdomain', $subdomain)->exists();
                if ($exists) {
                    return response()->json([
                        'error' => 'O subdomínio informado já está em uso. Por favor, escolha outro.',
                    ], 422);
                }
            }

            // Buscar plano
            $plan = Plan::findOrFail($request->plan_id);
            if (!$plan->is_active) {
                return response()->json([
                    'error' => 'O plano selecionado não está disponível.',
                ], 422);
            }

            // Criar pré-tenant
            $preTenant = PreTenant::create([
                'name' => $request->name,
                'fantasy_name' => $request->fantasy_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'document' => $request->document,
                'plan_id' => $request->plan_id,
                'subdomain_suggested' => $subdomain,
                'address' => $request->address,
                'zipcode' => $request->zipcode,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'status' => 'pending',
                'raw_payload' => $request->all(),
            ]);

            // Log
            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'pre_register_created',
                'payload' => ['message' => 'Pré-cadastro criado na landing page'],
            ]);

            // Criar cliente no Asaas
            $asaas = new AsaasService();
            $customerData = [
                'legal_name' => $preTenant->name,
                'trade_name' => $preTenant->fantasy_name ?? $preTenant->name,
                'email' => $preTenant->email,
                'phone' => $preTenant->phone,
                'document' => $preTenant->document,
                'id' => $preTenant->id,
            ];

            $customerResponse = $asaas->createCustomer($customerData);
            
            if (isset($customerResponse['error']) || !isset($customerResponse['id'])) {
                Log::error('Erro ao criar cliente no Asaas', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $customerResponse,
                ]);
                
                PreTenantLog::create([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'asaas_customer_error',
                    'payload' => ['error' => $customerResponse['error'] ?? 'Resposta inválida do Asaas'],
                ]);

                return response()->json([
                    'error' => 'Erro ao processar pagamento. Tente novamente mais tarde.',
                ], 500);
            }

            $preTenant->update(['asaas_customer_id' => $customerResponse['id']]);

            // Criar cobrança no Asaas
            $paymentData = [
                'customer' => $customerResponse['id'],
                'billingType' => 'PIX',
                'dueDate' => now()->addDays(5)->toDateString(),
                'value' => $plan->price_cents / 100,
                'description' => "Pré-cadastro - Plano {$plan->name}",
                'externalReference' => $preTenant->id,
            ];

            $paymentResponse = $asaas->createPayment($paymentData);

            if (isset($paymentResponse['error']) || !isset($paymentResponse['id'])) {
                Log::error('Erro ao criar pagamento no Asaas', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $paymentResponse,
                ]);

                PreTenantLog::create([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'asaas_payment_error',
                    'payload' => ['error' => $paymentResponse['error'] ?? 'Resposta inválida do Asaas'],
                ]);

                return response()->json([
                    'error' => 'Erro ao gerar link de pagamento. Tente novamente mais tarde.',
                ], 500);
            }

            $preTenant->update(['asaas_payment_id' => $paymentResponse['id']]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'payment_created',
                'payload' => [
                    'payment_id' => $paymentResponse['id'],
                    'invoice_url' => $paymentResponse['invoiceUrl'] ?? null,
                ],
            ]);

            // Retornar link do pagamento
            return response()->json([
                'success' => true,
                'payment_url' => $paymentResponse['invoiceUrl'] ?? null,
                'payment_id' => $paymentResponse['id'],
                'pre_tenant_id' => $preTenant->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar pré-cadastro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao processar pré-cadastro. Tente novamente mais tarde.',
            ], 500);
        }
    }
}
