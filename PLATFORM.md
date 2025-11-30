# 🏢 Documentação - Área Platform (Administrativa)

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Acesso e Autenticação](#acesso-e-autenticação)
3. [Estrutura de Rotas](#estrutura-de-rotas)
4. [Controllers](#controllers)
5. [Models](#models)
6. [Funcionalidades Principais](#funcionalidades-principais)
7. [Integrações](#integrações)
8. [Guia de Uso](#guia-de-uso)

---

## 🎯 Visão Geral

A **Platform** é a área administrativa central do sistema SaaS de agendamento médico. É responsável por gerenciar todos os aspectos administrativos da plataforma, incluindo:

- ✅ Gerenciamento de tenants (clínicas)
- ✅ Gestão de planos de assinatura
- ✅ Controle de assinaturas e renovações
- ✅ Gerenciamento de faturas
- ✅ Sistema de notificações
- ✅ Catálogo de especialidades médicas
- ✅ Gestão de usuários administrativos
- ✅ Configurações do sistema
- ✅ Integração com gateway de pagamento (Asaas)
- ✅ Envio de mensagens WhatsApp
- ✅ Monitor de kiosk

### Banco de Dados

A Platform utiliza o **banco central (landlord)**, que armazena:
- Dados dos tenants
- Planos e assinaturas
- Faturas
- Usuários administrativos
- Configurações do sistema
- Catálogo de especialidades médicas
- Dados de localização (países, estados, cidades)

---

## 🔐 Acesso e Autenticação

### URL de Acesso

```
http://localhost/Platform/dashboard
```

### Autenticação

- **Guard**: `web`
- **Model**: `App\Models\Platform\User`
- **Middleware**: `auth` (obrigatório para todas as rotas)

### Controle de Acesso

Os usuários da Platform possuem um campo `modules` (JSON) que define quais módulos podem acessar:

- `tenants` - Gerenciamento de tenants
- `plans` - Gerenciamento de planos
- `subscriptions` - Gerenciamento de assinaturas
- `invoices` - Gerenciamento de faturas
- `users` - Gerenciamento de usuários
- `settings` - Configurações do sistema
- `notifications` - Notificações
- `medical_specialties_catalog` - Catálogo de especialidades
- `notifications_outbox` - Histórico de notificações enviadas
- `system_notifications` - Notificações do sistema
- `locations` - Gerenciamento de localização (países, estados, cidades)

O middleware `module.access:{modulo}` verifica o acesso antes de permitir a rota.

---

## 🛣️ Estrutura de Rotas

Todas as rotas da Platform utilizam o prefixo `/Platform`:

```php
/Platform/dashboard                    # Dashboard principal
/Platform/tenants                      # CRUD de tenants
/Platform/plans                        # CRUD de planos
/Platform/subscriptions                # CRUD de assinaturas
/Platform/invoices                     # CRUD de faturas
/Platform/users                        # CRUD de usuários da platform
/Platform/settings                     # Configurações do sistema
/Platform/profile                      # Perfil do usuário logado
/Platform/system_notifications         # Notificações do sistema
/Platform/notifications_outbox         # Histórico de notificações enviadas
/Platform/medical_specialties_catalog   # Catálogo de especialidades
/kiosk/monitor                         # Monitor de kiosk (sem prefixo Platform)
```

### Rotas Especiais

```php
POST /Platform/tenants/{tenant}/sync                    # Sincronizar tenant com Asaas
POST /Platform/subscriptions/{id}/renew                 # Renovar assinatura
POST /Platform/subscriptions/{subscription}/sync        # Sincronizar assinatura com Asaas
GET  /Platform/tenants/{tenant}/subscriptions          # Listar assinaturas de um tenant
POST /Platform/invoices/{invoice}/sync                 # Sincronizar fatura manualmente
POST /Platform/whatsapp/send                           # Enviar mensagem WhatsApp
POST /Platform/whatsapp/invoice/{invoice}              # Enviar notificação de fatura
GET  /Platform/api/estados/{pais}                      # API: Estados por país
GET  /Platform/api/cidades/{estado}                    # API: Cidades por estado
GET  /Platform/system_notifications/json                # API: Notificações em JSON
POST /Platform/users/{user}/reset-password              # Resetar senha de usuário
POST /Platform/users/{user}/toggle-status               # Ativar/desativar usuário
GET  /Platform/settings/test/{service}                 # Testar conexão de serviço
GET  /kiosk/monitor                                     # Monitor de kiosk
GET  /kiosk/monitor/data                               # Dados do monitor (API)
POST /webhook/asaas                                    # Webhook do Asaas (sem prefixo)
```

---

## 🎮 Controllers

### Controllers da Platform (`app/Http/Controllers/Platform/`)

| Controller | Responsabilidade | Rotas Principais |
|------------|------------------|------------------|
| `DashboardController` | Dashboard principal com estatísticas | `/Platform/dashboard` |
| `TenantController` | CRUD de tenants + sincronização com Asaas | `/Platform/tenants` |
| `PlanController` | CRUD de planos de assinatura | `/Platform/plans` |
| `SubscriptionController` | CRUD de assinaturas + renovação | `/Platform/subscriptions` |
| `InvoiceController` | CRUD de faturas + sincronização manual | `/Platform/invoices` |
| `UserController` | CRUD de usuários da platform + reset de senha | `/Platform/users` |
| `MedicalSpecialtyCatalogController` | Catálogo de especialidades médicas | `/Platform/medical_specialties_catalog` |
| `NotificationOutboxController` | Histórico de notificações enviadas | `/Platform/notifications_outbox` |
| `SystemNotificationController` | Notificações do sistema | `/Platform/system_notifications` |
| `SystemSettingsController` | Configurações gerais e integrações | `/Platform/settings` |
| `PaisController`, `EstadoController`, `CidadeController` | CRUD de localização | `/Platform/paises`, `/Platform/estados`, `/Platform/cidades` |
| `LocationController` | API de localização (estados/cidades) | `/Platform/api/estados/{pais}`, `/Platform/api/cidades/{estado}` |
| `WhatsAppController` | Envio de mensagens WhatsApp | `/Platform/whatsapp/send` |
| `KioskMonitorController` | Monitor de kiosk | `/kiosk/monitor` |

---

## 🗄️ Models

### Models da Platform (`app/Models/Platform/`)

Armazenados no **banco central (landlord)**:

| Model | Tabela | Descrição |
|-------|--------|-----------|
| `Tenant` | `tenants` | Clientes (clínicas) - UUID como chave primária |
| `User` | `users` | Usuários da plataforma administrativa |
| `Plan` | `plans` | Planos de assinatura |
| `Subscription` | `subscriptions` | Assinaturas dos tenants |
| `Invoices` | `invoices` | Faturas geradas |
| `NotificationOutbox` | `notifications_outbox` | Histórico de notificações |
| `SystemNotification` | `system_notifications` | Notificações do sistema |
| `MedicalSpecialtyCatalog` | `medical_specialties_catalog` | Catálogo global de especialidades |
| `Pais`, `Estado`, `Cidade` | `paises`, `estados`, `cidades` | Dados de localização |
| `TenantLocalizacao` | `tenant_localizacoes` | Localização dos tenants |
| `SystemSetting` | `system_settings` | Configurações do sistema |
| `WebhookLog` | `webhook_logs` | Logs de webhooks recebidos |
| `Module` | - | Módulos de acesso (helper) |

### Características Importantes

- `Tenant` estende `Spatie\Multitenancy\Models\Tenant`
- `Tenant` possui métodos para configuração de banco: `getDatabaseName()`, `getDatabaseHost()`, etc.
- `User` (Platform) possui campo `modules` (JSON) para controle de acesso
- `Tenant` usa UUID (string) como chave primária

---

## ⚙️ Funcionalidades Principais

### 1. Gerenciamento de Tenants

**Criar Tenant:**
1. Acesse `/Platform/tenants`
2. Clique em "Criar Tenant"
3. Preencha os dados da clínica:
   - Nome legal e nome fantasia
   - Subdomain (usado na URL: `/t/{subdomain}`)
   - Documento (CPF/CNPJ)
   - Email
   - Localização (país, estado, cidade)
   - Configurações de banco de dados
4. O sistema criará automaticamente:
   - Banco de dados PostgreSQL
   - Usuário do banco
   - Estrutura de tabelas (migrations)
   - Usuário admin padrão

**Sincronizar com Asaas:**
- Acesse o tenant → Ações → "Sincronizar com Asaas"
- Cria ou atualiza o cliente no gateway de pagamento

### 2. Gestão de Planos

**Criar Plano:**
1. Acesse `/Platform/plans`
2. Clique em "Criar Plano"
3. Defina:
   - Nome do plano
   - Descrição
   - Valor mensal
   - Recursos incluídos
   - Status (ativo/inativo)

### 3. Assinaturas

**Criar Assinatura:**
1. Acesse `/Platform/subscriptions`
2. Clique em "Criar Assinatura"
3. Selecione:
   - Tenant
   - Plano
   - Data de início
   - Status

**Renovar Assinatura:**
- Acesse a assinatura → Ações → "Renovar"
- O sistema criará uma nova assinatura com base no plano atual

### 4. Faturas

**Sincronizar Fatura:**
- Acesse a fatura → Ações → "Sincronizar com Asaas"
- Busca informações atualizadas do gateway de pagamento

**Enviar Notificação:**
- Acesse a fatura → Ações → "Enviar WhatsApp"
- Envia notificação de fatura via WhatsApp

### 5. Configurações do Sistema

**Acessar Configurações:**
1. Acesse `/Platform/settings`
2. Configure:
   - Integração Asaas (API URL, API Key, Ambiente)
   - Integração WhatsApp (Token, Phone ID)
   - Configurações de Email (SMTP)
   - Timezone, Idioma, País padrão

**Nota:** Configurações definidas aqui têm prioridade sobre variáveis de ambiente.

### 6. Catálogo de Especialidades Médicas

**Gerenciar Especialidades:**
1. Acesse `/Platform/medical_specialties_catalog`
2. Crie, edite ou remova especialidades médicas
3. As especialidades ficam disponíveis para todos os tenants

### 7. Monitor de Kiosk

**Acessar Monitor:**
1. Acesse `/kiosk/monitor` (sem prefixo Platform)
2. Visualize status e informações dos kiosks conectados
3. Monitore atividades em tempo real

---

## 🔌 Integrações

### Asaas (Gateway de Pagamento)

**Configuração:**
- Variáveis de ambiente (`.env`):
  ```env
  ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
  ASAAS_API_KEY=sua_chave_api
  ASAAS_WEBHOOK_SECRET=seu_secret_webhook
  ASAAS_ENV=sandbox
  ```
- Ou via interface: `/Platform/settings`

**Funcionalidades:**
- Criação de clientes no Asaas
- Gerenciamento de assinaturas
- Geração de faturas
- Recebimento de webhooks de pagamento
- Sincronização manual de dados

**Webhook:**
- URL: `POST /webhook/asaas`
- Middleware: `verify.asaas.token`
- Validação de token obrigatória

### WhatsApp Business API

**Configuração:**
- Variáveis de ambiente (`.env`):
  ```env
  WHATSAPP_API_URL=https://graph.facebook.com/v18.0
  WHATSAPP_TOKEN=seu_token
  WHATSAPP_PHONE_ID=seu_phone_id
  META_ACCESS_TOKEN=seu_token_meta
  META_PHONE_NUMBER_ID=seu_phone_number_id
  ```
- Ou via interface: `/Platform/settings`

**Funcionalidades:**
- Envio de mensagens WhatsApp
- Notificações de faturas
- Notificações de agendamento (futuro)

---

## 📚 Guia de Uso

### Criar um Novo Tenant

1. Acesse `/Platform/tenants`
2. Clique em "Criar Tenant"
3. Preencha os dados obrigatórios:
   - Nome legal e nome fantasia
   - Subdomain (único)
   - Documento (CPF/CNPJ)
   - Email
   - Status (ativo/inativo)
4. Configure o banco de dados:
   - Host e porta (geralmente do `.env`)
   - Nome do banco (será criado automaticamente)
   - Usuário e senha (serão criados automaticamente)
5. Clique em "Salvar"
6. O sistema criará:
   - Banco de dados PostgreSQL
   - Estrutura de tabelas
   - Usuário admin padrão

**Credenciais padrão do admin:**
- Email: `admin@{subdomain}`
- Senha: Verifique o seeder `TenantAdminSeeder`

### Gerenciar Assinatura

1. Acesse `/Platform/subscriptions`
2. Para criar nova assinatura:
   - Clique em "Criar Assinatura"
   - Selecione tenant e plano
   - Defina data de início
3. Para renovar:
   - Acesse a assinatura
   - Clique em "Renovar"
   - Uma nova assinatura será criada

### Sincronizar com Asaas

1. Acesse `/Platform/tenants`
2. Localize o tenant
3. Clique em "Ações" → "Sincronizar com Asaas"
4. O sistema criará ou atualizará o cliente no Asaas

### Enviar Notificação WhatsApp

1. Acesse `/Platform/invoices`
2. Localize a fatura
3. Clique em "Ações" → "Enviar WhatsApp"
4. A mensagem será enviada para o número cadastrado do tenant

---

## 🔄 Migrações

### Migrações do Banco Central (`database/migrations/`)

Tabelas principais:
- `tenants` - Registro de todos os tenants
- `plans` - Planos de assinatura
- `subscriptions` - Assinaturas ativas
- `invoices` - Faturas geradas
- `users` - Usuários da platform
- `paises`, `estados`, `cidades` - Dados de localização
- `medical_specialties_catalog` - Catálogo de especialidades
- `notifications_outbox` - Histórico de notificações
- `system_notifications` - Notificações do sistema
- `system_settings` - Configurações
- `webhook_logs` - Logs de webhooks
- `tenant_localizacoes` - Localização dos tenants

**Executar migrações:**
```bash
php artisan migrate
```

---

## 🛡️ Segurança

1. **Autenticação Obrigatória**: Todas as rotas exigem autenticação
2. **Controle de Acesso**: Sistema de módulos para restringir funcionalidades
3. **Validação de Webhook**: Webhooks do Asaas são validados por token
4. **Isolamento de Dados**: Cada tenant possui banco de dados isolado
5. **Logs de Auditoria**: Sistema registra ações importantes

---

## 📝 Observações Importantes

1. **UUID como Chave Primária**: O modelo `Tenant` usa UUID (string) como chave primária
2. **Criação Automática de Banco**: Ao criar um tenant, o banco é criado automaticamente
3. **Configurações Dinâmicas**: Configurações podem ser alteradas via interface (têm prioridade sobre `.env`)
4. **Sincronização Manual**: Algumas operações (como sincronização com Asaas) podem ser executadas manualmente
5. **Monitor de Kiosk**: Sistema possui monitor para acompanhar status de kiosks

---

## 🔗 Links Relacionados

- [README.md](README.md) - Documentação geral do projeto
- [TENANT.md](TENANT.md) - Documentação da área Tenant
- [ARQUITETURA.md](ARQUITETURA.md) - Documentação técnica da arquitetura
- [docs/ENV.md](docs/ENV.md) - Guia de variáveis de ambiente

---

**Última atualização:** 2025-01-27

