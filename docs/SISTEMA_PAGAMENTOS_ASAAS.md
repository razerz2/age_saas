# 💳 Sistema de Pagamentos - Integração Asaas API

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Configuração](#configuração)
3. [Arquitetura do Sistema](#arquitetura-do-sistema)
4. [Fluxo de Criação de Assinaturas](#fluxo-de-criação-de-assinaturas)
5. [Tipos de Pagamento](#tipos-de-pagamento)
6. [Webhooks do Asaas](#webhooks-do-asaas)
7. [Sincronização Manual](#sincronização-manual)
8. [Mudança de Plano e Forma de Pagamento](#mudança-de-plano-e-forma-de-pagamento)
9. [Gestão de Faturas](#gestão-de-faturas)
10. [Tratamento de Erros](#tratamento-de-erros)
11. [Logs e Auditoria](#logs-e-auditoria)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

O sistema de pagamentos utiliza a **API do Asaas** como gateway de pagamento para gerenciar assinaturas e faturas dos tenants. A integração é bidirecional:

- **Saída (API)**: O sistema cria e gerencia assinaturas, clientes e faturas no Asaas
- **Entrada (Webhook)**: O Asaas notifica o sistema sobre eventos de pagamento, atualizações de assinatura, etc.

### Componentes Principais

- **`AsaasService`**: Serviço centralizado para comunicação com a API do Asaas
- **`SubscriptionController`**: Gerencia assinaturas e sincronização
- **`AsaasWebhookController`**: Processa eventos recebidos do Asaas
- **Models**: `Subscription`, `Invoices`, `Tenant`

---

## ⚙️ Configuração

### Variáveis de Ambiente

```env
# URL base da API do Asaas
ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
# ou para produção:
# ASAAS_API_URL=https://www.asaas.com/api/v3/

# Chave de API do Asaas
ASAAS_API_KEY=sua_chave_api_aqui

# Secret do Webhook (para validação)
ASAAS_WEBHOOK_SECRET=seu_secret_webhook

# Ambiente (sandbox ou production)
ASAAS_ENV=sandbox
```

### Configuração via Interface

As configurações também podem ser definidas via interface administrativa em `/Platform/settings`, que têm prioridade sobre as variáveis de ambiente.

### Configurações de Billing (SystemSetting)

As seguintes configurações controlam o comportamento automático de geração e notificação de faturas:

| Chave | Descrição | Valor Padrão | Onde Usar |
|-------|-----------|--------------|-----------|
| `billing.invoice_days_before_due` | Dias antes do vencimento para gerar faturas automaticamente | `10` | Comando `invoices:generate` |
| `billing.notify_days_before_due` | Dias antes do vencimento para enviar notificações preventivas | `5` | Comando `invoices:notify-upcoming` |
| `billing.recovery_days_after_suspension` | Dias após suspensão para iniciar recovery (cartão) | `5` | Comando `subscriptions:process-recovery` |
| `billing.purge_days_after_cancellation` | Dias após cancelamento para purgar dados | `90` | Comando `tenants:purge-canceled` |

**Como configurar:**
- Via código: `set_sysconfig('billing.invoice_days_before_due', 10)`
- Via interface: Em desenvolvimento (pode ser adicionado à interface administrativa)
- Via banco: `INSERT INTO system_settings (key, value) VALUES ('billing.invoice_days_before_due', '10')`

**Nota:** Essas configurações são específicas para faturas PIX/Boleto. Faturas de cartão são gerenciadas exclusivamente pelo Asaas.

### Configuração do Webhook no Asaas

1. Acesse o painel do Asaas
2. Vá em **Configurações → Webhooks**
3. Configure a URL do webhook: `https://seudominio.com/webhook/asaas`
4. Selecione os eventos que deseja receber:
   - `PAYMENT_CREATED`
   - `PAYMENT_RECEIVED`
   - `PAYMENT_CONFIRMED`
   - `PAYMENT_OVERDUE`
   - `PAYMENT_REFUNDED`
   - `PAYMENT_DELETED`
   - `SUBSCRIPTION_CREATED`
   - `SUBSCRIPTION_UPDATED`
   - `SUBSCRIPTION_INACTIVATED`
   - `SUBSCRIPTION_DELETED`
   - `CUSTOMER_DELETED`
5. Configure o **Token de Segurança** (deve corresponder a `ASAAS_WEBHOOK_SECRET`)

---

## 🏗️ Arquitetura do Sistema

### Fluxo de Dados

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   Platform  │────────▶│  AsaasService │────────▶│    Asaas     │
│  Controller │         │               │         │     API      │
└─────────────┘         └──────────────┘         └─────────────┘
       │                        │                         │
       │                        │                         │
       ▼                        ▼                         ▼
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│  Database   │         │   Webhook    │◀────────│   Webhook    │
│  (Platform) │         │  Controller │         │   Events     │
└─────────────┘         └──────────────┘         └─────────────┘
```

### Modelos e Relacionamentos

```
Tenant (1) ──┬── (N) Subscription
             │
             └── (N) Invoices (através de Subscription)

Subscription (1) ── (N) Invoices
Subscription (N) ── (1) Plan
```

### Campos de Integração

**Tabela `tenants`:**
- `asaas_customer_id` - ID do cliente no Asaas
- `suspended_at` - Data em que o tenant foi suspenso (para inadimplência)
- `canceled_at` - Data em que o tenant foi cancelado
- `asaas_synced` - Se o tenant está sincronizado
- `asaas_sync_status` - Status da sincronização (`pending`, `success`, `failed`, `deleted`)
- `asaas_last_sync_at` - Data da última sincronização
- `asaas_last_error` - Último erro de sincronização

**Tabela `subscriptions`:**
- `asaas_subscription_id` - ID da assinatura no Asaas
- `payment_method` - Método de pagamento (`PIX`, `CREDIT_CARD`, `BOLETO`, `DEBIT_CARD`)
- `auto_renew` - Se a assinatura renova automaticamente
- `billing_anchor_date` - Data de referência para cálculo de vencimento das faturas (PIX/Boleto)
- `recovery_started_at` - Data em que o processo de recovery foi iniciado (apenas para subscriptions)
- `status` - Status da assinatura (`pending`, `active`, `past_due`, `canceled`, `trialing`, `recovery_pending`)
- `asaas_synced` - Se está sincronizado
- `asaas_sync_status` - Status da sincronização
- `asaas_last_sync_at` - Data da última sincronização
- `asaas_last_error` - Último erro

**Nota sobre `past_due` vs `suspended_at`:**
O status `past_due` na subscription representa inadimplência lógica, mas o bloqueio efetivo de acesso é controlado exclusivamente via `tenant.suspended_at`. Uma subscription pode estar `active` ou `past_due`, mas o que determina se o tenant tem acesso ao sistema é o campo `suspended_at` do tenant.

**Tabela `invoices`:**
- `provider` - Provedor (`asaas`)
- `provider_id` - ID no Asaas (pode ser subscription_id ou payment_id)
- `asaas_payment_id` - ID específico do pagamento
- `payment_link` - Link de pagamento gerado
- `payment_method` - Método de pagamento
- `status` - Status (`pending`, `paid`, `overdue`, `canceled`)
- `paid_at` - Data e hora em que a fatura foi paga
- `notified_upcoming_at` - Data da última notificação preventiva enviada (deduplicação)
- `is_recovery` - Indica se é invoice de recovery (boolean)
- `recovery_origin_subscription_id` - ID da subscription original que deu origem ao recovery
- `recovery_target_subscription_id` - ID da subscription recovery_pending vinculada
- `asaas_payment_link_id` - ID do payment link do Asaas (para recovery)
- `asaas_recovery_subscription_id` - ID da nova assinatura criada no Asaas após pagamento do recovery
- `asaas_synced` - Se está sincronizado
- `asaas_sync_status` - Status da sincronização
- `asaas_last_sync_at` - Data da última sincronização
- `asaas_last_error` - Último erro

---

## 🔄 Fluxo de Criação de Assinaturas

### 1. Criação Manual (via Platform)

**Rota:** `POST /Platform/subscriptions`

**Fluxo:**

1. **Validação dos Dados**
   - Tenant selecionado
   - Plano válido e ativo
   - Datas de início/fim
   - Método de pagamento

2. **Criação Local**
   - Cria registro em `subscriptions`
   - Define status inicial (`pending` ou `trialing`)
   - Calcula data de término baseado no período do plano

3. **Aplicação de Regras de Acesso**
   - Busca `PlanAccessRule` do plano
   - Aplica limites (usuários, médicos) ao tenant
   - Salva funcionalidades permitidas

4. **Sincronização com Asaas**
   - Chama `syncWithAsaas($subscription)`
   - Verifica/cria cliente no Asaas
   - Cria assinatura ou fatura conforme método de pagamento

### 2. Sincronização Automática (`syncWithAsaas`)

O método `syncWithAsaas()` é responsável por sincronizar a assinatura local com o Asaas. Ele segue este fluxo:

```php
1. Verifica/Cria Cliente no Asaas
   ├─ Se não tem asaas_customer_id:
   │  ├─ Busca por email
   │  └─ Se não encontra, cria novo cliente
   │
2. Verifica Método de Pagamento e Auto-Renovação
   ├─ CREDIT_CARD + auto_renew = true:
   │  ├─ Cria assinatura recorrente no Asaas
   │  ├─ Cria Payment Link para checkout
   │  └─ Cria fatura local vinculada
   │
   ├─ PIX + auto_renew = true:
   │  ├─ Cria cobrança PIX única
   │  └─ Cria fatura local
   │
   └─ Outros casos:
      ├─ Se tinha assinatura no Asaas: cancela
      └─ Marca como não sincronizado
```

---

## 🏢 Planos Contratuais (Rede de Clínicas)

Tenants vinculados a uma **Rede de Clínicas** utilizam planos da categoria `contractual`. Para estes casos, as regras de pagamento do sistema são ignoradas:

1.  **Sem Assinatura (Subscription)**: O sistema não cria registros na tabela `subscriptions` para estes tenants. O acesso é liberado diretamente via `plan_id` no model `Tenant`.
2.  **Sem Cobrança Automática**: O Asaas não é utilizado para gerenciar faturas recorrentes destes tenants. A gestão financeira entre a rede e as clínicas é feita de forma externa ao sistema de pagamentos automatizado.
3.  **Liberação de Funcionalidades**: O `FeatureAccessService` reconhece o plano contratual e libera os limites e features configurados normalmente.

---

## 🔧 Regras Críticas de Billing

### Separação de Autoridade por Método de Pagamento

O sistema implementa uma separação clara de responsabilidades baseada no método de pagamento:

#### 💳 Cartão de Crédito/Débito: Asaas é Autoridade Total

- **Asaas controla completamente o ciclo de cobrança**
- O sistema **NÃO recalcula** ciclos localmente para pagamentos de cartão
- Faturas de cartão **NÃO influenciam** o cálculo de vencimento
- Faturas de cartão **NÃO geram** notificações preventivas
- O webhook apenas atualiza status, mas não recalcula `ends_at` ou `billing_anchor_date`

#### 💰 PIX/Boleto: Platform é Autoridade Total

- **Platform controla** a geração e cálculo de faturas
- O sistema **recalcula ciclos** apenas se `paid_at > due_date`
- Faturas são geradas automaticamente X dias antes do vencimento
- Notificações preventivas são enviadas Y dias antes do vencimento
- O `billing_anchor_date` é usado como referência para cálculo de próximos vencimentos

### Regra de Suspensão por Inadimplência

**⚠️ REGRA GLOBAL OBRIGATÓRIA:**

O sistema **NÃO adota período de tolerância** para inadimplência. Qualquer fatura vencida (`status = 'overdue'`) causa **suspensão imediata** do tenant, independentemente do método de pagamento.

**Comportamento:**
- **Suspensão:** Imediata quando `due_date` passa e fatura não foi paga
- **Reativação:** Apenas após confirmação de pagamento via webhook (`PAYMENT_RECEIVED` ou `PAYMENT_CONFIRMED`)
- **Sem exceções:** Não existe prazo de tolerância, contagem de dias em atraso ou exceção por método de pagamento

**Implementação:**
- Comando `invoices:invoices-check-overdue` marca faturas como `overdue` e suspende tenants imediatamente
- Webhook `PAYMENT_OVERDUE` suspende tenant imediatamente
- Webhook `PAYMENT_RECEIVED`/`PAYMENT_CONFIRMED` reativa tenant automaticamente

**Resultado:**
- Código mais simples e previsível
- Nenhuma ambiguidade ou edge case jurídico
- Comportamento alinhado com SaaS profissional

### Fluxo de Recovery para Assinaturas de Cartão

**Quando aplicar:** Assinaturas de cartão suspensas há ≥ 5 dias

**Importante:**
O recovery não é renegociação nem ajuste proporcional. Trata-se de cancelamento da assinatura quebrada e criação de um novo ciclo, iniciando na data do pagamento do recovery.

**Fluxo completo:**

1. **Detecção e Início do Recovery:**
   - Comando `subscriptions:process-recovery` seleciona **subscriptions** (não tenants) de cartão suspensas há ≥ 5 dias
   - **Idempotência:** Verifica se já existe subscription `recovery_pending` ou invoice recovery pendente para o tenant
   - Se já existe, ignora (evita múltiplos links e subscriptions)
   - Cancela assinatura no Asaas (`deleteSubscription`)
   - Encerra assinatura local (status = `canceled`, remove `asaas_subscription_id`)
   - Cria nova assinatura com status `recovery_pending`
   - Marca `recovery_started_at` na subscription (não no tenant)
   - Gera link de pagamento único (DETACHED, não recorrente)
   - **externalReference padronizado:** Sempre o ID da subscription `recovery_pending`
   - Cria invoice de recovery com vínculos:
     - `is_recovery = true`
     - `recovery_origin_subscription_id` = subscription original cancelada
     - `recovery_target_subscription_id` = subscription recovery_pending
     - `asaas_payment_link_id` = ID do payment link
   - Envia link ao cliente via WhatsApp

2. **Cliente tem 5 dias para pagar:**
   - Link de pagamento válido por 5 dias
   - Se não pagar: assinatura e tenant são cancelados automaticamente

3. **Pagamento confirmado (webhook):**
   - Webhook `PAYMENT_CONFIRMED` busca invoice por `externalReference` (ID da subscription recovery_pending)
   - Detecta invoice de recovery (`is_recovery = true`)
   - **Cria nova assinatura recorrente no Asaas:**
     - `nextDueDate` baseado na data do pagamento (`paymentDate` do webhook)
     - Ciclo mensal a partir da data do pagamento
   - Ativa assinatura local (status = `active`)
   - Atualiza invoice com `asaas_recovery_subscription_id` (ID da nova assinatura criada)
   - Limpa `recovery_started_at` da subscription
   - Reativa tenant automaticamente e limpa `suspended_at`
   - **Garantia:** Assinaturas antigas NÃO são reutilizadas (removido `asaas_subscription_id` antes de criar nova)

4. **Purga de dados:**
   - Comando `tenants:purge-canceled` remove tenants cancelados há ≥ 90 dias
   - **Proteções:** Verifica se não tem assinaturas ativas/pendentes ou invoices pendentes antes de purgar
   - **Opção `--dry-run`:** Simula purga sem fazer alterações
   - Remove banco de dados do tenant
   - Remove todas as assinaturas e faturas

**Campos adicionados:**
- `suspended_at` (tenants): Data da suspensão
- `canceled_at` (tenants): Data do cancelamento
- `recovery_started_at` (subscriptions): Data de início do recovery (consolidado apenas na subscription)
- `is_recovery` (invoices): Indica se é invoice de recovery
- `recovery_origin_subscription_id` (invoices): Subscription original cancelada
- `recovery_target_subscription_id` (invoices): Subscription recovery_pending vinculada
- `asaas_payment_link_id` (invoices): ID do payment link do Asaas
- `asaas_recovery_subscription_id` (invoices): ID da nova assinatura criada após pagamento

**Proibições:**
- ❌ Não recalcular vencimento de cartão
- ❌ Não reaproveitar assinatura Asaas quebrada
- ❌ Não ajustar ciclo manualmente
- ❌ Não gerar cobrança recorrente durante recovery
- ❌ Não criar múltiplos recoveries para o mesmo tenant (idempotência)

### Campo `billing_anchor_date`

O campo `billing_anchor_date` na tabela `subscriptions` armazena a data de referência para cálculo de vencimento das faturas:

- **Quando definido:** Usado como base para calcular próximos vencimentos
- **Quando não definido:** Usa `ends_at` ou data atual como fallback
- **Atualização:** Atualizado para `paid_at->toDateString()` quando um pagamento PIX/Boleto é recebido após o vencimento (`paid_at > due_date`)

### Campo `paid_at` em Invoices

O campo `paid_at` na tabela `invoices` armazena a data e hora exata do pagamento:

- **Uso:** Comparado com `due_date` para decidir se o ciclo deve ser recalculado
- **Regra:** Ciclo só é recalculado se `paid_at > due_date` (pagamento após vencimento)
- **Aplicação:** Apenas para PIX/Boleto
- **Fonte:** Definido via webhook `PAYMENT_RECEIVED`/`PAYMENT_CONFIRMED` usando `paymentDate` do payload

### Campo `notified_upcoming_at` em Invoices

O campo `notified_upcoming_at` na tabela `invoices` armazena a data da última notificação preventiva enviada:

- **Uso:** Deduplicação de notificações (evita enviar múltiplas notificações no mesmo dia)
- **Atualização:** Marcado quando notificação preventiva é enviada com sucesso
- **Verificação:** Comando `invoices:notify-upcoming` verifica se já foi notificado hoje antes de enviar

---

## 💳 Tipos de Pagamento

### 1. Cartão de Crédito com Auto-Renovação

**Quando usar:** `payment_method = 'CREDIT_CARD'` e `auto_renew = true`

**Fluxo:**

1. **Criação da Assinatura no Asaas**
   ```php
   POST /subscriptions
   {
     "customer": "cus_xxx",
     "billingType": "CREDIT_CARD",
     "value": 99.90,
     "cycle": "MONTHLY",
     "nextDueDate": "2025-01-15",
     "description": "Assinatura do plano Premium"
   }
   ```

2. **Criação do Payment Link**
   ```php
   POST /paymentLinks
   {
     "name": "Assinatura SaaS - Premium",
     "billingType": "CREDIT_CARD",
     "chargeType": "RECURRENT",
     "subscription": "sub_xxx",
     "value": 99.90,
     "dueDateLimitDays": 5
   }
   ```

3. **Resultado:**
   - Assinatura recorrente criada no Asaas
   - Payment Link gerado para checkout inicial
   - Fatura local criada com link de pagamento
   - Asaas gerará faturas automaticamente a cada ciclo
   - **Asaas é autoridade total** - sistema não recalcula ciclos localmente

**Vantagens:**
- Renovação automática
- Cliente não precisa pagar manualmente todo mês
- Asaas gerencia as cobranças recorrentes

### 2. PIX com Auto-Renovação

**Quando usar:** `payment_method = 'PIX'` e `auto_renew = true`

**Fluxo:**

1. **Criação da Cobrança PIX**
   ```php
   POST /payments
   {
     "customer": "cus_xxx",
     "billingType": "PIX",
     "dueDate": "2025-01-20",
     "value": 99.90,
     "description": "Assinatura do plano Premium",
     "externalReference": "subscription_id"
   }
   ```

2. **Resultado:**
   - Cobrança PIX única criada
   - Fatura local criada com link de pagamento
   - Cliente recebe QR Code PIX
   - **Faturas futuras são geradas automaticamente** via comando `invoices:generate`
   - **Platform é autoridade total** - sistema controla geração e cálculo de ciclos

**Vantagens:**
- Pagamento instantâneo
- Sem taxas de cartão
- Ideal para clientes que preferem PIX
- Geração automática de faturas X dias antes do vencimento

**Regras Especiais:**
- Faturas são geradas automaticamente pelo comando `invoices:generate`
- Ciclo é recalculado apenas se pagamento ocorrer após o vencimento (`paid_at > due_date`)
- Notificações preventivas são enviadas Y dias antes do vencimento

### 3. Outros Métodos

- **Boleto Bancário**: Similar ao PIX, cria cobrança única
- **Cartão de Débito**: Similar ao cartão de crédito, mas sem renovação automática

---

## 📡 Webhooks do Asaas

### Configuração da Rota

**Rota:** `POST /webhook/asaas`

**Middleware:** `verify.asaas.token` - Valida o token de segurança do webhook

### Processamento de Eventos

O `AsaasWebhookController` processa os seguintes eventos:

#### Eventos de Assinatura

**`SUBSCRIPTION_CREATED`**
- Vincula `asaas_subscription_id` à assinatura local
- Atualiza status para `pending`
- Cria notificação do sistema

**`SUBSCRIPTION_UPDATED`**
- Atualiza status de sincronização
- Cria notificação do sistema

**`SUBSCRIPTION_INACTIVATED`**
- Atualiza status da assinatura para `pending`
- Cria notificação de aviso

**`SUBSCRIPTION_DELETED`**
- Remove assinatura local e todas as faturas vinculadas
- Cria notificação de aviso

#### Eventos de Pagamento

**`PAYMENT_CREATED`**
- Se vinculado a uma assinatura, cria fatura local automaticamente
- Vincula `asaas_payment_id` à fatura
- Cria notificação do sistema

**`PAYMENT_RECEIVED` / `PAYMENT_CONFIRMED`**
- Busca invoice por `asaas_payment_id` ou `externalReference` (padronizado: ID da subscription recovery_pending)
- **Idempotência:** Se a invoice já estiver com `status = 'paid'`, o webhook retorna sucesso sem reprocessar, evitando criação duplicada de assinaturas. Isso é importante para retries do Asaas.
- Atualiza status da fatura para `paid`
- Define `paid_at` com a data/hora do pagamento (do payload `payment.paymentDate` ou `now()`)
- Se assinatura estava `pending`, atualiza para `active`
- **Se assinatura estava `recovery_pending` (cartão):**
  - Detecta invoice de recovery (`is_recovery = true`)
  - Cria nova assinatura recorrente no Asaas:
    - `nextDueDate` baseado na data do pagamento (`paymentDate` → `nextDueDate`)
    - Ciclo mensal a partir da data do pagamento
    - **NUNCA reutiliza assinatura antiga** (removido `asaas_subscription_id` antes)
  - Ativa assinatura local (status = `active`)
  - Atualiza invoice com `asaas_recovery_subscription_id` (ID da nova assinatura criada)
  - Limpa `recovery_started_at` da subscription (não do tenant)
  - Reativa tenant automaticamente e limpa `suspended_at`
- **REGRA CRÍTICA:** Só recalcula ciclo se:
  - Método de pagamento é PIX ou Boleto (`CREDIT_CARD` e `DEBIT_CARD` são ignorados)
  - `paid_at > due_date` (pagamento após vencimento)
  - Quando recalculado, atualiza `billing_anchor_date = paid_at->toDateString()`
- Se tenant estava suspenso (e não é recovery), reativa automaticamente e limpa `suspended_at`
- Cria notificação de sucesso

**`PAYMENT_OVERDUE`**
- Atualiza status da fatura para `overdue`
- **Suspende tenant imediatamente** (sem período de carência)
- Marca `suspended_at` no tenant
- Cria notificação de aviso

**`PAYMENT_REFUNDED`**
- Atualiza status da fatura para `canceled`
- Cria notificação de aviso

**`PAYMENT_DELETED`**
- Remove fatura local
- Cria notificação de aviso

#### Eventos de Cliente

**`CUSTOMER_DELETED`**
- Remove `asaas_customer_id` do tenant
- Marca como não sincronizado
- Cria notificação de aviso

### Logs de Auditoria

Todos os webhooks recebidos são registrados na tabela `webhook_logs`:

```php
WebhookLog::create([
    'event' => $event,
    'payload' => json_encode($payload),
]);
```

### Tratamento de Erros

- Se ocorrer erro no processamento, as entidades são marcadas com:
  - `asaas_sync_status = 'failed'`
  - `asaas_last_error = mensagem_do_erro`
- Retorna HTTP 500 para o Asaas (que tentará reenviar)

---

## 🔄 Sincronização Manual

### Sincronizar Assinatura

**Rota:** `POST /Platform/subscriptions/{subscription}/sync`

**O que faz:**
- Reexecuta o fluxo de sincronização completo
- Verifica/cria cliente no Asaas
- Cria ou atualiza assinatura conforme método de pagamento
- Atualiza status de sincronização

**Quando usar:**
- Assinatura criada mas não sincronizou
- Mudança de método de pagamento
- Erro na sincronização inicial

### Sincronizar Fatura

**Rota:** `POST /Platform/invoices/{invoice}/sync`

**O que faz:**
- Busca status atualizado do pagamento no Asaas
- Atualiza status local (`pending`, `paid`, `overdue`, etc.)
- Atualiza link de pagamento se necessário

**Quando usar:**
- Fatura não atualizou após pagamento
- Verificar status manualmente
- Corrigir divergências

---

## 🔀 Mudança de Plano e Forma de Pagamento

### Solicitação de Mudança de Plano

Quando um tenant solicita mudança de plano através de `PlanChangeRequest`:

1. **Aprovação pelo Administrador**
   - Atualiza `plan_id` da assinatura
   - Aplica novas regras de acesso
   - Atualiza todas as faturas pendentes com novo valor

2. **Mudança de Forma de Pagamento**

   **PIX → Cartão de Crédito:**
   - Cancela assinatura PIX no Asaas (se existir)
   - Cria nova assinatura recorrente com cartão
   - Atualiza `asaas_subscription_id`
   - Define `auto_renew = true`

   **Cartão de Crédito → PIX:**
   - Cancela assinatura com cartão no Asaas
   - Remove `asaas_subscription_id`
   - Gera link de pagamento PIX para próxima fatura
   - Mantém `auto_renew = true` (para criar novas cobranças)

   **Outras Mudanças:**
   - Gera link de pagamento apropriado
   - Atualiza faturas pendentes

3. **Atualização no Asaas**
   - Se assinatura gerenciada por cartão:
     ```php
     updateSubscription($asaas_subscription_id, [
         'value' => $newPlan->price_cents / 100,
         'description' => "Assinatura do plano {$newPlan->name}",
         'updatePendingPayments' => true  // Atualiza cobranças pendentes
     ]);
     ```

4. **Atualização de Faturas Pendentes**
   - Todas as faturas com status `pending` ou `overdue` são atualizadas
   - Valor ajustado para novo plano
   - Se tiver `provider_id`, atualiza no Asaas também

---

## 📊 Gestão de Faturas

### Criação Automática

**Assinaturas com Cartão de Crédito:**
- Asaas cria faturas automaticamente a cada ciclo
- Webhook `PAYMENT_CREATED` cria fatura local
- **Sistema não interfere** - Asaas é autoridade total

**Assinaturas com PIX/Boleto:**
- Fatura inicial criada na sincronização
- **Novas faturas são geradas automaticamente** via comando `invoices:generate`
- Comando executa diariamente às 01:30
- Gera faturas **X dias antes do vencimento** (padrão: 5 dias)
- **Nunca gera no dia do vencimento** (sempre pelo menos 1 dia antes)

### Comandos Agendados

#### `invoices:generate`

**Descrição:** Gera faturas automaticamente X dias antes do vencimento (apenas PIX/Boleto)

**Agendamento:** Diariamente às 01:30

**Configuração:**
- `billing.invoice_days_before_due` (SystemSetting, default: 10)
- Configurável via interface administrativa em `/Platform/settings`

**Regras:**
- Apenas para assinaturas com `payment_method` = `PIX` ou `BOLETO`
- Ignora assinaturas de cartão (Asaas controla)
- **Idempotente:** Não cria se já existir invoice `pending`/`overdue` no mesmo período (mesmo `due_date`)
- Calcula próximo vencimento baseado em `billing_anchor_date` ou `ends_at`
- Nunca emite no dia do vencimento (sempre pelo menos 1 dia antes)
- O `InvoiceObserver` envia automaticamente para o Asaas

**Exemplo:**
```bash
php artisan invoices:generate
```

#### `invoices:notify-upcoming`

**Descrição:** Notifica tenants sobre faturas próximas do vencimento (exclui faturas de cartão)

**Agendamento:** Diariamente às 01:45

**Configuração:**
- `billing.notify_days_before_due` (SystemSetting, default: 5)
- Configurável via interface administrativa em `/Platform/settings`

**Regras:**
- Apenas para faturas com `payment_method` = `PIX` ou `BOLETO`
- **Exclui faturas de cartão** (CREDIT_CARD, DEBIT_CARD)
- **Não notifica** faturas com status `paid`, `canceled` ou `overdue`
- **Deduplicação:** Marca `notified_upcoming_at` ao enviar com sucesso
- Não envia se já foi notificado hoje (verifica `notified_upcoming_at`)
- Envia notificação via WhatsApp (se configurado)
- Verifica se tenant tem telefone cadastrado

**Exemplo:**
```bash
php artisan invoices:notify-upcoming
```

#### `subscriptions:process-recovery`

**Descrição:** Processa recovery de assinaturas de cartão após suspensão prolongada (≥ 5 dias)

**Agendamento:** Diariamente às 02:30

**Configuração:**
- `billing.recovery_days_after_suspension` (SystemSetting, default: 5)

**Regras:**
- **Seleciona subscriptions** (não tenants) de cartão suspensas há ≥ 5 dias
- Exige `recovery_started_at IS NULL` na subscription
- **Idempotência:** Verifica se já existe subscription `recovery_pending` ou invoice recovery pendente para o tenant
- Se já existe, ignora (evita múltiplos links e subscriptions)
- Cancela assinatura no Asaas (`deleteSubscription`)
- Encerra assinatura local (status = `canceled`, remove `asaas_subscription_id`)
- Cria nova assinatura com status `recovery_pending`
- Marca `recovery_started_at` na subscription (não no tenant)
- Gera link de pagamento único (DETACHED, não recorrente)
- **externalReference padronizado:** Sempre o ID da subscription `recovery_pending`
- Cria invoice de recovery com vínculos completos:
  - `is_recovery = true`
  - `recovery_origin_subscription_id` = subscription original cancelada
  - `recovery_target_subscription_id` = subscription recovery_pending
  - `asaas_payment_link_id` = ID do payment link
- Envia link ao cliente via WhatsApp
- Cancela recoveries não pagos em 5 dias (subscription e tenant cancelados)

**Exemplo:**
```bash
php artisan subscriptions:process-recovery
```

#### `tenants:purge-canceled`

**Descrição:** Remove dados e banco de dados de tenants cancelados há ≥ 90 dias (com proteções)

**Agendamento:** Diariamente às 03:00

**Configuração:**
- `billing.purge_days_after_cancellation` (SystemSetting, default: 90)

**Regras:**
- Busca tenants cancelados há ≥ 90 dias
- **Proteções obrigatórias:**
  - Verifica se não tem assinaturas ativas/pendentes (`active`, `pending`, `recovery_pending`)
  - Verifica se não tem invoices pendentes (`pending`, `overdue`)
  - Se tiver, ignora e registra no log
- Remove banco de dados do tenant (desconecta conexões antes)
- Remove todas as assinaturas e faturas (cascade)
- Logs detalhados com métricas (subscriptions removidas, invoices removidas)

**Opção `--dry-run`:**
- Simula purga sem fazer alterações
- Mostra o que seria removido
- Útil para verificação antes de executar

**Exemplo:**
```bash
# Execução normal
php artisan tenants:purge-canceled

# Simulação (dry-run)
php artisan tenants:purge-canceled --dry-run
```

### Criação Manual

**Rota:** `POST /Platform/invoices`

**Fluxo:**
1. Seleciona tenant ou assinatura
2. Define valor, data de vencimento, descrição
3. Cria fatura local
4. Tenta sincronizar automaticamente com Asaas
5. Envia notificação via WhatsApp (se configurado)

### Atualização de Faturas

**Quando uma fatura é atualizada:**
- Se mudou o valor do plano, faturas pendentes são atualizadas
- Se tem `provider_id`, atualiza no Asaas via `updatePayment()`
- Atualiza link de pagamento se necessário

### Status das Faturas

- **`pending`**: Aguardando pagamento
- **`paid`**: Paga
- **`overdue`**: Vencida
- **`canceled`**: Cancelada/estornada

---

## ⚠️ Tratamento de Erros

### Erros de API

**Erro ao Criar Cliente:**
- Retorna erro com mensagem do Asaas
- Marca assinatura como `asaas_sync_status = 'failed'`
- Salva mensagem de erro em `asaas_last_error`

**Erro ao Criar Assinatura:**
- Marca como não sincronizado
- Assinatura local permanece criada
- Administrador pode tentar sincronizar manualmente

**Erro ao Atualizar:**
- Loga o erro
- Mantém dados locais
- Permite nova tentativa de sincronização

### Erros de Webhook

**Webhook Inválido:**
- Retorna HTTP 400
- Registra no log
- Não processa o evento

**Erro no Processamento:**
- Retorna HTTP 500
- Asaas tentará reenviar
- Marca entidades com erro
- Registra erro detalhado no log

### Recuperação de Erros

1. **Sincronização Manual**: Administrador pode forçar nova sincronização
2. **Logs**: Todos os erros são registrados com detalhes
3. **Notificações**: Sistema notifica sobre erros críticos

---

## 📝 Logs e Auditoria

### Logs do Sistema

Todos os eventos são registrados no log do Laravel:

```php
Log::info('📡 Asaas createSubscription resposta:', $response);
Log::error('❌ Erro ao criar assinatura Asaas', ['error' => $e->getMessage()]);
Log::warning('⚠️ Fatura vencida', ['invoice_id' => $invoice->id]);
```

### Logs de Webhook

Todos os webhooks recebidos são salvos em `webhook_logs`:

```sql
SELECT * FROM webhook_logs 
WHERE event = 'PAYMENT_CONFIRMED' 
ORDER BY created_at DESC;
```

### Campos de Auditoria

Cada entidade possui campos de auditoria:
- `asaas_synced` - Se está sincronizado
- `asaas_sync_status` - Status da sincronização
- `asaas_last_sync_at` - Última sincronização
- `asaas_last_error` - Último erro (se houver)

---

## 🔧 Troubleshooting

### Assinatura não sincroniza

**Sintomas:**
- `asaas_synced = false`
- `asaas_sync_status = 'pending'` ou `'failed'`

**Soluções:**
1. Verificar configuração do Asaas (API Key, URL)
2. Verificar se cliente existe no Asaas
3. Tentar sincronização manual
4. Verificar logs para erro específico

### Fatura não atualiza após pagamento

**Sintomas:**
- Fatura permanece `pending` mesmo após pagamento
- Webhook não foi recebido

**Soluções:**
1. Verificar se webhook está configurado corretamente
2. Verificar logs de webhook (`webhook_logs`)
3. Sincronizar fatura manualmente
4. Verificar se URL do webhook está acessível

### Webhook não chega

**Sintomas:**
- Eventos no Asaas não refletem no sistema
- Logs de webhook vazios

**Soluções:**
1. Verificar URL do webhook no Asaas
2. Verificar se middleware `verify.asaas.token` está funcionando
3. Verificar se servidor está acessível publicamente
4. Testar webhook manualmente (usar ferramenta como ngrok para desenvolvimento)

### Assinatura cancelada no Asaas mas não localmente

**Sintomas:**
- Assinatura existe localmente mas não no Asaas
- `asaas_subscription_id` aponta para assinatura inexistente

**Soluções:**
1. Verificar se webhook `SUBSCRIPTION_DELETED` foi recebido
2. Se não, cancelar manualmente no Asaas ou remover `asaas_subscription_id` localmente
3. Criar nova assinatura se necessário

### Faturas duplicadas

**Sintomas:**
- Múltiplas faturas para mesmo período
- Webhook `PAYMENT_CREATED` criando faturas duplicadas

**Soluções:**
1. Verificar se existe validação de duplicidade (verifica `asaas_payment_id`)
2. Remover faturas duplicadas manualmente
3. Verificar logs para identificar causa

### Casos de Borda

#### Meses Curtos (Fevereiro, meses com 30 dias)

**Problema:** Quando `billing_anchor_date` é dia 31 e o próximo mês tem menos dias.

**Solução:**
- O sistema usa `Carbon::addMonths()` que automaticamente ajusta para o último dia válido do mês
- Exemplo: Se `billing_anchor_date = 2025-01-31`, próximo vencimento será `2025-02-28` (não 31)

#### Deduplicação de Notificações

**Problema:** Múltiplas execuções do comando `invoices:notify-upcoming` no mesmo dia.

**Solução:**
- Campo `notified_upcoming_at` armazena a data da última notificação
- Comando verifica se `notified_upcoming_at` é hoje antes de enviar
- Se já foi notificado hoje, ignora a fatura

#### Pagamento em Atraso

**Problema:** Pagamento recebido após o vencimento (`paid_at > due_date`).

**Comportamento:**
- Para PIX/Boleto: Recalcula ciclo usando `paid_at` como novo `billing_anchor_date`
- Para Cartão: Ignora (Asaas controla)
- Próxima fatura será gerada baseada no novo `billing_anchor_date`

#### Faturas Geradas no Dia do Vencimento

**Problema:** Comando `invoices:generate` executado no dia do vencimento.

**Solução:**
- Sistema verifica se `issueDate` (vencimento - X dias) é igual ao vencimento
- Se for igual, ajusta para 1 dia antes automaticamente
- Garante que nunca gera no dia do vencimento

#### Assinatura sem `billing_anchor_date`

**Problema:** Assinatura antiga sem `billing_anchor_date` definido.

**Solução:**
- Sistema usa `ends_at` como fallback
- Se `ends_at` também não existir, usa data atual
- Após primeiro pagamento em atraso, `billing_anchor_date` será definido

---

## 📚 Referências

### Documentação Asaas

- [API de Assinaturas](https://docs.asaas.com/reference/criar-assinatura)
- [API de Pagamentos](https://docs.asaas.com/reference/criar-cobranca)
- [API de Clientes](https://docs.asaas.com/reference/criar-cliente)
- [Webhooks](https://docs.asaas.com/reference/webhooks)

### Arquivos do Sistema

- `app/Services/AsaasService.php` - Serviço de integração
- `app/Http/Controllers/Platform/SubscriptionController.php` - Controller de assinaturas
- `app/Http/Controllers/Webhook/AsaasWebhookController.php` - Controller de webhooks
- `app/Models/Platform/Subscription.php` - Model de assinatura
- `app/Models/Platform/Invoices.php` - Model de fatura

---

---

## 🔄 Comandos Agendados (Cron Jobs)

O sistema possui os seguintes comandos agendados para gestão automática de faturas:

| Comando | Horário | Descrição |
|---------|---------|-----------|
| `subscriptions:subscriptions-process` | 01:00 | Processa assinaturas vencidas |
| `invoices:generate` | 01:30 | Gera faturas X dias antes do vencimento (PIX/Boleto) |
| `invoices:notify-upcoming` | 01:45 | Notifica sobre faturas próximas do vencimento |
| `invoices:invoices-check-overdue` | 02:00 | Marca faturas vencidas e suspende tenants imediatamente (sem período de carência) |
| `subscriptions:process-recovery` | 02:30 | Processa recovery de assinaturas de cartão suspensas ≥ 5 dias |
| `tenants:purge-canceled` | 03:00 | Remove dados e banco de tenants cancelados há ≥ 90 dias |

**Configuração:** `app/Console/Kernel.php`

---

**Última atualização:** 2025-12-14

**Nota:** Esta documentação reflete o estado atual do sistema de pagamentos. Para atualizações futuras, consulte os logs de commit e as mudanças no código.

### Mudanças Recentes (2025-12-14)

- ✅ Implementado `billing_anchor_date` em subscriptions (DATE)
- ✅ Implementado `paid_at` em invoices (DATETIME)
- ✅ Implementado `notified_upcoming_at` em invoices (DATETIME) para deduplicação
- ✅ Separação de autoridade por método de pagamento (Cartão: Asaas, PIX/Boleto: Platform)
- ✅ Webhook atualizado: só recalcula ciclo se `paid_at > due_date` e apenas para PIX/Boleto
- ✅ Webhook atualiza `billing_anchor_date = paid_at->toDateString()` quando recalculado
- ✅ Comando `invoices:generate` para emissão automática de faturas (configurável via SystemSetting)
- ✅ Comando `invoices:notify-upcoming` para notificações preventivas (configurável via SystemSetting)
- ✅ Idempotência melhorada: verifica invoices `pending`/`overdue` no mesmo período
- ✅ Deduplicação de notificações via `notified_upcoming_at`
- ✅ Configurações via SystemSetting: `billing.invoice_days_before_due` (default: 10) e `billing.notify_days_before_due` (default: 5)
- ✅ **Suspensão imediata por inadimplência:** Removido período de carência - suspensão ocorre imediatamente quando fatura vence
- ✅ **Reativação automática:** Tenant reativado automaticamente após confirmação de pagamento via webhook
- ✅ **Recovery de assinaturas de cartão:** Fluxo completo de cancelamento e recriação após suspensão ≥ 5 dias
- ✅ **Recovery idempotente:** Impede múltiplos links e subscriptions recovery_pending por tenant
- ✅ **Recovery consolidado:** `recovery_started_at` apenas na subscription (não no tenant)
- ✅ **externalReference padronizado:** Sempre o ID da subscription recovery_pending
- ✅ **Vínculos de recovery:** Campos `is_recovery`, `recovery_origin_subscription_id`, `recovery_target_subscription_id`, `asaas_payment_link_id`, `asaas_recovery_subscription_id` em invoices
- ✅ **Webhook recovery:** Cria assinatura recorrente com ciclo baseado na data do pagamento (`paymentDate` → `nextDueDate`)
- ✅ **Purga de tenants cancelados:** Remoção automática de dados e banco após 90 dias de cancelamento
- ✅ **Purga com proteções:** Verifica assinaturas ativas/pendentes e invoices pendentes antes de purgar
- ✅ **Purga com --dry-run:** Opção para simular purga sem fazer alterações
- ✅ **Campos de auditoria:** `suspended_at`, `canceled_at` (tenants), `recovery_started_at` (subscriptions) para rastreamento completo

