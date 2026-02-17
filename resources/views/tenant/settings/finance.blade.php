@extends('layouts.tailadmin.app')

@section('title', 'Configurações Financeiras')
@section('page', 'settings')

@section('content')
<div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="page-title mb-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-currency-usd"></i>
                </span>
                Configurações Financeiras
            </h3>
            <div class="flex items-center gap-3">
                <x-tailadmin-button variant="secondary" size="md" href="{{ workspace_route('tenant.settings.index') }}"
                    class="bg-transparent border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                    <i class="mdi mdi-arrow-left"></i>
                    Voltar para Configurações
                </x-tailadmin-button>
                <x-help-button module="settings" />
            </div>
        </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ workspace_route('tenant.settings.finance.update') }}">
                    @csrf

                    {{-- Status do Módulo --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="mdi mdi-power me-2"></i>Status do Módulo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="finance_enabled"
                                       name="finance_enabled"
                                       value="1"
                                       {{ $settings['finance.enabled'] === 'true' ? 'checked' : '' }}>
                                <label class="form-check-label" for="finance_enabled">
                                    <strong class="d-block mb-1">Habilitar Módulo Financeiro</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Quando habilitado, o módulo financeiro permite gerenciar contas, transações, cobranças e comissões.
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Integração Asaas --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-link-variant me-2"></i>Integração com Asaas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Provedor de Pagamento</label>
                                    <select class="form-select" name="payment_provider" required>
                                        <option value="asaas" {{ $settings['finance.payment_provider'] === 'asaas' ? 'selected' : '' }}>Asaas</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ambiente</label>
                                    <select class="form-select" name="asaas_environment" required>
                                        <option value="sandbox" {{ $settings['finance.asaas.environment'] === 'sandbox' ? 'selected' : '' }}>Sandbox (Teste)</option>
                                        <option value="production" {{ $settings['finance.asaas.environment'] === 'production' ? 'selected' : '' }}>Produção</option>
                                    </select>
                                    <small class="text-muted">Use Sandbox para testes e Produção para ambiente real</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">API Key <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="asaas_api_key" 
                                           value="{{ $settings['finance.asaas.api_key'] }}" 
                                           placeholder="Sua API Key do Asaas" required>
                                    <small class="text-muted">Obtenha sua API Key no painel do Asaas</small>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Webhook Secret</label>
                                    <div class="flex gap-2">
                                        <input type="text" class="form-control flex-1" name="asaas_webhook_secret" 
                                               value="{{ $settings['finance.asaas.webhook_secret'] }}" 
                                               placeholder="Secret para validar webhooks"
                                               readonly>
                                        <x-tailadmin-button type="button" variant="secondary" size="sm" class="px-3 py-2" data-settings-action="regenerate-secret">
                                            <i class="mdi mdi-refresh"></i> Gerar Novo
                                        </x-tailadmin-button>
                                    </div>
                                    <input type="hidden" name="regenerate_webhook_secret" id="regenerate_webhook_secret" value="0">
                                    <small class="text-muted">Configure este secret no painel do Asaas na URL do webhook: 
                                        <code>{{ url('/t/' . tenant()->subdomain . '/webhooks/asaas') }}</code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modo de Cobrança --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-credit-card me-2"></i>Modo de Cobrança
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Modo de Cobrança <span class="text-danger">*</span></label>
                                <select class="form-select" name="billing_mode" id="billing_mode" required>
                                    <option value="disabled" {{ $settings['finance.billing_mode'] === 'disabled' ? 'selected' : '' }}>Desabilitado</option>
                                    <option value="global" {{ $settings['finance.billing_mode'] === 'global' ? 'selected' : '' }}>Global (Valor Único para Todos)</option>
                                    <option value="per_doctor" {{ $settings['finance.billing_mode'] === 'per_doctor' ? 'selected' : '' }}>Por Médico</option>
                                    <option value="per_doctor_specialty" {{ $settings['finance.billing_mode'] === 'per_doctor_specialty' ? 'selected' : '' }}>Por Médico e Especialidade</option>
                                </select>
                                <small class="text-muted">
                                    <strong>Desabilitado:</strong> Não cria cobranças automaticamente<br>
                                    <strong>Global:</strong> Usa valores únicos para todos os agendamentos (modos "Apenas Reserva" ou "Valor Completo")<br>
                                    <strong>Por Médico:</strong> Define valores específicos para cada médico<br>
                                    <strong>Por Médico e Especialidade:</strong> Define valores específicos para cada médico e sua especialidade
                                </small>
                            </div>

                            {{-- Valores Globais --}}
                            <div id="billing_amounts_global" style="display: {{ in_array($settings['finance.billing_mode'], ['global']) ? 'block' : 'none' }};">
                                <div class="alert alert-info mb-3">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    Configure os valores globais que serão usados para todos os agendamentos.
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Modo de Cobrança Global</label>
                                        <select class="form-select" name="global_billing_type">
                                            <option value="reservation" {{ $settings['finance.global_billing_type'] === 'reservation' ? 'selected' : '' }}>Apenas Reserva</option>
                                            <option value="full" {{ $settings['finance.global_billing_type'] === 'full' ? 'selected' : '' }}>Valor Completo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor da Reserva (R$)</label>
                                        <input type="number" step="0.01" class="form-control" name="reservation_amount" 
                                               value="{{ $settings['finance.reservation_amount'] }}" 
                                               placeholder="0.00" min="0">
                                        <small class="text-muted">Valor cobrado quando modo é "Apenas Reserva"</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Valor Completo (R$)</label>
                                        <input type="number" step="0.01" class="form-control" name="full_appointment_amount" 
                                               value="{{ $settings['finance.full_appointment_amount'] }}" 
                                               placeholder="0.00" min="0">
                                        <small class="text-muted">Valor cobrado quando modo é "Valor Completo"</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Preços por Médico/Especialidade --}}
                            <div id="billing_prices_management" style="display: {{ in_array($settings['finance.billing_mode'], ['per_doctor', 'per_doctor_specialty']) ? 'block' : 'none' }};">
                                <div class="alert alert-info mb-3">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    Configure os valores de cobrança para cada médico{{ $settings['finance.billing_mode'] === 'per_doctor_specialty' ? ' e sua especialidade' : '' }}.
                                    Os valores podem ser definidos como "Apenas Reserva" ou "Valor Completo".
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Médico</th>
                                                @if($settings['finance.billing_mode'] === 'per_doctor_specialty')
                                                    <th>Especialidade</th>
                                                @endif
                                                <th>Tipo de Cobrança</th>
                                                <th>Valor Reserva (R$)</th>
                                                <th>Valor Completo (R$)</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="billing_prices_table_body">
                                            @php
                                                try {
                                                    $doctors = \App\Models\Tenant\Doctor::with('user', 'specialties')->get();
                                                    $existingPrices = \App\Models\Tenant\DoctorBillingPrice::where('active', true)->get()->keyBy(function($item) {
                                                        return $item->doctor_id . '_' . ($item->specialty_id ?? 'null');
                                                    });
                                                } catch (\Exception $e) {
                                                    $doctors = collect();
                                                    $existingPrices = collect();
                                                }
                                            @endphp
                                            
                                            @foreach($doctors as $doctor)
                                                @if($settings['finance.billing_mode'] === 'per_doctor')
                                                    @php
                                                        $priceKey = $doctor->id . '_null';
                                                        $price = $existingPrices->get($priceKey);
                                                    @endphp
                                                    <tr data-doctor-id="{{ $doctor->id }}" data-specialty-id="">
                                                        <td>{{ $doctor->user->name ?? 'N/A' }}</td>
                                                        <td>
                                                            <select class="form-select form-select-sm billing-type" name="billing_prices[{{ $doctor->id }}][type]">
                                                                @php
                                                                    // Determina o tipo baseado nos valores existentes
                                                                    $selectedType = 'reservation';
                                                                    if ($price) {
                                                                        if ($price->full_appointment_amount > 0 && $price->reservation_amount == 0) {
                                                                            $selectedType = 'full';
                                                                        } else {
                                                                            $selectedType = 'reservation';
                                                                        }
                                                                    }
                                                                @endphp
                                                                <option value="reservation" {{ $selectedType === 'reservation' ? 'selected' : '' }}>Apenas Reserva</option>
                                                                <option value="full" {{ $selectedType === 'full' ? 'selected' : '' }}>Valor Completo</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm reservation-amount" 
                                                                   name="billing_prices[{{ $doctor->id }}][reservation_amount]" 
                                                                   value="{{ $price && $selectedType === 'reservation' ? number_format($price->reservation_amount, 2, '.', '') : '0.00' }}" 
                                                                   placeholder="0.00" min="0"
                                                                   {{ $selectedType === 'full' ? 'disabled' : '' }}>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" class="form-control form-control-sm full-amount" 
                                                                   name="billing_prices[{{ $doctor->id }}][full_appointment_amount]" 
                                                                   value="{{ $price && $selectedType === 'full' ? number_format($price->full_appointment_amount, 2, '.', '') : '0.00' }}" 
                                                                   placeholder="0.00" min="0"
                                                                   {{ $selectedType === 'reservation' ? 'disabled' : '' }}>
                                                        </td>
                                                        <td>
                                                            @if($price)
                                                                <x-tailadmin-button type="button" variant="danger" size="xs" class="remove-price px-2 py-1" data-price-id="{{ $price->id }}">
                                                                    <i class="mdi mdi-delete"></i>
                                                                </x-tailadmin-button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach($doctor->specialties as $specialty)
                                                        @php
                                                            $priceKey = $doctor->id . '_' . $specialty->id;
                                                            $price = $existingPrices->get($priceKey);
                                                        @endphp
                                                        <tr data-doctor-id="{{ $doctor->id }}" data-specialty-id="{{ $specialty->id }}">
                                                            <td>{{ $doctor->user->name ?? 'N/A' }}</td>
                                                            <td>{{ $specialty->name }}</td>
                                                            <td>
                                                                <select class="form-select form-select-sm billing-type" name="billing_prices[{{ $doctor->id }}][{{ $specialty->id }}][type]">
                                                                    @php
                                                                        // Determina o tipo baseado nos valores existentes
                                                                        $selectedType = 'reservation';
                                                                        if ($price) {
                                                                            if ($price->full_appointment_amount > 0 && $price->reservation_amount == 0) {
                                                                                $selectedType = 'full';
                                                                            } else {
                                                                                $selectedType = 'reservation';
                                                                            }
                                                                        }
                                                                    @endphp
                                                                    <option value="reservation" {{ $selectedType === 'reservation' ? 'selected' : '' }}>Apenas Reserva</option>
                                                                    <option value="full" {{ $selectedType === 'full' ? 'selected' : '' }}>Valor Completo</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" class="form-control form-control-sm reservation-amount" 
                                                                       name="billing_prices[{{ $doctor->id }}][{{ $specialty->id }}][reservation_amount]" 
                                                                       value="{{ $price && $selectedType === 'reservation' ? number_format($price->reservation_amount, 2, '.', '') : '0.00' }}" 
                                                                       placeholder="0.00" min="0"
                                                                       {{ $selectedType === 'full' ? 'disabled' : '' }}>
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.01" class="form-control form-control-sm full-amount" 
                                                                       name="billing_prices[{{ $doctor->id }}][{{ $specialty->id }}][full_appointment_amount]" 
                                                                       value="{{ $price && $selectedType === 'full' ? number_format($price->full_appointment_amount, 2, '.', '') : '0.00' }}" 
                                                                       placeholder="0.00" min="0"
                                                                       {{ $selectedType === 'reservation' ? 'disabled' : '' }}>
                                                            </td>
                                                            <td>
                                                                @if($price)
                                                                    <x-tailadmin-button type="button" variant="danger" size="xs" class="remove-price px-2 py-1" data-price-id="{{ $price->id }}">
                                                                        <i class="mdi mdi-delete"></i>
                                                                    </x-tailadmin-button>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                            
                                            @if($doctors->isEmpty())
                                                <tr>
                                                    <td colspan="{{ $settings['finance.billing_mode'] === 'per_doctor_specialty' ? '6' : '5' }}" class="text-center text-muted">
                                                        Nenhum médico cadastrado. Cadastre médicos primeiro para configurar preços.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                
                                <input type="hidden" name="removed_prices" id="removed_prices" value="">
                            </div>
                        </div>
                    </div>

                    {{-- Origem de Cobrança --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-source-branch me-2"></i>Origem de Cobrança
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Selecione em quais origens de agendamento a cobrança será aplicada:</p>

                            <div class="form-check form-switch mb-3 p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="charge_on_public_appointment"
                                       name="charge_on_public_appointment"
                                       value="1"
                                       {{ $settings['finance.charge_on_public_appointment'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="charge_on_public_appointment">
                                    <strong class="d-block mb-1">Cobrar em Agendamentos Públicos</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Cria cobrança quando paciente agenda pela área pública do site.
                                    </p>
                                </label>
                            </div>

                            <div class="form-check form-switch mb-3 p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="charge_on_patient_portal"
                                       name="charge_on_patient_portal"
                                       value="1"
                                       {{ $settings['finance.charge_on_patient_portal'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="charge_on_patient_portal">
                                    <strong class="d-block mb-1">Cobrar no Portal do Paciente</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Cria cobrança quando paciente agenda pelo portal do paciente.
                                    </p>
                                </label>
                            </div>

                            <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="charge_on_internal_appointment"
                                       name="charge_on_internal_appointment"
                                       value="1"
                                       {{ $settings['finance.charge_on_internal_appointment'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="charge_on_internal_appointment">
                                    <strong class="d-block mb-1">Cobrar em Agendamentos Internos</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Cria cobrança quando agendamento é criado pela área administrativa.
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Métodos de Pagamento --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-payment me-2"></i>Métodos de Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Selecione os métodos de pagamento disponíveis:</p>

                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="payment_methods[]"
                                               value="pix"
                                               id="payment_method_pix"
                                               {{ in_array('pix', $settings['finance.payment_methods']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_method_pix">
                                            <i class="mdi mdi-qrcode me-2"></i>PIX
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="payment_methods[]"
                                               value="credit_card"
                                               id="payment_method_credit_card"
                                               {{ in_array('credit_card', $settings['finance.payment_methods']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_method_credit_card">
                                            <i class="mdi mdi-credit-card me-2"></i>Cartão de Crédito
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="payment_methods[]"
                                               value="boleto"
                                               id="payment_method_boleto"
                                               {{ in_array('boleto', $settings['finance.payment_methods']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="payment_method_boleto">
                                            <i class="mdi mdi-file-document me-2"></i>Boleto
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notificações --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-bell-outline me-2"></i>Notificações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="auto_send_payment_link"
                                       name="auto_send_payment_link"
                                       value="1"
                                       {{ $settings['finance.auto_send_payment_link'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_send_payment_link">
                                    <strong class="d-block mb-1">Enviar Link de Pagamento Automaticamente</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Quando habilitado, o link de pagamento é enviado automaticamente por email/WhatsApp ao criar a cobrança.
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Conta Padrão --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-bank me-2"></i>Conta Padrão
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Conta Financeira Padrão</label>
                                <select class="form-select" name="default_account_id">
                                    <option value="">Selecione uma conta</option>
                                    @php
                                        try {
                                            $accounts = \App\Models\Tenant\FinancialAccount::where('active', true)->orderBy('name')->get();
                                        } catch (\Exception $e) {
                                            $accounts = collect();
                                        }
                                    @endphp
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" 
                                                {{ $settings['finance.default_account_id'] == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ ucfirst($account->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Conta usada por padrão ao criar transações financeiras</small>
                                @if($accounts->isEmpty())
                                    @php
                                        $tableExists = false;
                                        try {
                                            $tableExists = \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('financial_accounts');
                                        } catch (\Exception $e) {
                                            $tableExists = false;
                                        }
                                    @endphp
                                    @if($tableExists)
                                        <small class="text-muted d-block mt-1">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            Nenhuma conta financeira cadastrada. 
                                            <a href="{{ workspace_route('tenant.finance.accounts.create') }}">Criar primeira conta</a>
                                        </small>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Comissões Médicas --}}
                    <div class="card border shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="mdi mdi-account-cash me-2"></i>Comissões Médicas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-3 p-3 rounded bg-light" style="border: 1px solid #e9ecef;">
                                <input class="form-check-input" type="checkbox" 
                                       id="doctor_commission_enabled"
                                       name="doctor_commission_enabled"
                                       value="1"
                                       {{ $settings['finance.doctor_commission_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="doctor_commission_enabled">
                                    <strong class="d-block mb-1">Habilitar Comissões Médicas</strong>
                                    <p class="text-muted mb-0" style="font-size: 0.875rem;">
                                        Quando habilitado, comissões são calculadas automaticamente ao confirmar pagamentos.
                                    </p>
                                </label>
                            </div>

                            <div id="commission_percentage_group" style="display: {{ $settings['finance.doctor_commission_enabled'] ? 'block' : 'none' }};">
                                <div class="mb-3">
                                    <label class="form-label">Percentual de Comissão Padrão (%)</label>
                                    <input type="number" step="0.01" class="form-control" name="default_commission_percentage" 
                                           value="{{ $settings['finance.default_commission_percentage'] }}" 
                                           placeholder="0.00" min="0" max="100">
                                    <small class="text-muted">Percentual padrão usado para calcular comissões (0 a 100%)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 pt-3 border-t border-gray-200 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ workspace_route('tenant.settings.index') }}" class="btn-patient-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Cancelar
                        </a>
                        <button type="submit" class="btn-patient-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                            </svg>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

