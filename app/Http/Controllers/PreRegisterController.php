<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Platform\PreTenant;
use App\Models\Platform\PreTenantLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\Plan;
use App\Models\Platform\Subscription;
use App\Services\AsaasService;
use App\Services\Platform\PreTenantProcessorService;
use App\Services\Platform\TenantPlanService;

class PreRegisterController extends Controller
{
    private const BRAZIL_COUNTRY_ID = 31;

    public function store(Request $request)
    {
        try {
            // Validação
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'fantasy_name' => 'nullable|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    // Verifica se já existe pré-cadastro pendente ou pago com este email
                    function ($attribute, $value, $fail) {
                        $existingPreTenant = PreTenant::where('email', $value)
                            ->whereIn('status', ['pending', 'paid'])
                            ->first();
                        
                        if ($existingPreTenant) {
                            $fail('Já existe um pré-cadastro em andamento com este e-mail.');
                        }
                        
                        // Verifica se já existe tenant com este email
                        $existingTenant = Tenant::where('email', $value)->first();
                        if ($existingTenant) {
                            $fail('Já existe uma conta cadastrada com este e-mail.');
                        }
                    },
                ],
                'phone' => 'nullable|string|max:20',
                'document' => 'nullable|string|max:30',
                'plan_id' => 'required|uuid|exists:plans,id',
                'trial' => 'nullable|boolean',
                'accept_terms' => 'required|accepted',
                'subdomain_suggested' => 'nullable|string|max:100', // Mantido para compatibilidade, mas não será usado
                'address' => 'required|string|max:255',
                'address_number' => 'required|string|max:20',
                'complement' => 'nullable|string|max:100',
                'neighborhood' => 'required|string|max:100',
                'zipcode' => 'required|string|max:20',
                'state_id' => 'required|integer|exists:estados,id_estado',
                'city_id' => 'required|integer|exists:cidades,id_cidade',
            ], [
                'name.required' => 'O nome é obrigatório.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'Informe um e-mail válido.',
                'plan_id.required' => 'O plano é obrigatório.',
                'plan_id.exists' => 'O plano selecionado não existe.',
                'accept_terms.required' => 'Você deve aceitar os Termos de Uso e a Política de Privacidade para continuar.',
                'accept_terms.accepted' => 'Você deve aceitar os Termos de Uso e a Política de Privacidade para continuar.',
                'address.required' => 'O endereço é obrigatório.',
                'address_number.required' => 'O número é obrigatório.',
                'neighborhood.required' => 'O bairro é obrigatório.',
                'zipcode.required' => 'O CEP é obrigatório.',
                'state_id.required' => 'O estado é obrigatório.',
                'city_id.required' => 'A cidade é obrigatória.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Retorna erros de validação em formato JSON
            return response()->json([
                'error' => 'Erro de validação. Verifique os dados informados.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            // Erro inesperado durante validação
            Log::error('Erro durante validação do pré-cadastro', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Erro ao validar dados. Tente novamente mais tarde.',
            ], 500);
        }

        try {
            // Gerar subdomínio automaticamente a partir do nome fantasia ou nome legal
            $subdomain = $this->generateUniqueSubdomain(
                $request->fantasy_name ?? $request->name
            );

            // Buscar plano exclusivamente entre os elegíveis para comercialização pública
            $plan = Plan::publiclyAvailable()->find($request->plan_id);
            if (!$plan) {
                return response()->json([
                    'error' => 'O plano selecionado não está disponível para contratação pública.',
                ], 422);
            }

            // Validar que o plano tem preço válido
            if (empty($plan->price_cents) || $plan->price_cents <= 0) {
                Log::error('Plano sem preço válido', [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'price_cents' => $plan->price_cents,
                ]);

                return response()->json([
                    'error' => 'O plano selecionado não possui um preço válido. Entre em contato com o suporte.',
                ], 422);
            }

            if ($request->boolean('trial')) {
                return $this->handleCommercialTrial($request, $plan, $subdomain);
            }

            // Criar pré-tenant dentro de transação para garantir rollback em caso de erro
            DB::beginTransaction();
            
            try {
                $preTenant = PreTenant::create([
                    'name' => $request->name,
                    'fantasy_name' => $request->fantasy_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'document' => $request->document,
                    'plan_id' => $request->plan_id,
                    'subdomain_suggested' => $subdomain,
                    'address' => $request->address,
                    'address_number' => $request->address_number,
                    'complement' => $request->complement,
                    'neighborhood' => $request->neighborhood,
                    'zipcode' => $request->zipcode,
                    'country_id' => self::BRAZIL_COUNTRY_ID,
                    'state_id' => $request->state_id,
                    'city_id' => $request->city_id,
                    'status' => 'pending',
                    'raw_payload' => $request->all(),
                ]);
                
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }

            // Log do subdomínio gerado
            Log::info('Subdomínio gerado automaticamente', [
                'pre_tenant_id' => $preTenant->id,
                'subdomain' => $subdomain,
                'fantasy_name' => $request->fantasy_name,
                'legal_name' => $request->name,
            ]);

            // Log
            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'pre_register_created',
                'payload' => ['message' => 'Pré-cadastro criado na landing page'],
            ]);

