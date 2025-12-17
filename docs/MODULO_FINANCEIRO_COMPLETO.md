# 💰 Módulo Financeiro Completo - Documentação Técnica

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquitetura](#arquitetura)
3. [Instalação e Configuração](#instalação-e-configuração)
4. [Funcionalidades](#funcionalidades)
5. [Segurança](#segurança)
6. [Hardening de Produção](#hardening-de-produção)
7. [Conciliação Automática](#conciliação-automática)
8. [Relatórios](#relatórios)
9. [Go-Live](#go-live)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

O Módulo Financeiro é um sistema completo e opcional para gestão financeira de tenants, incluindo:

- ✅ Gestão de contas e categorias
- ✅ Transações financeiras
- ✅ Cobranças automáticas via Asaas
- ✅ Comissões médicas
- ✅ Relatórios e dashboards
- ✅ Conciliação automática
- ✅ Auditoria completa

### Características Principais

- **Opcional**: Pode ser habilitado/desabilitado por tenant
- **Isolado**: Zero impacto quando desabilitado
- **Multi-tenant**: Cada tenant tem sua própria configuração
- **Seguro**: Hardening completo para produção
- **Auditável**: Rastreabilidade total

---

## 🏗️ Arquitetura

### Estrutura de Tabelas

```
financial_accounts          # Contas financeiras
financial_categories         # Categorias (receita/despesa)
financial_transactions      # Transações financeiras
financial_charges           # Cobranças de agendamentos
doctor_commissions          # Comissões médicas
asaas_webhook_events        # Auditoria de webhooks
```

### Serviços

```
AsaasService                    # Integração com Asaas
AsaasWebhookProcessor          # Processamento de webhooks
ChargeReconciliationService    # Conciliação de cobranças
TransactionReconciliationService # Conciliação de transações
CommissionReconciliationService  # Conciliação de comissões
FinanceHealthCheckService      # Health checks
FinanceRedirectService         # Lógica de redirecionamento
```

### Observers

```
AppointmentFinanceObserver     # Cria cobranças automaticamente
```

---

## ⚙️ Instalação e Configuração

### 1. Migrations

```bash
php artisan tenants:migrate
```

### 2. Habilitar Módulo

```php
TenantSetting::set('finance.enabled', 'true');
```

### 3. Configurar Asaas

```php
TenantSetting::set('finance.asaas.environment', 'production');
TenantSetting::set('finance.asaas.api_key', 'sua_api_key');
TenantSetting::set('finance.asaas.webhook_secret', 'seu_secret');
```

### 4. Configurar Webhook no Asaas

- URL: `https://seu-dominio.com/t/{tenant}/webhooks/asaas`
- Secret: Mesmo valor de `finance.asaas.webhook_secret`

---

## 🎨 Funcionalidades

### CRUDs Completos

- **Contas Financeiras**: Gestão de contas (dinheiro, banco, PIX, crédito)
- **Categorias**: Categorias de receita e despesa
- **Transações**: Entrada e saída de valores
- **Cobranças**: Gestão de cobranças de agendamentos
- **Comissões**: Comissões médicas

### Fluxo Automático

1. Agendamento criado → Observer cria cobrança (se configurado)
2. Link de pagamento enviado → Email/WhatsApp
3. Pagamento realizado → Webhook recebido
4. Conciliação automática → Charge + Transaction + Commission
5. Notificações → Paciente e médico

### Relatórios

- Dashboard financeiro
- Fluxo de caixa
- Receitas x Despesas
- Cobranças
- Pagamentos recebidos
- Comissões médicas

---

## 🔐 Segurança

### Webhooks

- ✅ Rate limit: 60 req/min por IP
- ✅ Secret validation: `hash_equals()` seguro
- ✅ IP whitelist: Opcional e configurável
- ✅ Idempotência: Eventos nunca processados duas vezes

### Dados Sensíveis

- ✅ Tokens mascarados em logs
- ✅ Secrets criptografados
- ✅ URLs sanitizadas

### Acesso

- ✅ Middleware `module.access:finance`
- ✅ Verificação de `finance.enabled`
- ✅ Filtros por role (admin, doctor, user)

---

## 🛡️ Hardening de Produção

### Middlewares de Segurança

- `ThrottleAsaasWebhook` - Rate limiting
- `VerifyAsaasWebhookSecret` - Validação de secret
- `VerifyAsaasWebhookIpWhitelist` - Whitelist de IPs

### Health Checks

```bash
php artisan finance:health-check
php artisan finance:health-check --tenant=clinic-slug
php artisan finance:health-check --json
```

### Feature Flags

- `finance.webhook_enabled` - Kill switch para webhooks
- `finance.auto_commission_enabled` - Comissões automáticas
- `finance.auto_transaction_enabled` - Transações automáticas

### Logs Estruturados

- Canal: `finance`
- Rotação: 30 dias
- Contexto: tenant, charge_id, payment_id, appointment_id, event_type

---

## 🔄 Conciliação Automática

### Processamento de Webhooks

1. Webhook recebido → Validações
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

---

## 📊 Relatórios

### Dashboard Financeiro

- Cards de resumo (receitas, despesas, saldo, pendências)
- Gráfico de receitas (12 meses)
- Gráfico de receitas por categoria

### Relatórios Disponíveis

1. **Fluxo de Caixa**: Transações com saldo acumulado
2. **Receitas x Despesas**: Comparativo com gráficos
3. **Cobranças**: Status, origem, período
4. **Pagamentos Recebidos**: Lista de pagamentos confirmados
5. **Comissões**: Comissões médicas por período

### Exportações

- CSV (todos os relatórios)
- UTF-8 com BOM
- Formatação brasileira

---

## 🚀 Go-Live

### Checklist Completo

Ver `docs/FINANCE_GO_LIVE_CHECKLIST.md` para checklist detalhado.

### Pré-requisitos

- [ ] Fila finance ativa
- [ ] SSL ativo
- [ ] Rate limit aplicado
- [ ] Asaas configurado
- [ ] Webhook testado
- [ ] Comandos testados

---

## 🔧 Troubleshooting

### Webhook não recebido

1. Verificar `finance.enabled = true`
2. Verificar `finance.webhook_enabled = true`
3. Verificar secret no Asaas
4. Verificar URL do webhook
5. Verificar logs: `storage/logs/finance.log`

### Cobrança não criada

1. Verificar `finance.billing_mode`
2. Verificar `finance.charge_on_*` settings
3. Verificar origem do agendamento
4. Verificar logs do Observer

### Transação não criada

1. Verificar se charge está paga
2. Verificar se já existe transação
3. Verificar logs de conciliação
4. Executar `finance:reconcile`

### Comissão não criada

1. Verificar `finance.doctor_commission_enabled`
2. Verificar `finance.default_commission_percentage`
3. Verificar se agendamento tem médico
4. Verificar logs de conciliação

---

## 📞 Suporte

Para problemas ou dúvidas:

1. Verificar logs: `storage/logs/finance.log`
2. Executar health check: `php artisan finance:health-check`
3. Executar reconciliação: `php artisan finance:reconcile`
4. Consultar documentação: Este arquivo

---

**Última atualização**: Janeiro 2025
**Versão**: 1.0.0

