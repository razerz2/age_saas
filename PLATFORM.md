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

### Rotas Principais (com prefixo `/Platform`)

Todas as rotas da Platform utilizam o prefixo `/Platform` e exigem autenticação (`auth` middleware):

```php
# Rotas públicas (sem autenticação)
GET  /                                    # Redireciona para login ou dashboard
GET  /kiosk/monitor                      # Monitor de kiosk (sem prefixo Platform)
GET  /kiosk/monitor/data                 # Dados do monitor (API, sem prefixo Platform)
POST /webhook/asaas                      # Webhook do Asaas (sem prefixo)
GET  /google/callback                     # Callback do Google Calendar OAuth (rota global)

# Rotas autenticadas (com prefixo /Platform)
GET  /Platform/dashboard                  # Dashboard principal
GET  /Platform/profile                    # Editar perfil do usuário logado
PATCH /Platform/profile                   # Atualizar perfil
DELETE /Platform/profile                  # Deletar perfil

# CRUD de recursos (com controle de acesso por módulo)
/Platform/tenants                         # CRUD de tenants
/Platform/plans                           # CRUD de planos
/Platform/subscriptions                   # CRUD de assinaturas
/Platform/invoices                        # CRUD de faturas
/Platform/users                           # CRUD de usuários da platform
/Platform/settings                        # Configurações do sistema
/Platform/system_notifications            # Notificações do sistema (read-only)
/Platform/notifications_outbox            # Histórico de notificações enviadas
/Platform/medical_specialties_catalog     # Catálogo de especialidades
/Platform/paises                          # Listar/visualizar países (read-only)
/Platform/estados                        # Listar/visualizar estados (read-only)
/Platform/cidades                         # Listar/visualizar cidades (read-only)
```

### Rotas Especiais

```php
# Tenants
POST /Platform/tenants/{tenant}/sync                    # Sincronizar tenant com Asaas
GET  /Platform/tenants/{tenant}/subscriptions          # Listar assinaturas de um tenant

# Assinaturas
POST /Platform/subscriptions/{id}/renew               # Renovar assinatura (onde {id} é numérico)
POST /Platform/subscriptions/{subscription}/sync       # Sincronizar assinatura com Asaas

# Faturas
POST /Platform/invoices/{invoice}/sync                 # Sincronizar fatura manualmente com Asaas

# Usuários
POST /Platform/users/{user}/reset-password             # Resetar senha de usuário
POST /Platform/users/{user}/toggle-status              # Ativar/desativar usuário

# Configurações
GET  /Platform/settings/test/{service}                 # Testar conexão de serviço (Asaas, WhatsApp, Email)
POST /Platform/settings/update/general                  # Atualizar configurações gerais
POST /Platform/settings/update/integrations            # Atualizar integrações

# WhatsApp
POST /Platform/whatsapp/send                           # Enviar mensagem WhatsApp
POST /Platform/whatsapp/invoice/{invoice}              # Enviar notificação de fatura via WhatsApp

# APIs auxiliares
GET  /Platform/api/estados/{pais}                      # API: Estados por país
GET  /Platform/api/cidades/{estado}                    # API: Cidades por estado
GET  /Platform/system_notifications/json                # API: Notificações em JSON (últimas 5)
```

### Controle de Acesso por Módulo

As rotas abaixo exigem o módulo correspondente no campo `modules` do usuário:

- `tenants` - Acesso a `/Platform/tenants/*`
- `plans` - Acesso a `/Platform/plans/*`
- `subscriptions` - Acesso a `/Platform/subscriptions/*`
- `invoices` - Acesso a `/Platform/invoices/*`
- `users` - Acesso a `/Platform/users/*`
- `settings` - Acesso a `/Platform/settings/*`
- `medical_specialties_catalog` - Acesso a `/Platform/medical_specialties_catalog/*`
- `notifications_outbox` - Acesso a `/Platform/notifications_outbox/*`
- `system_notifications` - Acesso a `/Platform/system_notifications/*`
- `locations` - Acesso a `/Platform/paises/*`, `/Platform/estados/*`, `/Platform/cidades/*`