            // Criar cliente no Asaas
            $asaas = new AsaasService();
            
            // Validar configuração do Asaas
            if (!has_asaas_credentials()) {
                Log::error('Configuração do Asaas não encontrada');
                throw new \Exception('Configuração do sistema de pagamento não encontrada. Entre em contato com o suporte.');
            }
            
            // Validar dados antes de criar cliente
            if (empty($preTenant->email)) {
                throw new \Exception('Email é obrigatório para criar cliente no Asaas');
            }

            $customerData = [
                'legal_name' => $preTenant->name,
                'trade_name' => $preTenant->fantasy_name ?? $preTenant->name,
                'email' => $preTenant->email,
                'phone' => $preTenant->phone,
                'document' => $preTenant->document,
                'id' => $preTenant->id,
            ];

            Log::info('Criando cliente no Asaas', [
                'pre_tenant_id' => $preTenant->id,
                'email' => $preTenant->email,
            ]);

            $customerResponse = $asaas->createCustomer($customerData);
            
            // Verifica se há erro na resposta
            if (isset($customerResponse['error'])) {
                $errorMessage = is_string($customerResponse['error']) 
                    ? $customerResponse['error'] 
                    : ($customerResponse['errors'][0]['description'] ?? 'Erro desconhecido ao criar cliente no Asaas');
                
                Log::error('Erro ao criar cliente no Asaas', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $customerResponse,
                    'error_message' => $errorMessage,
                ]);
                
                try {
                    PreTenantLog::create([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'asaas_customer_error',
                        'payload' => [
                            'error' => $errorMessage,
                            'response' => $customerResponse,
                        ],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Erro ao criar log de erro do cliente Asaas', [
                        'error' => $e->getMessage(),
                    ]);
                }

