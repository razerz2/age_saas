# 📊 Documentação do Módulo Financeiro de Tenant

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Modelos de Dados](#modelos-de-dados)
4. [Controllers](#controllers)
5. [Services](#services)
6. [Observers](#observers)
7. [Rotas](#rotas)
8. [Integração com Asaas](#integração-com-asaas)
9. [Fluxos de Trabalho](#fluxos-de-trabalho)
10. [Configurações](#configurações)
11. [Relatórios](#relatórios)
12. [Segurança e Permissões](#segurança-e-permissões)

---

## 🎯 Visão Geral

O módulo financeiro de tenant é um sistema completo de gestão financeira que permite aos tenants (clínicas/consultórios) gerenciar:

- **Contas Financeiras**: Controle de múltiplas contas (caixa, banco, PIX, crédito)
- **Categorias**: Organização de receitas e despesas por categorias
- **Transações**: Registro manual ou automático de receitas e despesas
- **Cobranças**: Geração automática de cobranças vinculadas a agendamentos (opcional)
- **Comissões**: Cálculo e gestão de comissões de médicos
- **Relatórios**: Análises financeiras detalhadas

### Características Principais

- ✅ **Funciona 100% independente** - Não requer gateway de pagamento
- ✅ **Billing opcional** - Gateway de pagamento é plugável
- ✅ **Arquitetura escalável** - Suporte a múltiplos gateways (Asaas, Stripe, etc.)
- ✅ Cobrança automática vinculada a agendamentos (quando billing habilitado)
- ✅ Suporte a múltiplos métodos de pagamento (PIX, Boleto, Cartão)
- ✅ Controle de acesso baseado em roles (admin, user, doctor)
- ✅ Relatórios financeiros exportáveis
- ✅ Sistema de comissões para médicos
- ✅ Transações imutáveis quando pagas

---

## 🏗️ Arquitetura

### Princípios Arquiteturais

**REGRA FUNDAMENTAL:** O módulo financeiro **NÃO depende** do Asaas ou qualquer gateway de pagamento.

O sistema está dividido em duas camadas distintas:

1. **Finance (Core)** → Registros financeiros internos (sempre disponível)
2. **Billing (Opcional)** → Cobrança externa via gateway (plugável)

### Estrutura de Diretórios

```
app/
├── Http/
│   └── Controllers/
│       └── Tenant/
│           └── Finance/
│               ├── FinancialAccountController.php
│               ├── FinancialCategoryController.php
│               ├── FinancialChargeController.php
│               ├── FinancialTransactionController.php
│               ├── DoctorCommissionController.php
│               └── Reports/
│                   ├── FinanceReportController.php
│                   ├── CashFlowReportController.php
│                   ├── IncomeExpenseReportController.php
│                   ├── ChargesReportController.php
│                   ├── PaymentsReportController.php
│                   └── CommissionsReportController.php
├── Models/
│   └── Tenant/
│       ├── FinancialAccount.php
│       ├── FinancialCategory.php
│       ├── FinancialCharge.php
│       ├── FinancialTransaction.php
│       └── DoctorBillingPrice.php
├── Services/
│   ├── Finance/
│   │   └── FinanceRecorderService.php  # Core - sem dependências externas
│   └── Billing/
│       ├── BillingService.php          # Orquestrador
│       ├── BillingProviderInterface.php # Interface para providers
│       └── Providers/
│           └── AsaasBillingProvider.php # Provider Asaas
├── Events/
│   └── Finance/
│       ├── PaymentConfirmed.php
│       ├── ChargeCreated.php
│       └── ChargeCancelled.php
├── Listeners/
│   └── Finance/
│       └── CreateTransactionOnPaymentConfirmed.php
└── Observers/
    └── Finance/
        └── AppointmentFinanceObserver.php
```

### Conexão com Banco de Dados

Todos os modelos do módulo financeiro utilizam a conexão `tenant`, que aponta para o banco de dados específico de cada tenant:

```php
protected $connection = 'tenant';
```

---

## 📚 Ledger Financeiro e Valores Líquidos

### Conceito de Ledger Financeiro

O módulo financeiro utiliza o conceito de **ledger contábil** para garantir rastreabilidade completa de todos os lançamentos financeiros.

**Campos de Origem:**
- `origin_type` - Tipo da origem do lançamento:
  - `appointment` - Receita vinculada a agendamento
  - `charge` - Receita vinculada a cobrança externa
  - `manual` - Lançamento manual
  - `refund` - Estorno de transação
  - `adjustment` - Ajuste contábil

- `origin_id` - ID da entidade de origem (appointment_id, charge_id, etc.)

**Direção Contábil:**
- `credit` - Entrada de recursos (receitas)
- `debit` - Saída de recursos (despesas)

### Valores Brutos, Taxas e Líquidos

**Campos de Valores:**
- `gross_amount` - Valor bruto da operação (antes das taxas)
- `gateway_fee` - Taxa cobrada pelo gateway de pagamento
- `net_amount` - Valor líquido recebido (gross_amount - gateway_fee)
- `amount` - Valor líquido (mantido para compatibilidade, sempre igual a net_amount)

**Aplicação Prática:**

**Cenário 1 - Finance sem Billing:**
```
Valor da consulta: R$ 200,00
gross_amount = 200.00
gateway_fee = 0.00
net_amount = 200.00
```

**Cenário 2 - Finance com Billing (Cartão de Crédito):**
```
Valor da consulta: R$ 200,00
Taxa do gateway: R$ 7,98 (3,99% + R$ 0,40)
gross_amount = 200.00
gateway_fee = 7.98
net_amount = 192.02
```

### Preparação para Pagamentos Parciais

O sistema está preparado para suportar pagamentos parciais:

- Uma `FinancialCharge` pode ter múltiplas `FinancialTransaction`
- Cada transação registra o valor líquido pago
- `getPaidAmountAttribute()` soma todos os pagamentos
- `getPaymentStatusAttribute()` retorna:
  - `pending` - Nenhum pagamento
  - `partially_paid` - Pagamento parcial
  - `paid` - Pagamento completo

**Exemplo de Pagamento Parcial:**
```
Charge: R$ 200,00
Pagamento 1: R$ 100,00 (net_amount)
Pagamento 2: R$ 100,00 (net_amount)
Status: paid (total pago = R$ 200,00)
```

**Nota:** A interface para pagamentos parciais ainda não foi implementada. O sistema está preparado para quando essa funcionalidade for desenvolvida.

---

## 📊 Modelos de Dados

### 1. FinancialAccount (Contas Financeiras)

**Tabela:** `financial_accounts`

**Campos:**
- `id` (UUID, Primary Key)
- `name` (string) - Nome da conta
- `type` (enum: cash, bank, pix, credit) - Tipo da conta
- `initial_balance` (decimal 15,2) - Saldo inicial
- `active` (boolean) - Status ativo/inativo
- `created_at`, `updated_at` (timestamps)

**Relacionamentos:**
- `hasMany(FinancialTransaction)` - Transações vinculadas

**Métodos Especiais:**
- `getCurrentBalanceAttribute()` - Calcula saldo atual baseado em transações pagas

**Exemplo de Uso:**
```php
$account = FinancialAccount::create([
    'name' => 'Conta Corrente Principal',
    'type' => 'bank',
    'initial_balance' => 1000.00,
    'active' => true,
]);

$balance = $account->current_balance; // Calcula saldo atual
```

---

### 2. FinancialCategory (Categorias Financeiras)

**Tabela:** `financial_categories`

**Campos:**
- `id` (UUID, Primary Key)
- `name` (string) - Nome da categoria
- `type` (enum: income, expense) - Tipo (receita ou despesa)
- `color` (string, nullable) - Cor hexadecimal para visualização
- `active` (boolean) - Status ativo/inativo
- `created_at`, `updated_at` (timestamps)

**Relacionamentos:**
- `hasMany(FinancialTransaction)` - Transações vinculadas

**Exemplo de Uso:**
```php
$category = FinancialCategory::create([
    'name' => 'Consultas',
    'type' => 'income',
    'color' => '#3b82f6',
    'active' => true,
]);
```

---

### 3. FinancialTransaction (Transações Financeiras)

**Tabela:** `financial_transactions`

**Campos:**
- `id` (UUID, Primary Key)
- `type` (enum: income, expense) - Tipo de transação
- `origin_type` (string) - Tipo de origem: `appointment`, `charge`, `manual`, `refund`, `adjustment`
- `origin_id` (UUID, nullable) - ID da origem (appointment_id, charge_id, etc.)
- `direction` (enum: credit, debit) - Direção contábil (credit para receitas, debit para despesas)
- `description` (string) - Descrição da transação
- `amount` (decimal 15,2) - Valor líquido (net_amount) - mantido para compatibilidade
- `gross_amount` (decimal 15,2) - Valor bruto da operação
- `gateway_fee` (decimal 15,2) - Taxa do gateway de pagamento
- `net_amount` (decimal 15,2) - Valor líquido (gross_amount - gateway_fee)
- `date` (date) - Data da transação
- `status` (enum: pending, paid, cancelled) - Status
- `account_id` (UUID, nullable, FK) - Conta vinculada
- `category_id` (UUID, nullable, FK) - Categoria vinculada
- `appointment_id` (UUID, nullable, FK) - Agendamento vinculado (mantido para compatibilidade)
- `patient_id` (UUID, nullable, FK) - Paciente vinculado
- `doctor_id` (UUID, nullable, FK) - Médico vinculado
- `created_by` (bigint, nullable, FK) - Usuário que criou
- `metadata` (json, nullable) - Dados adicionais (idempotência, etc.)
- `created_at`, `updated_at` (timestamps)

**Relacionamentos:**
- `belongsTo(FinancialAccount)` - Conta
- `belongsTo(FinancialCategory)` - Categoria
- `belongsTo(Appointment)` - Agendamento
- `belongsTo(Patient)` - Paciente
- `belongsTo(Doctor)` - Médico
- `belongsTo(User)` - Criador
- `hasOne(DoctorCommission)` - Comissão vinculada

**Exemplo de Uso:**
```php
$transaction = FinancialTransaction::create([
    'type' => 'income',
    'origin_type' => 'appointment',
    'origin_id' => $appointment->id,
    'direction' => 'credit',
    'description' => 'Pagamento de consulta',
    'amount' => 150.00,
    'gross_amount' => 150.00,
    'gateway_fee' => 0,
    'net_amount' => 150.00,
    'date' => now(),
    'status' => 'paid',
    'account_id' => $account->id,
    'category_id' => $category->id,
    'appointment_id' => $appointment->id,
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'created_by' => auth()->id(),
]);
```

**Regras de Ledger:**
- `income` → `direction = 'credit'`
- `expense` → `direction = 'debit'`
- Todo lançamento deve ter `origin_type`
- `origin_id` é obrigatório quando houver vínculo externo
- `amount` sempre igual a `net_amount` (compatibilidade)

---

### 4. FinancialCharge (Cobranças)

**Tabela:** `financial_charges`

**Campos:**
- `id` (UUID, Primary Key)
- `appointment_id` (UUID, nullable, FK) - Agendamento vinculado
- `patient_id` (UUID, FK) - Paciente
- `asaas_customer_id` (string, nullable) - ID do cliente no Asaas
- `asaas_charge_id` (string, nullable) - ID da cobrança no Asaas
- `amount` (decimal 15,2) - Valor total da cobrança
- `billing_type` (enum: reservation, full) - Tipo (reserva ou completo)
- `status` (enum: pending, paid, expired, cancelled) - Status
- `due_date` (date) - Data de vencimento
- `payment_link` (text, nullable) - Link de pagamento
- `origin` (enum: public, portal, internal) - Origem do agendamento
- `paid_at` (timestamp, nullable) - Data do pagamento
- `payment_method` (string, nullable) - Método de pagamento
- `created_at`, `updated_at` (timestamps)

**Relacionamentos:**
- `belongsTo(Appointment)` - Agendamento
- `belongsTo(Patient)` - Paciente
- `hasMany(FinancialTransaction)` - Transações vinculadas (suporta pagamentos parciais)
- `hasOne(FinancialTransaction)` - Primeira transação (legado, compatibilidade)

**Métodos Especiais:**
- `isPaid()` - Verifica se está paga
- `isOverdue()` - Verifica se está vencida
- `getPaidAmountAttribute()` - Calcula valor total pago (soma de net_amount das transações pagas)
- `getPaymentStatusAttribute()` - Status de pagamento: `pending`, `partially_paid`, `paid`

**Exemplo de Uso:**
```php
$charge = FinancialCharge::create([
    'appointment_id' => $appointment->id,
    'patient_id' => $patient->id,
    'amount' => 200.00,
    'billing_type' => 'full',
    'status' => 'pending',
    'due_date' => now()->addDays(5),
    'origin' => 'internal',
]);

if ($charge->isOverdue()) {
    // Processar cobrança vencida
}
```

---

### 5. DoctorBillingPrice (Preços de Cobrança por Médico)

**Tabela:** `doctor_billing_prices`

**Campos:**
- `id` (UUID, Primary Key)
- `doctor_id` (UUID, FK) - Médico
- `specialty_id` (UUID, nullable, FK) - Especialidade (opcional)
- `reservation_amount` (decimal 15,2) - Valor da reserva
- `full_appointment_amount` (decimal 15,2) - Valor completo
- `active` (boolean) - Status ativo/inativo
- `created_at`, `updated_at` (timestamps)

**Relacionamentos:**
- `belongsTo(Doctor)` - Médico
- `belongsTo(MedicalSpecialty)` - Especialidade

**Métodos Estáticos:**
- `findPrice($doctorId, $specialtyId = null)` - Busca preço por médico e especialidade

**Exemplo de Uso:**
```php
$price = DoctorBillingPrice::findPrice($doctorId, $specialtyId);

if ($price) {
    $reservationAmount = $price->reservation_amount;
    $fullAmount = $price->full_appointment_amount;
}
```

---

## 🎮 Controllers

### FinancialAccountController

**Namespace:** `App\Http\Controllers\Tenant\Finance`

**Rotas:**
- `GET /tenant/finance/accounts` - Listar contas
- `GET /tenant/finance/accounts/create` - Formulário de criação
- `POST /tenant/finance/accounts` - Criar conta
- `GET /tenant/finance/accounts/{account}` - Detalhes da conta
- `GET /tenant/finance/accounts/{account}/edit` - Formulário de edição
- `PUT /tenant/finance/accounts/{account}` - Atualizar conta
- `DELETE /tenant/finance/accounts/{account}` - Excluir conta

**Permissões:**
- Apenas usuários com role `admin` podem gerenciar contas

**Métodos Principais:**
- `index()` - Lista todas as contas paginadas
- `create()` - Exibe formulário de criação
- `store(StoreAccountRequest)` - Cria nova conta
- `show(FinancialAccount)` - Exibe detalhes da conta
- `edit(FinancialAccount)` - Exibe formulário de edição
- `update(UpdateAccountRequest, FinancialAccount)` - Atualiza conta
- `destroy(FinancialAccount)` - Remove conta (se não houver transações)

---

### FinancialCategoryController

**Namespace:** `App\Http\Controllers\Tenant\Finance`

**Rotas:**
- `GET /tenant/finance/categories` - Listar categorias
- `GET /tenant/finance/categories/create` - Formulário de criação
- `POST /tenant/finance/categories` - Criar categoria
- `GET /tenant/finance/categories/{category}` - Detalhes da categoria
- `GET /tenant/finance/categories/{category}/edit` - Formulário de edição
- `PUT /tenant/finance/categories/{category}` - Atualizar categoria
- `DELETE /tenant/finance/categories/{category}` - Excluir categoria

**Permissões:**
- Apenas usuários com role `admin` podem gerenciar categorias

**Métodos Principais:**
- `index()` - Lista todas as categorias paginadas
- `create()` - Exibe formulário de criação
- `store(StoreCategoryRequest)` - Cria nova categoria
- `show(FinancialCategory)` - Exibe detalhes da categoria
- `edit(FinancialCategory)` - Exibe formulário de edição
- `update(UpdateCategoryRequest, FinancialCategory)` - Atualiza categoria
- `destroy(FinancialCategory)` - Remove categoria (se não houver transações)

---

### FinancialTransactionController

**Namespace:** `App\Http\Controllers\Tenant\Finance`

**Rotas:**
- `GET /tenant/finance/transactions` - Listar transações
- `GET /tenant/finance/transactions/create` - Formulário de criação
- `POST /tenant/finance/transactions` - Criar transação
- `GET /tenant/finance/transactions/{transaction}` - Detalhes da transação
- `GET /tenant/finance/transactions/{transaction}/edit` - Formulário de edição
- `PUT /tenant/finance/transactions/{transaction}` - Atualizar transação

**Permissões:**
- `admin` - Acesso total
- `user` - Apenas transações de médicos permitidos
- `doctor` - Apenas suas próprias transações

**Filtros Disponíveis:**
- `type` - Filtrar por tipo (income/expense)
- `status` - Filtrar por status
- `date_from` - Data inicial
- `date_to` - Data final

**Métodos Principais:**
- `index(Request)` - Lista transações com filtros
- `create()` - Exibe formulário de criação
- `store(StoreTransactionRequest)` - Cria nova transação
- `show(FinancialTransaction)` - Exibe detalhes da transação
- `edit(FinancialTransaction)` - Exibe formulário de edição
- `update(UpdateTransactionRequest, FinancialTransaction)` - Atualiza transação

---

### FinancialChargeController

**Namespace:** `App\Http\Controllers\Tenant\Finance`

**Rotas:**
- `GET /tenant/finance/charges` - Listar cobranças
- `GET /tenant/finance/charges/{charge}` - Detalhes da cobrança
- `POST /tenant/finance/charges/{charge}/cancel` - Cancelar cobrança
- `POST /tenant/finance/charges/{charge}/resend-link` - Reenviar link de pagamento

**Permissões:**
- `admin` - Acesso total
- `user` - Apenas cobranças de médicos permitidos
- `doctor` - Apenas suas próprias cobranças

**Filtros Disponíveis:**
- `status` - Filtrar por status
- `origin` - Filtrar por origem
- `date_from` - Data inicial
- `date_to` - Data final

**Métodos Principais:**
- `index(Request)` - Lista cobranças com filtros
- `show(FinancialCharge)` - Exibe detalhes da cobrança
- `cancel(FinancialCharge)` - Cancela cobrança (apenas admin)
- `resendLink(FinancialCharge)` - Reenvia link de pagamento

---

## 🔧 Services

### FinanceRecorderService (Core)

**Namespace:** `App\Services\Finance`

**Responsabilidades:**
- Registrar receitas/despesas sem qualquer integração externa
- Atualizar saldos de contas
- Gerenciar transações financeiras

**REGRAS:**
- ❌ Nunca chama Asaas ou qualquer gateway
- ❌ Nunca cria FinancialCharge
- ✅ Usa apenas tenant connection
- ✅ Atualiza saldo da conta (se status = paid)

**Métodos Principais:**

#### `recordAppointmentIncome(Appointment $appointment): FinancialTransaction`
Registra receita vinculada a um agendamento.

```php
$recorder = app(FinanceRecorderService::class);
$transaction = $recorder->recordAppointmentIncome($appointment);
```

#### `recordManualIncome(array $data): FinancialTransaction`
Registra receita manual.

```php
$transaction = $recorder->recordManualIncome([
    'description' => 'Pagamento de consulta',
    'amount' => 200.00,
    'date' => now(),
    'status' => 'paid',
    'account_id' => $account->id,
    'category_id' => $category->id,
]);
```

#### `recordExpense(array $data): FinancialTransaction`
Registra despesa manual.

```php
$transaction = $recorder->recordExpense([
    'description' => 'Compra de material',
    'amount' => 150.00,
    'date' => now(),
    'status' => 'paid',
]);
```

#### `recordRefund(FinancialTransaction $originalTransaction, ?string $reason): FinancialTransaction`
Registra estorno de uma transação paga.

```php
$refund = $recorder->recordRefund($transaction, 'Cancelamento de consulta');
```

---

### BillingService (Orquestrador)

**Namespace:** `App\Services\Billing`

**Responsabilidades:**
- Decidir qual provider usar
- Criar FinancialCharge
- Chamar provider
- Disparar eventos

**REGRAS:**
- ❌ Não calcula valores financeiros (usa FinanceRecorderService)
- ❌ Não cria FinancialTransaction diretamente
- ✅ Apenas gerencia cobranças externas

**Métodos Principais:**

#### `createChargeForAppointment(Appointment $appointment): ?FinancialCharge`
Cria cobrança para um agendamento.

```php
$billingService = app(BillingService::class);
$charge = $billingService->createChargeForAppointment($appointment);
```

#### `cancelCharge(FinancialCharge $charge): bool`
Cancela uma cobrança.

```php
$success = $billingService->cancelCharge($charge);
```

---

### BillingProviderInterface

**Namespace:** `App\Services\Billing`

Interface para providers de billing (gateways de pagamento).

**Métodos:**
- `createCustomer(Patient $patient): ?string`
- `createCharge(FinancialCharge $charge): array`
- `cancelCharge(FinancialCharge $charge): bool`
- `getChargeStatus(FinancialCharge $charge): array`
- `generatePaymentLink(FinancialCharge $charge): ?string`

---

### AsaasBillingProvider

**Namespace:** `App\Services\Billing\Providers`

Implementação do BillingProviderInterface para Asaas.

**Configuração:**
- `finance.billing.asaas.environment` - Ambiente (sandbox/production)
- `finance.billing.asaas.api_key` - Chave da API do Asaas

**Nota:** Este é apenas um provider. O sistema pode ter múltiplos providers (Stripe, Pix direto, etc.).

---

## 👁️ Observers

### AppointmentFinanceObserver

**Namespace:** `App\Observers\Finance`

**Responsabilidade:**
Processa eventos financeiros quando um agendamento é criado.

**Eventos Observados:**
- `Appointment::created` - Processa financeiro/billing

**Lógica Simplificada:**

```php
public function created(Appointment $appointment): void
{
    if (tenant_setting('finance.enabled') !== 'true') {
        return;
    }

    // Se billing desabilitado, apenas registra receita
    if (tenant_setting('finance.billing.enabled') !== 'true') {
        app(FinanceRecorderService::class)
            ->recordAppointmentIncome($appointment);
        return;
    }

    // Se billing habilitado, cria cobrança
    app(BillingService::class)
        ->createChargeForAppointment($appointment);
}
```

**REGRAS:**
- ❌ Nunca chama Asaas diretamente
- ❌ Nunca cria FinancialTransaction diretamente
- ✅ Delega para FinanceRecorderService ou BillingService

**Fluxos:**

**Cenário A - Finance ON, Billing OFF:**
```
Appointment criado
  ↓
Observer detecta
  ↓
FinanceRecorderService.recordAppointmentIncome()
  ↓
FinancialTransaction criada (status = paid)
```

**Cenário B - Finance ON, Billing ON:**
```
Appointment criado
  ↓
Observer detecta
  ↓
BillingService.createChargeForAppointment()
  ↓
FinancialCharge criada
  ↓
AsaasBillingProvider.createCharge()
  ↓
Webhook recebe pagamento
  ↓
Event PaymentConfirmed disparado
  ↓
Listener cria FinancialTransaction
```

---

## 🛣️ Rotas

### Rotas Autenticadas

Todas as rotas do módulo financeiro estão protegidas pelo middleware `module.access:finance`:

```php
Route::middleware(['module.access:finance'])->group(function () {
    // Rotas do módulo financeiro
});
```

### Rotas Públicas

#### Páginas de Pagamento

- `GET /t/{slug}/pagamento/{charge}` - Página de pagamento pública
- `GET /t/{slug}/pagamento/{charge}/sucesso` - Página de sucesso
- `GET /t/{slug}/pagamento/{charge}/erro` - Página de erro

**Controller:** `App\Http\Controllers\Tenant\PaymentController`

### Webhook do Asaas

- `POST /t/{slug}/webhooks/asaas` - Webhook para receber notificações do Asaas

**Controller:** `App\Http\Controllers\Tenant\AsaasWebhookController`

**Middlewares:**
- `throttle.asaas.webhook` - Rate limiting
- `verify.asaas.webhook.secret` - Verificação de secret
- `verify.asaas.webhook.ip` - Whitelist de IPs

---

## 🔗 Integração com Gateways de Pagamento (Billing)

### Arquitetura de Billing

O sistema suporta múltiplos gateways de pagamento através da interface `BillingProviderInterface`.

**Providers Disponíveis:**
- ✅ Asaas (`AsaasBillingProvider`)
- 🔜 Stripe (futuro)
- 🔜 Pix direto (futuro)
- 🔜 Outros gateways (futuro)

### Fluxo de Cobrança com Billing

1. **Criação da Cobrança:**
   ```php
   $billingService = app(BillingService::class);
   $charge = $billingService->createChargeForAppointment($appointment);
   ```

2. **Provider cria no gateway:**
   ```php
   // Internamente, BillingService chama:
   $provider = new AsaasBillingProvider();
   $result = $provider->createCharge($charge);
   ```

3. **Geração do Link:**
   ```php
   $paymentLink = $provider->generatePaymentLink($charge);
   ```

4. **Notificação de Pagamento:**
   - Webhook recebe notificação do gateway
   - `AsaasWebhookProcessor` atualiza status da cobrança
   - Evento `PaymentConfirmed` é disparado
   - Listener `CreateTransactionOnPaymentConfirmed` cria `FinancialTransaction`
   - Notifica paciente/clínica

### Status de Cobranças

**Mapeamento de Status:**

| Asaas | Sistema |
|-------|---------|
| PENDING | pending |
| RECEIVED/CONFIRMED | paid |
| OVERDUE | expired |
| REFUNDED/CANCELLED | cancelled |

### Métodos de Pagamento Suportados

- **PIX** - Pagamento instantâneo
- **BOLETO** - Boleto bancário
- **CREDIT_CARD** - Cartão de crédito
- **DEBIT_CARD** - Cartão de débito

---

## 🔄 Fluxos de Trabalho

### 1. Criação de Agendamento (Finance ON, Billing OFF)

```
1. Usuário cria agendamento
   ↓
2. AppointmentObserver detecta criação
   ↓
3. AppointmentFinanceObserver verifica: finance.enabled = true
   ↓
4. Verifica: finance.billing.enabled = false
   ↓
5. FinanceRecorderService.recordAppointmentIncome()
   ↓
6. FinancialTransaction criada (status = paid)
   ↓
7. Saldo da conta atualizado
```

### 2. Criação de Agendamento (Finance ON, Billing ON)

```
1. Usuário cria agendamento
   ↓
2. AppointmentObserver detecta criação
   ↓
3. AppointmentFinanceObserver verifica: finance.enabled = true
   ↓
4. Verifica: finance.billing.enabled = true
   ↓
5. BillingService.createChargeForAppointment()
   ↓
6. Determina valor e cria FinancialCharge
   ↓
7. AsaasBillingProvider.createCharge()
   ↓
8. Cobrança criada no Asaas
   ↓
9. Link de pagamento gerado
   ↓
10. Notificação enviada (se configurado)
```

### 3. Pagamento de Cobrança (Webhook)

```
1. Paciente realiza pagamento no gateway
   ↓
2. Gateway envia webhook
   ↓
3. AsaasWebhookController recebe webhook
   ↓
4. ProcessAsaasWebhookJob processa
   ↓
5. AsaasWebhookProcessor atualiza FinancialCharge
   ↓
6. Event PaymentConfirmed disparado
   ↓
7. CreateTransactionOnPaymentConfirmed listener
   ↓
8. FinanceRecorderService cria FinancialTransaction
   ↓
9. Notifica paciente/clínica
```

### 3. Registro Manual de Transação

```
1. Usuário acessa formulário de criação
   ↓
2. Preenche dados da transação
   ↓
3. FinancialTransactionController valida dados
   ↓
4. Cria FinancialTransaction
   ↓
5. Atualiza saldo da conta (se status = paid)
```

---

## ⚙️ Configurações

### Configurações do Tenant

O módulo utiliza `TenantSetting` para armazenar configurações:

#### Habilitar/Desabilitar Módulo
```php
tenant_setting('finance.enabled', 'true'); // 'true' ou 'false'
```

#### Modo de Cobrança
```php
tenant_setting('finance.billing_mode', 'global');
// Valores: 'disabled', 'global', 'per_doctor', 'per_doctor_specialty'
```

#### Valores Globais (modo global)
```php
tenant_setting('finance.global_billing_type', 'reservation'); // 'reservation' ou 'full'
tenant_setting('finance.reservation_amount', '50.00');
tenant_setting('finance.full_appointment_amount', '200.00');
```

#### Cobrança por Origem
```php
tenant_setting('finance.charge_on_public_appointment', 'true');
tenant_setting('finance.charge_on_patient_portal', 'true');
tenant_setting('finance.charge_on_internal_appointment', 'true');
```

#### Habilitar/Desabilitar Billing
```php
tenant_setting('finance.billing.enabled', 'false'); // 'true' ou 'false'
tenant_setting('finance.billing.provider', 'asaas'); // 'asaas', 'stripe', etc.
```

#### Integração Asaas (quando billing habilitado)
```php
tenant_setting('finance.billing.asaas.environment', 'sandbox'); // 'sandbox' ou 'production'
tenant_setting('finance.billing.asaas.api_key', 'sua_chave_api');
tenant_setting('finance.billing.asaas.webhook_secret', 'seu_secret');
```

#### Conta e Categoria Padrão
```php
tenant_setting('finance.default_account_id', 'uuid-da-conta');
tenant_setting('finance.default_category_id', 'uuid-da-categoria');
```

#### Métodos de Pagamento
```php
tenant_setting('finance.payment_methods', '["pix", "boleto", "credit_card"]');
```

#### Notificações
```php
tenant_setting('finance.auto_send_payment_link', 'true');
```

---

## 📈 Relatórios

O módulo oferece diversos relatórios financeiros:

### 1. Fluxo de Caixa
- **Rota:** `GET /tenant/finance/reports/cash-flow`
- **Controller:** `CashFlowReportController`
- **Exportação:** CSV, Excel, PDF

### 2. Receitas e Despesas
- **Rota:** `GET /tenant/finance/reports/income-expense`
- **Controller:** `IncomeExpenseReportController`
- **Exportação:** CSV, Excel, PDF

### 3. Cobranças
- **Rota:** `GET /tenant/finance/reports/charges`
- **Controller:** `ChargesReportController`
- **Exportação:** CSV, Excel, PDF

### 4. Pagamentos
- **Rota:** `GET /tenant/finance/reports/payments`
- **Controller:** `PaymentsReportController`
- **Exportação:** CSV, Excel, PDF

### 5. Comissões
- **Rota:** `GET /tenant/finance/reports/commissions`
- **Controller:** `CommissionsReportController`
- **Exportação:** CSV, Excel, PDF

---

## 🔒 Segurança e Permissões

### Middleware de Acesso

Todas as rotas do módulo financeiro utilizam:
- `tenant.auth` - Autenticação obrigatória
- `module.access:finance` - Verificação de acesso ao módulo

### Controle de Acesso por Role

#### Admin
- ✅ Acesso total ao módulo
- ✅ Gerenciar contas e categorias
- ✅ Ver todas as transações e cobranças
- ✅ Cancelar cobranças
- ✅ Acessar todos os relatórios

#### User
- ✅ Ver transações e cobranças de médicos permitidos
- ✅ Criar transações manuais
- ✅ Reenviar links de pagamento
- ✅ Ver relatórios filtrados

#### Doctor
- ✅ Ver apenas suas próprias transações e cobranças
- ✅ Ver relatórios próprios
- ❌ Não pode cancelar cobranças
- ❌ Não pode gerenciar contas/categorias

### Validações

Todos os controllers utilizam Form Requests para validação:

- `StoreAccountRequest` - Validação de criação de conta
- `UpdateAccountRequest` - Validação de atualização de conta
- `StoreCategoryRequest` - Validação de criação de categoria
- `UpdateCategoryRequest` - Validação de atualização de categoria
- `StoreTransactionRequest` - Validação de criação de transação
- `UpdateTransactionRequest` - Validação de atualização de transação

---

## 📝 Migrations

### Tabelas Criadas

1. **financial_accounts** - Contas financeiras
2. **financial_categories** - Categorias financeiras
3. **financial_transactions** - Transações financeiras
4. **financial_charges** - Cobranças
5. **doctor_billing_prices** - Preços por médico

### Executar Migrations

```bash
php artisan tenants:migrate
```

---

## 🧪 Exemplos de Uso

### Criar Conta Financeira

```php
use App\Models\Tenant\FinancialAccount;

$account = FinancialAccount::create([
    'name' => 'Conta Corrente Banco do Brasil',
    'type' => 'bank',
    'initial_balance' => 5000.00,
    'active' => true,
]);
```

### Criar Categoria

```php
use App\Models\Tenant\FinancialCategory;

$category = FinancialCategory::create([
    'name' => 'Consultas Médicas',
    'type' => 'income',
    'color' => '#10b981',
    'active' => true,
]);
```

### Criar Transação Manual

```php
use App\Models\Tenant\FinancialTransaction;

$transaction = FinancialTransaction::create([
    'type' => 'income',
    'description' => 'Pagamento de consulta particular',
    'amount' => 300.00,
    'date' => now(),
    'status' => 'paid',
    'account_id' => $account->id,
    'category_id' => $category->id,
    'created_by' => auth()->id(),
]);
```

### Consultar Cobranças Pendentes

```php
use App\Models\Tenant\FinancialCharge;

$pendingCharges = FinancialCharge::where('status', 'pending')
    ->where('due_date', '>=', now())
    ->with(['patient', 'appointment'])
    ->get();
```

### Verificar Saldo de Conta

```php
$account = FinancialAccount::find($accountId);
$currentBalance = $account->current_balance;
```

---

## 🐛 Troubleshooting

### Transação não é criada automaticamente

**Verificações:**
1. Módulo financeiro está habilitado? (`finance.enabled = 'true'`)
2. Se billing está desabilitado, verificar se `FinanceRecorderService` está sendo chamado
3. Se billing está habilitado, verificar se webhook está funcionando
4. Verificar logs em `storage/logs/laravel.log`

### Cobrança não é criada automaticamente

**Verificações:**
1. Módulo financeiro está habilitado? (`finance.enabled = 'true'`)
2. Billing está habilitado? (`finance.billing.enabled = 'true'`)
3. Provider está configurado? (`finance.billing.provider = 'asaas'`)
4. Origem do agendamento está configurada para gerar cobrança?
5. Valor configurado é maior que zero?

### Erro ao criar cobrança no gateway

**Verificações:**
1. Billing está habilitado? (`finance.billing.enabled = 'true'`)
2. API Key do provider está configurada corretamente?
3. Ambiente está correto (sandbox/production)?
4. Paciente tem CPF/Email válidos?
5. Verificar logs em `storage/logs/laravel.log`

### Link de pagamento não é gerado

**Verificações:**
1. Cobrança foi criada no gateway? (`asaas_charge_id` não é null)
2. API do gateway está respondendo?
3. Verificar logs para erros específicos

### Transação não é criada após pagamento

**Verificações:**
1. Webhook está configurado corretamente?
2. Evento `PaymentConfirmed` está sendo disparado?
3. Listener `CreateTransactionOnPaymentConfirmed` está registrado?
4. Verificar `EventServiceProvider` para mapeamento de eventos

---

## 📚 Referências

- [Documentação do Asaas](https://docs.asaas.com/)
- [Laravel Multitenancy](https://spatie.be/docs/laravel-multitenancy)
- [Documentação Laravel](https://laravel.com/docs)

---

## 🔄 Changelog

### Versão 2.0.0 (Refatoração Arquitetural)
- ✅ Separação Finance (core) e Billing (opcional)
- ✅ Finance funciona 100% independente do Asaas
- ✅ BillingProviderInterface para múltiplos gateways
- ✅ AsaasBillingProvider implementado
- ✅ FinanceRecorderService para registro de transações
- ✅ BillingService como orquestrador
- ✅ Sistema de eventos (PaymentConfirmed, ChargeCreated, etc.)
- ✅ Transações imutáveis quando pagas
- ✅ Idempotência em webhooks

### Versão 1.0.0
- Implementação inicial do módulo financeiro
- Integração com Asaas
- Sistema de cobranças automáticas
- Relatórios financeiros
- Gestão de comissões

---

**Última atualização:** 2025-01-20
**Autor:** Sistema de Documentação Automática