**Nota:** As rotas `/Platform/dashboard` e `/Platform/profile/*` são sempre acessíveis para usuários autenticados, independente dos módulos.

---

## 🎮 Controllers

### Controllers da Platform (`app/Http/Controllers/Platform/`)

| Controller | Responsabilidade | Rotas Principais | Módulo |
|------------|------------------|------------------|--------|
| `DashboardController` | Dashboard principal com estatísticas e métricas | `/Platform/dashboard` | Sempre acessível |
| `TenantController` | CRUD de tenants + sincronização com Asaas + criação de banco | `/Platform/tenants` | `tenants` |
| `PlanController` | CRUD de planos de assinatura | `/Platform/plans` | `plans` |
| `SubscriptionController` | CRUD de assinaturas + renovação + sincronização | `/Platform/subscriptions` | `subscriptions` |
| `InvoiceController` | CRUD de faturas + sincronização manual + envio WhatsApp | `/Platform/invoices` | `invoices` |
| `UserController` | CRUD de usuários da platform + reset de senha + toggle status | `/Platform/users` | `users` |
| `MedicalSpecialtyCatalogController` | Catálogo de especialidades médicas | `/Platform/medical_specialties_catalog` | `medical_specialties_catalog` |
| `NotificationOutboxController` | Histórico de notificações enviadas | `/Platform/notifications_outbox` | `notifications_outbox` |
| `SystemNotificationController` | Notificações do sistema (read-only) | `/Platform/system_notifications` | `system_notifications` |
| `SystemSettingsController` | Configurações gerais e integrações + testes de conexão | `/Platform/settings` | `settings` |
| `PaisController` | Listar e visualizar países | `/Platform/paises` | `locations` |
| `EstadoController` | Listar e visualizar estados | `/Platform/estados` | `locations` |
| `CidadeController` | Listar e visualizar cidades | `/Platform/cidades` | `locations` |
| `LocationController` | API de localização (estados/cidades) | `/Platform/api/estados/{pais}`, `/Platform/api/cidades/{estado}` | Sempre acessível |
| `WhatsAppController` | Envio de mensagens WhatsApp | `/Platform/whatsapp/send`, `/Platform/whatsapp/invoice/{invoice}` | Sempre acessível |
| `KioskMonitorController` | Monitor de kiosk com estatísticas | `/kiosk/monitor`, `/kiosk/monitor/data` | Público (sem autenticação) |

### Funcionalidades Detalhadas dos Controllers

#### DashboardController
- Exibe estatísticas principais:
  - Tenants ativos
  - Assinaturas ativas
  - Faturamento do mês atual
  - Assinaturas canceladas no mês
  - Gráfico de receita total vs faturas vencidas
  - Crescimento de clientes (mês a mês)
  - Top 5 tenants mais antigos

#### TenantController
- CRUD completo de tenants
- Criação automática de banco de dados PostgreSQL
- Criação automática de usuário admin padrão
- Sincronização com gateway de pagamento Asaas
- Visualização de informações do usuário admin do tenant

#### SubscriptionController
- CRUD completo de assinaturas
- Renovação de assinaturas
- Sincronização com Asaas
- Listagem de assinaturas por tenant

#### InvoiceController
- CRUD completo de faturas
- Sincronização manual com Asaas
- Envio automático de notificações via WhatsApp ao criar fatura

#### SystemSettingsController
- Configurações gerais: timezone, país padrão, idioma
- Integrações: Asaas, WhatsApp (Meta), Email (SMTP)
- Teste de conexão para cada serviço
- Atualização de variáveis de ambiente

#### KioskMonitorController
- Monitor público (sem autenticação)
- Exibe estatísticas em tempo real:
  - Total de clientes ativos
  - Total de assinaturas ativas
  - Faturamento total (faturas pagas)

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