                // Não faz rollback aqui pois o pré-tenant já foi criado e pode ser útil para debug
                return response()->json([
                    'error' => $errorMessage,
                ], 500);
            }
            
            // Verifica se o ID do cliente foi retornado
            $customerId = $customerResponse['id'] ?? null;
            if (!$customerId) {
                // Tenta buscar em 'data' se a resposta vier paginada
                $customerId = $customerResponse['data']['id'] ?? null;
            }
            
            if (!$customerId) {
                $errorMessage = 'Resposta inválida do Asaas: ID do cliente não encontrado';
                
                Log::error('Erro ao criar cliente no Asaas - ID não encontrado', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $customerResponse,
                ]);
                
                try {
                    PreTenantLog::create([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'asaas_customer_error',
                        'payload' => [
                            'error' => $errorMessage,
                            'response' => $customerResponse,
                        ],
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Erro ao criar log de erro do cliente Asaas', [
                        'error' => $e->getMessage(),
                    ]);
                }

                return response()->json([
                    'error' => 'Erro ao processar pagamento. Tente novamente mais tarde.',
                ], 500);
            }

            // Atualizar com o ID do cliente do Asaas
            $preTenant->update(['asaas_customer_id' => $customerId]);
            
            Log::info('Cliente criado no Asaas com sucesso', [
                'pre_tenant_id' => $preTenant->id,
                'asaas_customer_id' => $customerId,
            ]);

            // Criar Payment Link no Asaas (permite múltiplas formas de pagamento: PIX, Boleto, Cartão)
            $paymentValue = $plan->price_cents / 100;
            
            if ($paymentValue <= 0) {
                throw new \Exception("Valor do pagamento inválido: {$paymentValue}");
            }

            $paymentLinkData = [
                'name' => "Pré-cadastro - {$plan->name}",
                'description' => "Pagamento do plano {$plan->name} - " . ($preTenant->fantasy_name ?? $preTenant->name),
                'customer' => $customerId,
                'value' => $paymentValue,
                'dueDateLimitDays' => 5,
                'externalReference' => $preTenant->id,
            ];

            Log::info('Criando Payment Link no Asaas', [
                'pre_tenant_id' => $preTenant->id,
                'customer_id' => $customerId,
                'value' => $paymentValue,
            ]);

            $paymentResponse = $asaas->createPaymentLink($paymentLinkData);

            // Verifica se há erro na resposta
            if (isset($paymentResponse['error'])) {
                $errorMessage = is_string($paymentResponse['error']) 
                    ? $paymentResponse['error'] 
                    : ($paymentResponse['errors'][0]['description'] ?? 'Erro desconhecido ao criar pagamento no Asaas');
                
                Log::error('Erro ao criar pagamento no Asaas', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $paymentResponse,
                    'error_message' => $errorMessage,
                ]);

                PreTenantLog::create([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'asaas_payment_error',
                    'payload' => [
                        'error' => $errorMessage,
                        'response' => $paymentResponse,
                    ],
                ]);

                return response()->json([
                    'error' => $errorMessage,
                ], 500);
            }
            
            // Verifica se o ID do payment link foi retornado
            $paymentLinkId = $paymentResponse['id'] ?? null;
            if (!$paymentLinkId) {
                $errorMessage = 'Resposta inválida do Asaas: ID do link de pagamento não encontrado';
                
                Log::error('Erro ao criar Payment Link no Asaas - ID não encontrado', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $paymentResponse,
                ]);

                PreTenantLog::create([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'asaas_payment_link_error',
                    'payload' => [
                        'error' => $errorMessage,
                        'response' => $paymentResponse,
                    ],
                ]);

                return response()->json([
                    'error' => 'Erro ao gerar link de pagamento. Tente novamente mais tarde.',
                ], 500);
            }

            // Salva o ID do payment link (não é um payment_id, é um payment_link_id)
            $preTenant->update(['asaas_payment_id' => $paymentLinkId]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'payment_link_created',
                'payload' => [
                    'payment_link_id' => $paymentLinkId,
                    'url' => $paymentResponse['url'] ?? null,
                ],
            ]);

            // Retornar link do pagamento (Payment Link tem campo 'url')
            $paymentUrl = $paymentResponse['url'] ?? null;
            
            if (!$paymentUrl) {
                Log::warning('URL do Payment Link não encontrada na resposta do Asaas', [
                    'pre_tenant_id' => $preTenant->id,
                    'response' => $paymentResponse,
                ]);
                
                PreTenantLog::create([
                    'pre_tenant_id' => $preTenant->id,
                    'event' => 'payment_link_url_missing',
                    'payload' => ['response' => $paymentResponse],
                ]);
                
                return response()->json([
                    'error' => 'Erro ao gerar link de pagamento. URL não encontrada.',
                ], 500);
            }

            // Retornar link do pagamento
            return response()->json([
                'success' => true,
                'payment_url' => $paymentUrl,
                'payment_id' => $paymentLinkId,
                'pre_tenant_id' => $preTenant->id,
            ]);

        } catch (\Illuminate\Session\TokenMismatchException $e) {
            // Erro de CSRF token
            Log::warning('Erro de CSRF token no pré-cadastro', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Sessão expirada. Por favor, recarregue a página e tente novamente.',
            ], 419);
        } catch (\Illuminate\Database\QueryException $e) {
            // Erro de banco de dados
            Log::error('Erro de banco de dados ao processar pré-cadastro', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql() ?? null,
                'bindings' => $e->getBindings() ?? null,
            ]);

            return response()->json([
                'error' => 'Erro ao salvar dados. Tente novamente mais tarde.',
            ], 500);
        } catch (\Throwable $e) {
            // Log detalhado do erro
            Log::error('Erro ao processar pré-cadastro', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', '_token']),
            ]);

            // Se já criou o pré-tenant, registra o erro nos logs do pré-tenant
            if (isset($preTenant) && $preTenant->id) {
                try {
                    PreTenantLog::create([
                        'pre_tenant_id' => $preTenant->id,
                        'event' => 'processing_error',
                        'payload' => [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'class' => get_class($e),
                        ],
                    ]);
                } catch (\Throwable $logError) {
                    Log::warning('Erro ao criar log de erro do pré-cadastro', [
                        'error' => $logError->getMessage(),
                    ]);
                }
            }

            // Retorna mensagem de erro mais específica em desenvolvimento
            $errorMessage = 'Erro ao processar pré-cadastro. Tente novamente mais tarde.';
            if (config('app.debug')) {
                $errorMessage .= ' Detalhes: ' . $e->getMessage() . ' (' . get_class($e) . ')';
            }

            return response()->json([
                'error' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Inicia trial comercial sem fluxo financeiro.
     */
    private function handleCommercialTrial(Request $request, Plan $plan, string $subdomain)
    {
        if (! $plan->hasCommercialTrial()) {
            return response()->json([
                'error' => 'Este plano nao esta disponivel para teste gratuito no momento.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $preTenant = PreTenant::create([
                'name' => $request->name,
                'fantasy_name' => $request->fantasy_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'document' => $request->document,
                'plan_id' => $plan->id,
                'subdomain_suggested' => $subdomain,
                'address' => $request->address,
                'address_number' => $request->address_number,
                'complement' => $request->complement,
                'neighborhood' => $request->neighborhood,
                'zipcode' => $request->zipcode,
                'country_id' => self::BRAZIL_COUNTRY_ID,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'status' => 'paid',
                'raw_payload' => array_merge($request->all(), ['trial' => true]),
            ]);

            PreTenantLog::create([
                'pre_tenant_id' => $preTenant->id,
                'event' => 'pre_register_trial_created',
                'payload' => [
                    'plan_id' => $plan->id,
                    'trial_days' => $plan->trial_days,
                    'message' => 'Pre-cadastro trial criado sem fluxo financeiro.',
                ],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $processor = app(PreTenantProcessorService::class);
        $tenant = $processor->createTenantFromPreTenant($preTenant);

        if (! $tenant) {
            return response()->json([
                'error' => 'Nao foi possivel provisionar o ambiente de teste no momento.',
            ], 500);
        }

        $hasActiveTrial = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('is_trial', true)
            ->whereIn('status', ['active', 'trialing'])
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->exists();

        if ($hasActiveTrial) {
            return response()->json([
                'error' => 'Ja existe um trial ativo para esta tenant.',
            ], 422);
        }

        $trialStartsAt = now();
        $trialEndsAt = $trialStartsAt->copy()->addDays((int) $plan->trial_days);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'starts_at' => $trialStartsAt,
            'ends_at' => $trialEndsAt,
            // Campos financeiros mantidos apenas por compatibilidade de schema legado.
            'due_day' => max(1, min(31, (int) $trialStartsAt->day)),
            'status' => 'trialing',
            'auto_renew' => false,
            'payment_method' => 'PIX',
            'is_trial' => true,
            'trial_ends_at' => $trialEndsAt,
            'asaas_synced' => false,
            'asaas_sync_status' => 'skipped',
            'asaas_last_error' => null,
            'asaas_last_sync_at' => now(),
        ]);

        PreTenantLog::create([
            'pre_tenant_id' => $preTenant->id,
            'event' => 'trial_subscription_created',
            'payload' => [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'trial_ends_at' => $trialEndsAt->toDateTimeString(),
            ],
        ]);

        app(TenantPlanService::class)->applyPlanRules($tenant, $plan);

        try {
            $processor->sendWelcomeEmail($tenant, $preTenant);
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar email de boas-vindas no trial comercial', [
                'tenant_id' => $tenant->id,
                'pre_tenant_id' => $preTenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'trial' => true,
            'message' => "Teste gratis iniciado por {$plan->trial_days} dias. Sem cartao de credito.",
            'redirect_url' => route('tenant.login', ['slug' => $tenant->subdomain]),
            'tenant_id' => $tenant->id,
            'trial_ends_at' => $trialEndsAt->toDateTimeString(),
        ]);
    }
    /**
     * Gera um subdominio unico baseado no nome fornecido.
     */
    private function generateUniqueSubdomain(string $name): string
    {
        // Remove caracteres especiais e converte para minúsculas
        $name = trim($name);
        
        // Divide o nome em palavras
        $words = preg_split('/\s+/', $name);
        $words = array_filter($words, function($word) {
            return !empty(trim($word));
        });
        $words = array_values($words);
        
        // Se não houver palavras suficientes, usa o nome completo
        if (count($words) < 2) {
            $baseSubdomain = Str::slug($name);
            return $this->ensureUniqueSubdomain($baseSubdomain);
        }
        
        // Pega as duas primeiras palavras
        $word1 = Str::slug($words[0]);
        $word2 = Str::slug($words[1]);
        
        // Tenta combinação padrão: palavra1-palavra2
        $subdomain = "{$word1}-{$word2}";
        if (!$this->subdomainExists($subdomain)) {
            return $subdomain;
        }
        
        // Tenta ordem invertida: palavra2-palavra1
        $subdomain = "{$word2}-{$word1}";
        if (!$this->subdomainExists($subdomain)) {
            return $subdomain;
        }
        
        // Se houver terceira palavra, tenta adicionar
        if (count($words) >= 3) {
            $word3 = Str::slug($words[2]);
            
            // palavra1-palavra3
            $subdomain = "{$word1}-{$word3}";
            if (!$this->subdomainExists($subdomain)) {
                return $subdomain;
            }
            
            // palavra2-palavra3
            $subdomain = "{$word2}-{$word3}";
            if (!$this->subdomainExists($subdomain)) {
                return $subdomain;
            }
            
            // palavra3-palavra1
            $subdomain = "{$word3}-{$word1}";
            if (!$this->subdomainExists($subdomain)) {
                return $subdomain;
            }
            
            // Todas as três palavras
            $subdomain = "{$word1}-{$word2}-{$word3}";
            if (!$this->subdomainExists($subdomain)) {
                return $subdomain;
            }
        }
        
        // Se ainda não encontrou, adiciona número sequencial
        return $this->ensureUniqueSubdomain("{$word1}-{$word2}");
    }

    /**
     * Verifica se um subdomínio já existe (em tenants ou pre-tenants)
     * 
     * @param string $subdomain
     * @return bool
     */
    private function subdomainExists(string $subdomain): bool
    {
        // Verifica em tenants ativos
        $existsInTenants = Tenant::where('subdomain', $subdomain)->exists();
        
        // Verifica em pre-tenants pendentes
        $existsInPreTenants = PreTenant::where('subdomain_suggested', $subdomain)
            ->where('status', 'pending')
            ->exists();
        
        return $existsInTenants || $existsInPreTenants;
    }

    /**
     * Garante que o subdomínio seja único, adicionando número sequencial se necessário
     * 
     * @param string $baseSubdomain
     * @return string
     */
    private function ensureUniqueSubdomain(string $baseSubdomain): string
    {
        // Limita o tamanho do subdomínio base (máximo 40 caracteres para deixar espaço para números)
        $baseSubdomain = substr($baseSubdomain, 0, 40);
        
        // Remove caracteres inválidos
        $baseSubdomain = Str::slug($baseSubdomain);
        
        // Se o subdomínio base já é único, retorna
        if (!$this->subdomainExists($baseSubdomain)) {
            return $baseSubdomain;
        }
        
        // Tenta adicionar números sequenciais (até 999)
        for ($i = 1; $i <= 999; $i++) {
            $subdomain = "{$baseSubdomain}-{$i}";
            if (!$this->subdomainExists($subdomain)) {
                return $subdomain;
            }
        }
        
        // Se ainda não encontrou, adiciona timestamp (último recurso)
        $subdomain = "{$baseSubdomain}-" . time();
        return $subdomain;
    }
}
