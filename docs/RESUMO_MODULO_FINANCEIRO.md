# 💰 RESUMO COMPLETO - MÓDULO FINANCEIRO

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Estrutura de Dados](#estrutura-de-dados)
3. [Funcionalidades Implementadas](#funcionalidades-implementadas)
4. [Arquitetura e Componentes](#arquitetura-e-componentes)
5. [Fluxos Automáticos](#fluxos-automáticos)
6. [Segurança e Hardening](#segurança-e-hardening)
7. [Relatórios](#relatórios)
8. [Conciliação Automática](#conciliação-automática)
9. [Arquivos Criados](#arquivos-criados)
10. [Configurações](#configurações)

---

## 🎯 Visão Geral

O **Módulo Financeiro** é um sistema completo e **opcional** para gestão financeira de tenants, totalmente integrado ao fluxo de agendamentos. O módulo permite:

- ✅ Gestão completa de contas, categorias e transações financeiras
- ✅ Cobranças automáticas via Asaas integradas ao fluxo de agendamentos
- ✅ Comissões médicas automáticas
- ✅ Relatórios financeiros completos
- ✅ Conciliação automática via webhooks
- ✅ Auditoria completa de todas as operações
- ✅ Hardening completo para produção

### Características Principais

- **Opcional**: Pode ser habilitado/desabilitado por tenant sem impacto no sistema
- **Isolado**: Zero impacto quando desabilitado
- **Multi-tenant**: Cada tenant tem sua própria configuração e dados
- **Seguro**: Hardening completo com rate limiting, validação de secrets, IP whitelist
- **Auditável**: Rastreabilidade total de todas as operações
- **Resiliente**: Processamento assíncrono via filas, retry automático, idempotência

---

## 🗄️ Estrutura de Dados

### Tabelas Criadas

#### 1. `financial_accounts` - Contas Financeiras
- Armazena contas (dinheiro, banco, PIX, crédito)
- Campos: `id`, `name`, `type`, `initial_balance`, `current_balance`, `description`, `is_active`, `timestamps`

#### 2. `financial_categories` - Categorias Financeiras
- Categorias de receita e despesa
- Campos: `id`, `name`, `type` (income/expense), `description`, `is_active`, `timestamps`

#### 3. `financial_transactions` - Transações Financeiras
- Entrada e saída de valores
- Campos: `id`, `type` (income/expense), `amount`, `date`, `description`, `account_id`, `category_id`, `appointment_id`, `doctor_id`, `patient_id`, `charge_id`, `status`, `timestamps`

#### 4. `financial_charges` - Cobranças de Agendamentos
- Cobranças vinculadas a agendamentos
- Campos: `id`, `appointment_id`, `patient_id`, `amount`, `due_date`, `status` (pending/paid/cancelled/expired/refunded), `asaas_charge_id`, `asaas_payment_id`, `payment_link`, `paid_at`, `payment_method`, `timestamps`

#### 5. `doctor_commissions` - Comissões Médicas
- Comissões calculadas automaticamente
- Campos: `id`, `doctor_id`, `appointment_id`, `transaction_id`, `charge_id`, `amount`, `percentage`, `status` (pending/paid/cancelled), `paid_at`, `timestamps`

#### 6. `asaas_webhook_events` - Auditoria de Webhooks
- Registro completo de todos os webhooks recebidos
- Campos: `id`, `asaas_event_id`, `event_type`, `payload` (JSON), `status` (success/error/skipped), `error_message`, `processed_at`, `timestamps`

#### 7. Campos Adicionados em Tabelas Existentes
- `patients.asaas_customer_id` - ID do cliente no Asaas
- `appointments.origin` - Origem do agendamento (public/portal/internal)

---

## ⚙️ Funcionalidades Implementadas

### 1. CRUDs Completos

#### Contas Financeiras (`/workspace/{slug}/finance/accounts`)
- ✅ Listar, criar, editar, visualizar contas
- ✅ Tipos: dinheiro, banco, PIX, crédito
- ✅ Saldo inicial e atual
- ✅ Ativação/desativação

#### Categorias Financeiras (`/workspace/{slug}/finance/categories`)
- ✅ Listar, criar, editar, visualizar categorias
- ✅ Tipos: receita (income) e despesa (expense)
- ✅ Ativação/desativação

#### Transações Financeiras (`/workspace/{slug}/finance/transactions`)
- ✅ Listar, criar, editar, visualizar transações
- ✅ Tipos: receita e despesa
- ✅ Vinculação com contas, categorias, agendamentos, médicos, pacientes
- ✅ Status: pending, paid, cancelled

#### Cobranças (`/workspace/{slug}/finance/charges`)
- ✅ Listar e visualizar cobranças
- ✅ Cancelar cobranças
- ✅ Reenviar link de pagamento
- ✅ Filtros por status, origem, período, médico

#### Comissões Médicas (`/workspace/{slug}/finance/commissions`)
- ✅ Listar e visualizar comissões
- ✅ Marcar como paga
- ✅ Filtros por médico, status, período

### 2. Integração com Asaas

#### Serviço Asaas (`AsaasService`)
- ✅ Criar cliente no Asaas
- ✅ Criar cobrança no Asaas
- ✅ Gerar link de pagamento
- ✅ Cancelar cobrança
- ✅ Buscar pagamento
- ✅ Suporte a ambiente sandbox e produção

#### Webhook do Asaas
- ✅ Endpoint: `/t/{slug}/webhooks/asaas`
- ✅ Validação de secret
- ✅ Rate limiting (60 req/min)
- ✅ IP whitelist (opcional)
- ✅ Processamento assíncrono via fila
- ✅ Idempotência garantida

### 3. Fluxo Automático de Cobrança

#### Observer de Agendamentos (`AppointmentFinanceObserver`)
- ✅ Escuta eventos `created` e `updated` de `Appointment`
- ✅ Cria cobrança automaticamente quando:
  - Módulo financeiro está habilitado
  - Modo de cobrança não está desabilitado
  - Origem do agendamento permite cobrança (configurável)
- ✅ Respeita configurações:
  - `finance.billing_mode`
  - `finance.charge_on_public_appointment`
  - `finance.charge_on_patient_portal`
- ✅ Previne duplicação
- ✅ Usa transações para atomicidade
- ✅ Logs de erros sem quebrar fluxo

### 4. Redirecionamento e UX

#### Serviço de Redirecionamento (`FinanceRedirectService`)
- ✅ Decide quando redirecionar para pagamento
- ✅ Respeita origem do agendamento
- ✅ Agendamentos internos nunca redirecionam
- ✅ Valida todas as condições antes de redirecionar

#### Página Pública de Pagamento (`/t/{tenant}/pagamento/{charge}`)
- ✅ Exibe detalhes da cobrança
- ✅ Link de pagamento do Asaas
- ✅ Validações de status
- ✅ Mensagens apropriadas (pago, expirado, etc.)

### 5. Notificações

#### Envio de Links de Pagamento
- ✅ Email via `TenantNotificationService`
- ✅ WhatsApp via `TenantNotificationService`
- ✅ Envio automático quando configurado
- ✅ Configuração: `finance.auto_send_payment_link`

---

## 🏗️ Arquitetura e Componentes

### Models

- `FinancialAccount` - Contas financeiras
- `FinancialCategory` - Categorias
- `FinancialTransaction` - Transações
- `FinancialCharge` - Cobranças
- `DoctorCommission` - Comissões
- `AsaasWebhookEvent` - Auditoria de webhooks

### Services

- `AsaasService` - Integração com Asaas API
- `FinanceRedirectService` - Lógica de redirecionamento
- `AsaasWebhookProcessor` - Processamento de webhooks
- `ChargeReconciliationService` - Conciliação de cobranças
- `TransactionReconciliationService` - Conciliação de transações
- `CommissionReconciliationService` - Conciliação de comissões
- `FinanceHealthCheckService` - Health checks

### Controllers

- `FinanceController` - Dashboard financeiro
- `FinancialAccountController` - CRUD de contas
- `FinancialCategoryController` - CRUD de categorias
- `FinancialTransactionController` - CRUD de transações
- `FinancialChargeController` - Gestão de cobranças
- `DoctorCommissionController` - Gestão de comissões
- `FinanceSettingsController` - Configurações financeiras
- `AsaasWebhookController` - Webhook do Asaas
- `PaymentController` - Página pública de pagamento
- `FinanceReportController` - Dashboard de relatórios
- `CashFlowReportController` - Relatório de fluxo de caixa
- `IncomeExpenseReportController` - Relatório receitas x despesas
- `ChargesReportController` - Relatório de cobranças
- `PaymentsReportController` - Relatório de pagamentos
- `CommissionsReportController` - Relatório de comissões

### Observers

- `AppointmentFinanceObserver` - Cria cobranças automaticamente

### Jobs

- `ProcessAsaasWebhookJob` - Processamento assíncrono de webhooks

### Commands

- `FinanceReconcileCommand` - Reconciliação manual
- `FinanceHealthCheckCommand` - Health checks

### Middlewares

- `ThrottleAsaasWebhook` - Rate limiting
- `VerifyAsaasWebhookSecret` - Validação de secret
- `VerifyAsaasWebhookIpWhitelist` - Whitelist de IPs

### Form Requests

- `StoreAccountRequest` / `UpdateAccountRequest`
- `StoreCategoryRequest` / `UpdateCategoryRequest`
- `StoreTransactionRequest` / `UpdateTransactionRequest`

---

## 🔄 Fluxos Automáticos

### Fluxo 1: Criação de Agendamento → Cobrança

1. Agendamento criado (público, portal ou interno)
2. `AppointmentFinanceObserver` detecta evento
3. Verifica se módulo está habilitado e configurações permitem cobrança
4. Cria `FinancialCharge` no banco
5. Cria cliente no Asaas (se não existir)
6. Cria cobrança no Asaas
7. Atualiza `FinancialCharge` com IDs do Asaas
8. Gera link de pagamento
9. Envia link por email/WhatsApp (se configurado)
10. Redireciona para pagamento (se aplicável)

### Fluxo 2: Pagamento → Conciliação Automática

1. Pagamento realizado no Asaas
2. Asaas envia webhook para `/t/{tenant}/webhooks/asaas`
3. Middlewares validam (rate limit, secret, IP)
4. `AsaasWebhookController` recebe e despacha job
5. `ProcessAsaasWebhookJob` processa assincronamente
6. `AsaasWebhookProcessor` direciona evento
7. `ChargeReconciliationService` atualiza cobrança como paga
8. `TransactionReconciliationService` cria transação de receita
9. `CommissionReconciliationService` cria comissão (se aplicável)
10. Auditoria registrada em `asaas_webhook_events`

### Fluxo 3: Reconciliação Manual

1. Executar `php artisan finance:reconcile`
2. Busca cobranças pendentes ou inconsistentes
3. Consulta status real no Asaas
4. Corrige divergências
5. Cria transações e comissões faltantes
6. Loga todas as operações

---

## 🔐 Segurança e Hardening

### Segurança de Webhooks

- ✅ **Rate Limit**: 60 requisições por minuto por IP
- ✅ **Secret Validation**: `hash_equals()` para comparação segura
- ✅ **IP Whitelist**: Opcional e configurável por tenant
- ✅ **Idempotência**: Eventos nunca processados duas vezes

### Logs Estruturados

- ✅ Canal dedicado: `finance`
- ✅ Rotação: 30 dias
- ✅ Contexto obrigatório: tenant, charge_id, payment_id, appointment_id, event_type
- ✅ Masking de dados sensíveis

### Health Checks

- ✅ Verificação de saúde dos webhooks
- ✅ Verificação de saúde da fila
- ✅ Verificação de conectividade Asaas
- ✅ Verificação de inconsistências pendentes
- ✅ Comando: `php artisan finance:health-check`

### Feature Flags

- ✅ `finance.webhook_enabled` - Kill switch para webhooks
- ✅ `finance.auto_commission_enabled` - Comissões automáticas
- ✅ `finance.auto_transaction_enabled` - Transações automáticas

### Filas e Resiliência

- ✅ Fila dedicada: `finance`
- ✅ Retry: máximo 3 tentativas
- ✅ Timeout: 60 segundos
- ✅ Dead-letter: webhooks falhados marcados como `error`
- ✅ Não trava sistema em caso de falha

---

## 📊 Relatórios

### Dashboard Financeiro (`/workspace/{slug}/finance/reports`)

- ✅ Cards de resumo:
  - Receita do dia
  - Receita do mês
  - Despesas do mês
  - Saldo atual
  - Cobranças pendentes
  - Comissões pendentes
- ✅ Gráficos:
  - Linha: Receitas últimos 12 meses
  - Pizza: Receitas por categoria

### Relatórios Disponíveis

1. **Fluxo de Caixa** (`/workspace/{slug}/finance/reports/cash-flow`)
   - Transações com saldo acumulado
   - Filtros: período, conta, médico
   - Exportação: CSV

2. **Receitas x Despesas** (`/workspace/{slug}/finance/reports/income-expense`)
   - Comparativo com gráficos
   - Agrupamento por dia/mês
   - Exportação: CSV

3. **Cobranças** (`/workspace/{slug}/finance/reports/charges`)
   - Status, origem, período
   - Filtros avançados
   - Exportação: CSV

4. **Pagamentos Recebidos** (`/workspace/{slug}/finance/reports/payments`)
   - Lista de pagamentos confirmados
   - Método de pagamento
   - Exportação: CSV

5. **Comissões** (`/workspace/{slug}/finance/reports/commissions`)
   - Comissões por médico
   - Status e período
   - Exportação: CSV

### Controle de Acesso

- ✅ Admin: Acesso total
- ✅ Doctor: Apenas seus dados
- ✅ User: Apenas médicos permitidos

---

## 🔄 Conciliação Automática

### Processamento de Webhooks

1. Webhook recebido → Validações (rate limit, secret, IP)
2. Job despachado → Fila `finance`
3. Processor processa → Direciona para serviços
4. Serviços executam → Conciliações aplicadas
5. Auditoria registrada → Banco de dados

### Eventos Tratados

- `PAYMENT_RECEIVED` / `PAYMENT_CONFIRMED` → Pago
- `PAYMENT_OVERDUE` → Vencido
- `PAYMENT_CANCELED` → Cancelado
- `PAYMENT_REFUNDED` → Estornado

### Reconciliação Manual

```bash
php artisan finance:reconcile
php artisan finance:reconcile --tenant=clinic-slug
php artisan finance:reconcile --from=2025-01-01 --to=2025-01-31
php artisan finance:reconcile --force
```

### Proteções

- ✅ Idempotência em múltiplas camadas
- ✅ Verificação de duplicação
- ✅ Transações para atomicidade
- ✅ Logs detalhados

---

## 📁 Arquivos Criados

### Migrations (8)

1. `create_financial_accounts_table.php`
2. `create_financial_categories_table.php`
3. `create_financial_transactions_table.php`
4. `create_financial_charges_table.php`
5. `create_doctor_commissions_table.php`
6. `create_asaas_webhook_events_table.php`
7. `add_asaas_customer_id_to_patients_table.php`
8. `add_origin_to_appointments_table.php`
9. `add_status_to_asaas_webhook_events_table.php`
10. `add_paid_fields_to_financial_charges_table.php`

### Models (6)

1. `FinancialAccount.php`
2. `FinancialCategory.php`
3. `FinancialTransaction.php`
4. `FinancialCharge.php`
5. `DoctorCommission.php`
6. `AsaasWebhookEvent.php`

### Services (7)

1. `AsaasService.php` (tenant-specific)
2. `FinanceRedirectService.php`
3. `AsaasWebhookProcessor.php`
4. `ChargeReconciliationService.php`
5. `TransactionReconciliationService.php`
6. `CommissionReconciliationService.php`
7. `FinanceHealthCheckService.php`

### Controllers (14)

1. `FinanceController.php`
2. `FinancialAccountController.php`
3. `FinancialCategoryController.php`
4. `FinancialTransactionController.php`
5. `FinancialChargeController.php`
6. `DoctorCommissionController.php`
7. `FinanceSettingsController.php`
8. `AsaasWebhookController.php`
9. `PaymentController.php`
10. `FinanceReportController.php`
11. `CashFlowReportController.php`
12. `IncomeExpenseReportController.php`
13. `ChargesReportController.php`
14. `PaymentsReportController.php`
15. `CommissionsReportController.php`

### Form Requests (6)

1. `StoreAccountRequest.php`
2. `UpdateAccountRequest.php`
3. `StoreCategoryRequest.php`
4. `UpdateCategoryRequest.php`
5. `StoreTransactionRequest.php`
6. `UpdateTransactionRequest.php`

### Observers (1)

1. `AppointmentFinanceObserver.php`

### Jobs (1)

1. `ProcessAsaasWebhookJob.php`

### Commands (2)

1. `FinanceReconcileCommand.php`
2. `FinanceHealthCheckCommand.php`

### Middlewares (3)

1. `ThrottleAsaasWebhook.php`
2. `VerifyAsaasWebhookSecret.php`
3. `VerifyAsaasWebhookIpWhitelist.php`

### Helpers (1)

1. `FinanceHelpers.php` (masking de dados sensíveis)

### Views (30+)

- Views de CRUDs (accounts, categories, transactions, charges, commissions)
- Views de relatórios (dashboard, cashflow, income_expense, charges, payments, commissions)
- Views de configurações
- View de pagamento público

### Documentação (4)

1. `MODULO_FINANCEIRO.md`
2. `MODULO_FINANCEIRO_COMPLETO.md`
3. `HARDENING_PRODUCAO.md`
4. `docs/FINANCE_GO_LIVE_CHECKLIST.md`

---

## ⚙️ Configurações

### Configurações do Tenant

#### Habilitar Módulo
```php
TenantSetting::set('finance.enabled', 'true');
```

#### Configurar Asaas
```php
TenantSetting::set('finance.asaas.environment', 'production'); // ou 'sandbox'
TenantSetting::set('finance.asaas.api_key', 'sua_api_key');
TenantSetting::set('finance.asaas.webhook_secret', 'seu_secret');
```

#### Modo de Cobrança
```php
TenantSetting::set('finance.billing_mode', 'automatic'); // ou 'manual', 'disabled'
```

#### Origem de Cobrança
```php
TenantSetting::set('finance.charge_on_public_appointment', 'true');
TenantSetting::set('finance.charge_on_patient_portal', 'true');
```

#### Valores de Cobrança
```php
TenantSetting::set('finance.default_charge_amount', '100.00');
TenantSetting::set('finance.charge_by_appointment_type', 'true');
```

#### Comissões
```php
TenantSetting::set('finance.doctor_commission_enabled', 'true');
TenantSetting::set('finance.default_commission_percentage', '30');
```

#### Notificações
```php
TenantSetting::set('finance.auto_send_payment_link', 'true');
```

#### Segurança (Webhook)
```php
TenantSetting::set('finance.webhook_ip_whitelist_enabled', 'true');
TenantSetting::set('finance.webhook_ip_whitelist', json_encode(['IP1', 'IP2']));
```

#### Feature Flags
```php
TenantSetting::set('finance.webhook_enabled', 'true');
TenantSetting::set('finance.auto_commission_enabled', 'true');
TenantSetting::set('finance.auto_transaction_enabled', 'true');
```

### Configurações do Sistema

#### Logging
- Canal `finance` configurado em `config/logging.php`
- Rotação: 30 dias

#### Multitenancy
- Job `ProcessAsaasWebhookJob` registrado como tenant-aware em `config/multitenancy.php`

#### Rotas
- Rotas financeiras em `routes/tenant.php`
- Rotas públicas em `routes/web.php`

---

## ✅ Checklist de Implementação

### PASSO 1: Setup Inicial ✅
- [x] Migrations criadas
- [x] Models criados
- [x] AsaasService criado
- [x] Controllers básicos criados
- [x] Rotas adicionadas
- [x] Menu dinâmico implementado

### PASSO 2: Observer e Fluxo Automático ✅
- [x] AppointmentFinanceObserver criado
- [x] Campo `origin` adicionado em appointments
- [x] Lógica de criação de cobrança implementada
- [x] Integração com Asaas funcionando
- [x] Prevenção de duplicação

### PASSO 3: Fluxo de Cobrança e UX ✅
- [x] FinanceRedirectService criado
- [x] PaymentController atualizado
- [x] Redirecionamento implementado
- [x] Envio de links de pagamento
- [x] Página pública de pagamento

### PASSO 4: CRUDs Completos ✅
- [x] Controllers completos
- [x] Form Requests criados
- [x] Views básicas funcionais
- [x] Controle de acesso por role
- [x] Filtros por médico

### PASSO 5: Relatórios ✅
- [x] Dashboard financeiro
- [x] Relatórios analíticos
- [x] Filtros avançados
- [x] Exportações (CSV)
- [x] Controle de acesso

### PASSO 6: Conciliação Automática ✅
- [x] AsaasWebhookProcessor criado
- [x] Serviços de conciliação criados
- [x] Job assíncrono implementado
- [x] Comando de reconciliação manual
- [x] Auditoria completa

### PASSO 7: Hardening de Produção ✅
- [x] Middlewares de segurança
- [x] Rate limiting
- [x] Health checks
- [x] Logs estruturados
- [x] Feature flags
- [x] Checklist de go-live

---

## 🚀 Status Final

✅ **Módulo Financeiro 100% Implementado e Pronto para Produção**

- ✅ Todas as funcionalidades implementadas
- ✅ Segurança reforçada
- ✅ Hardening completo
- ✅ Documentação completa
- ✅ Zero impacto quando desabilitado
- ✅ Pronto para go-live

---

**Última atualização**: Janeiro 2025
**Versão**: 1.0.0
**Status**: ✅ Produção Ready