### 1. Dashboard

**Acessar Dashboard:**
1. Acesse `/Platform/dashboard` (sempre disponível para usuários autenticados)
2. Visualize estatísticas em tempo real:
   - **Tenants Ativos**: Total de clínicas com status ativo
   - **Assinaturas Ativas**: Total de assinaturas ativas
   - **Faturamento do Mês**: Soma de faturas pagas no mês/ano atual
   - **Assinaturas Canceladas**: Total cancelado no mês atual
   - **Gráfico de Receita**: Receita total vs faturas vencidas (formato doughnut)
   - **Crescimento de Clientes**: Gráfico mensal de novos tenants no ano atual
   - **Top 5 Tenants Mais Antigos**: Lista dos tenants mais antigos do sistema

### 2. Gerenciamento de Tenants

**Criar Tenant:**
1. Acesse `/Platform/tenants` (requer módulo `tenants`)
2. Clique em "Criar Tenant"
3. Preencha os dados da clínica:
   - Nome legal e nome fantasia
   - Subdomain (usado na URL: `/t/{subdomain}`)
   - Documento (CPF/CNPJ)
   - Email
   - Localização (país, estado, cidade)
   - Endereço (opcional)
   - Status (ativo/inativo)
4. O sistema criará automaticamente:
   - Banco de dados PostgreSQL
   - Usuário do banco de dados
   - Estrutura de tabelas (executa migrations do tenant)
   - Usuário admin padrão com credenciais:
     - Email: `admin@{subdomain}.com`
     - Senha: `admin123` (padrão)

**Visualizar Tenant:**
- Acesse o tenant → Visualizar
- Exibe informações completas incluindo:
  - Dados cadastrais
  - Localização
  - Configurações de banco de dados
  - Informações do usuário admin
  - Link de acesso direto ao login do tenant

**Sincronizar com Asaas:**
- Acesse o tenant → Ações → "Sincronizar com Asaas"
- Cria ou atualiza o cliente no gateway de pagamento Asaas
- Sincroniza dados do tenant com a API do Asaas

### 3. Gestão de Planos

**Criar Plano:**
1. Acesse `/Platform/plans`
2. Clique em "Criar Plano"
3. Defina:
   - Nome do plano
   - Descrição
   - Valor mensal
   - Recursos incluídos
   - Status (ativo/inativo)

### 4. Assinaturas

**Criar Assinatura:**
1. Acesse `/Platform/subscriptions` (requer módulo `subscriptions`)
2. Clique em "Criar Assinatura"
3. Selecione:
   - Tenant
   - Plano
   - Data de início
   - Status (ativo/inativo)
   - Auto-renovação (opcional)

**Renovar Assinatura:**
- Acesse a assinatura → Ações → "Renovar"
- O sistema criará uma nova assinatura com base no plano atual
- A assinatura antiga será encerrada

**Sincronizar Assinatura:**
- Acesse a assinatura → Ações → "Sincronizar com Asaas"
- Sincroniza dados da assinatura com o gateway de pagamento

**Listar Assinaturas por Tenant:**
- Acesse `/Platform/tenants/{tenant}/subscriptions`
- Exibe todas as assinaturas de um tenant específico

### 5. Faturas

**Criar Fatura:**
1. Acesse `/Platform/invoices` (requer módulo `invoices`)
2. Clique em "Criar Fatura"
3. Preencha:
   - Tenant ou Assinatura
   - Valor
   - Data de vencimento
   - Descrição
4. Ao salvar, o sistema:
   - Cria a fatura no banco
   - Tenta sincronizar automaticamente com Asaas
   - Envia notificação via WhatsApp (se configurado)

**Sincronizar Fatura:**
- Acesse a fatura → Ações → "Sincronizar com Asaas"
- Busca informações atualizadas do gateway de pagamento
- Atualiza status e dados da fatura

**Enviar Notificação:**
- Acesse a fatura → Ações → "Enviar WhatsApp"
- Envia notificação de fatura via WhatsApp para o tenant

### 6. Configurações do Sistema

**Acessar Configurações:**
1. Acesse `/Platform/settings` (requer módulo `settings`)
2. Configure **Configurações Gerais**:
   - Timezone (ex: `America/Sao_Paulo`)
   - País padrão (seleção de país)
   - Idioma (ex: `pt_BR`)
3. Configure **Integrações**:
   - **Asaas**: API Key, Ambiente (sandbox/production)
   - **WhatsApp (Meta)**: Access Token, Phone Number ID
   - **Email (SMTP)**: Host, Porta, Username, Password, From Address, From Name
4. **Testar Conexões**:
   - Use `/Platform/settings/test/{service}` para testar:
     - `asaas` - Testa conexão com Asaas
     - `whatsapp` - Testa conexão com WhatsApp
     - `email` - Testa envio de email

**Nota:** Configurações definidas aqui têm prioridade sobre variáveis de ambiente. As configurações são salvas na tabela `system_settings` e também atualizadas no arquivo `.env`.

### 7. Catálogo de Especialidades Médicas

**Gerenciar Especialidades:**
1. Acesse `/Platform/medical_specialties_catalog` (requer módulo `medical_specialties_catalog`)
2. Crie, edite ou remova especialidades médicas
3. As especialidades ficam disponíveis para todos os tenants
4. Os tenants podem importar especialidades deste catálogo

### 8. Localização (Países, Estados, Cidades)

**Visualizar Localizações:**
1. Acesse `/Platform/paises`, `/Platform/estados` ou `/Platform/cidades` (requer módulo `locations`)
2. Visualize dados de localização cadastrados
3. **Nota:** Estas rotas são read-only (apenas visualização)

**API de Localização:**
- `GET /Platform/api/estados/{pais}` - Retorna estados de um país (JSON)
- `GET /Platform/api/cidades/{estado}` - Retorna cidades de um estado (JSON)
- Utilizadas em formulários para seleção dinâmica

### 9. Gerenciamento de Usuários da Platform

**Criar Usuário:**
1. Acesse `/Platform/users` (requer módulo `users`)
2. Clique em "Criar Usuário"
3. Preencha:
   - Nome
   - Email
   - Senha
   - Módulos de acesso (selecione quais módulos o usuário pode acessar)
   - Status (ativo/inativo)
4. O usuário será criado com status ativo por padrão

**Gerenciar Módulos de Acesso:**
- Cada usuário possui um campo `modules` (JSON) que define quais módulos pode acessar
- Os módulos disponíveis são:
  - `tenants`, `plans`, `subscriptions`, `invoices`
  - `users`, `settings`, `medical_specialties_catalog`
  - `notifications_outbox`, `system_notifications`, `locations`
- Se nenhum módulo for selecionado, o usuário só terá acesso ao dashboard e perfil

**Resetar Senha:**
- Acesse o usuário → Ações → "Resetar Senha"
- Gera uma nova senha aleatória: `user{4 dígitos}`
- **Nota:** Você não pode resetar sua própria senha por aqui (use o menu de perfil)

**Ativar/Desativar Usuário:**
- Acesse o usuário → Ações → "Ativar/Desativar"
- Alterna o status do usuário entre ativo e inativo
- Usuários inativos não conseguem fazer login

### 10. Perfil do Usuário

**Gerenciar Perfil:**
1. Acesse `/Platform/profile` (sempre acessível para usuários autenticados)
2. Visualize e edite seus dados:
   - Nome
   - Email
   - Senha (opcional)
3. **Atualizar Perfil:**
   - Use `PATCH /Platform/profile` para atualizar dados
4. **Deletar Conta:**
   - Use `DELETE /Platform/profile` para deletar sua conta
   - **Atenção:** Esta ação é irreversível

**Nota:** O perfil é gerenciado pelo `ProfileController` (não está em `Platform/`), mas é acessível via rota `/Platform/profile`.

### 11. Monitor de Kiosk

**Acessar Monitor:**
1. Acesse `/kiosk/monitor` (rota pública, sem autenticação)
2. Visualize estatísticas em tempo real:
   - Total de clientes ativos
   - Total de assinaturas ativas
   - Faturamento total (faturas pagas)
3. Os dados são atualizados via API: `GET /kiosk/monitor/data` (retorna JSON)

**Nota:** Esta funcionalidade é útil para exibir em telas públicas ou dashboards de monitoramento.

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

1. Acesse `/Platform/tenants` (requer módulo `tenants`)
2. Clique em "Criar Tenant"
3. Preencha os dados obrigatórios:
   - Nome legal e nome fantasia
   - Subdomain (único, usado na URL `/t/{subdomain}`)
   - Documento (CPF/CNPJ)
   - Email
   - Status (ativo/inativo)
4. Configure a localização (opcional):
   - País, Estado, Cidade
   - Endereço
5. O sistema criará automaticamente:
   - Banco de dados PostgreSQL (nome gerado automaticamente)
   - Usuário do banco de dados (credenciais geradas)
   - Estrutura de tabelas (executa todas as migrations do tenant)
   - Usuário admin padrão

**Credenciais padrão do admin:**
- Email: `admin@{subdomain}.com` (subdomain sanitizado)
- Senha: `admin123` (padrão definido no `TenantProvisioner`)

**Link de acesso:**
- URL: `/t/{subdomain}/login`
- O link é exibido na página de visualização do tenant

### Gerenciar Assinatura

1. Acesse `/Platform/subscriptions` (requer módulo `subscriptions`)
2. **Para criar nova assinatura:**
   - Clique em "Criar Assinatura"
   - Selecione tenant e plano
   - Defina data de início
   - Configure status e auto-renovação
3. **Para renovar:**
   - Acesse a assinatura
   - Clique em "Renovar"
   - Uma nova assinatura será criada automaticamente
4. **Para sincronizar:**
   - Acesse a assinatura
   - Clique em "Sincronizar com Asaas"
   - Atualiza dados no gateway de pagamento

### Sincronizar com Asaas

**Sincronizar Tenant:**
1. Acesse `/Platform/tenants` (requer módulo `tenants`)
2. Localize o tenant
3. Clique em "Ações" → "Sincronizar com Asaas"
4. O sistema criará ou atualizará o cliente no Asaas

**Sincronizar Assinatura:**
1. Acesse `/Platform/subscriptions` (requer módulo `subscriptions`)
2. Localize a assinatura
3. Clique em "Ações" → "Sincronizar com Asaas"
4. Atualiza dados da assinatura no gateway

**Sincronizar Fatura:**
1. Acesse `/Platform/invoices` (requer módulo `invoices`)
2. Localize a fatura
3. Clique em "Ações" → "Sincronizar com Asaas"
4. Busca status e dados atualizados do gateway

### Enviar Notificação WhatsApp

**Enviar Mensagem Genérica:**
1. Use a rota `POST /Platform/whatsapp/send`
2. Envie dados: número, mensagem
3. A mensagem será enviada via WhatsApp Business API

**Enviar Notificação de Fatura:**
1. Acesse `/Platform/invoices` (requer módulo `invoices`)
2. Localize a fatura
3. Clique em "Ações" → "Enviar WhatsApp"
4. A mensagem será enviada para o número cadastrado do tenant
5. A notificação inclui dados da fatura (valor, vencimento, link de pagamento)

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

**Nota:** Esta documentação foi revisada e atualizada com base no código implementado, incluindo:
- Todas as rotas atuais da Platform
- Detalhamento completo dos controllers e suas funcionalidades
- Informações sobre controle de acesso por módulos
- Dashboard com estatísticas detalhadas
- Funcionalidades de sincronização com Asaas
- Monitor de kiosk público
- APIs auxiliares de localização
- Configurações do sistema com testes de conexão

