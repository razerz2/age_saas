# 🏢 Documentação - Área Platform (Administrativa)

> Esta documentação está sendo reorganizada.
> Para o índice oficial e navegação por áreas, consulte `docs/README.md`.
> Este arquivo permanece como referência funcional detalhada da área Platform.

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
- ✅ Gestão de planos de assinatura (Comerciais, Contratuais e Sandbox)
- ✅ Controle de assinaturas e renovações
- ✅ Gerenciamento de faturas
- ✅ Sistema de notificações
- ✅ Catálogo de especialidades médicas
- ✅ Gestão de usuários administrativos
- ✅ Configurações do sistema
- ✅ Integração com gateway de pagamento (Asaas)
- ✅ Envio de mensagens WhatsApp
- ✅ Monitor de kiosk
- ✅ **Módulo de Pré-Cadastro** - Gerenciamento de pré-cadastros de novos tenants
- ✅ **Módulo de Redes de Clínicas** - Gerenciamento de redes, vinculação de tenants e importação em lote (CSV)
- ✅ **Importação de Tenants** - Criação massiva de clínicas vinculadas a redes via CSV com resolução automática de localização.

### Banco de Dados

A Platform utiliza o **banco central (landlord)**, que armazena:
- Dados dos tenants
- Planos e assinaturas
- Faturas
- Usuários administrativos
- Configurações do sistema
- Catálogo de especialidades médicas
- Dados de localização (países, estados, cidades)
- Pré-cadastros de tenants (pre_tenants)
- Logs de pré-cadastros (pre_tenant_logs)
- Redes de clínicas (clinic_networks)
- Usuários das redes (network_users)

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

- `tenants` - Tenants
- `clinic_networks` - Redes de Clínicas
- `pre_tenants` - Pré-Cadastros
- `plans` - Planos
- `subscriptions` - Assinaturas
- `invoices` - Faturas
- `medical_specialties_catalog` - Catálogo Médico
- `notifications_outbox` - Notificações
- `system_notifications` - Notificações do Sistema
- `notification_templates` - Templates de Notificação
- `locations` - Localização
- `users` - Usuários
- `settings` - Configurações

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
/Platform/subscription-access              # Gerenciamento de regras de acesso por plano
/Platform/subscriptions                   # CRUD de assinaturas
/Platform/invoices                        # CRUD de faturas
/Platform/users                           # CRUD de usuários da platform
/Platform/settings                        # Configurações do sistema
/Platform/system_notifications            # Notificações do sistema (read-only)
/Platform/notifications_outbox            # Histórico de notificações enviadas
/Platform/medical_specialties_catalog     # Catálogo de especialidades
/Platform/pre-tenants                      # Gerenciamento de pré-cadastros
/Platform/paises                          # Listar/visualizar países (read-only)
/Platform/estados                        # Listar/visualizar estados (read-only)
/Platform/cidades                         # Listar/visualizar cidades (read-only)
```

### Rotas Especiais

```php
# Tenants
POST /Platform/tenants/{tenant}/sync                    # Sincronizar tenant com Asaas
GET  /Platform/tenants/{tenant}/subscriptions          # Listar assinaturas de um tenant

# Planos - Regras de Acesso
GET    /Platform/subscription-access                  # Listar regras de acesso
POST   /Platform/subscription-access                  # Criar regra de acesso
GET    /Platform/subscription-access/{id}              # Visualizar regra
GET    /Platform/subscription-access/{id}/edit         # Editar regra
PUT    /Platform/subscription-access/{id}             # Atualizar regra
DELETE /Platform/subscription-access/{id}             # Excluir regra

# Redes de Clínicas e Importação
GET    /Platform/clinic-networks                      # Listar redes
POST   /Platform/clinic-networks                      # Criar rede
GET    /Platform/clinic-networks/import-all           # Importação geral (selecionar rede e arquivo)
POST   /Platform/clinic-networks/import-all           # Processar importação geral
GET    /Platform/clinic-networks/{network}/import      # Importação para rede específica
POST   /Platform/clinic-networks/{network}/import      # Processar importação para rede

# Assinaturas
POST /Platform/subscriptions/{id}/renew               # Renovar assinatura (onde {id} é numérico)
POST /Platform/subscriptions/{subscription}/sync       # Sincronizar assinatura com Asaas

# Solicitações de Mudança de Plano
GET    /Platform/plan-change-requests                 # Listar solicitações de mudança de plano
GET    /Platform/plan-change-requests/{id}            # Visualizar detalhes da solicitação
POST   /Platform/plan-change-requests/{id}/approve    # Aprovar solicitação
POST   /Platform/plan-change-requests/{id}/reject     # Rejeitar solicitação

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

# Templates de Notificação
GET    /Platform/notification-templates                # Listar templates
GET    /Platform/notification-templates/{id}/edit      # Editar template
PUT    /Platform/notification-templates/{id}           # Atualizar template
DELETE /Platform/notification-templates/{id}           # Deletar template
POST   /Platform/notification-templates/{id}/restore   # Restaurar template deletado
POST   /Platform/notification-templates/{id}/test      # Testar envio de template
POST   /Platform/notification-templates/{id}/toggle    # Ativar/desativar template

# Pré-Cadastros
GET    /Platform/pre-tenants                           # Listar pré-cadastros
GET    /Platform/pre-tenants/{preTenant}                # Visualizar pré-cadastro
POST   /Platform/pre-tenants/{preTenant}/approve         # Aprovar pré-cadastro manualmente
POST   /Platform/pre-tenants/{preTenant}/cancel          # Cancelar pré-cadastro
POST   /Platform/pre-tenants/{preTenant}/confirm-payment # Confirmar pagamento manualmente

# Rotas Públicas (sem autenticação)
GET  /                                   # Landing page (home)
GET  /funcionalidades                    # Landing page (funcionalidades)
GET  /planos                             # Landing page (planos)
GET  /planos/json/{id}                   # API: Dados do plano em JSON
GET  /contato                            # Landing page (contato)
GET  /manual                             # Landing page (manual)
POST /pre-register                       # Criar pré-cadastro (landing page)
POST /webhook/asaas/pre-registration     # Webhook do Asaas para pré-cadastros

# APIs auxiliares
GET  /Platform/api/estados/{pais}                      # API: Estados por país
GET  /Platform/api/cidades/{estado}                    # API: Cidades por estado
GET  /Platform/system_notifications/json                # API: Notificações em JSON (últimas 5)

# Rotas Públicas (sem autenticação)
POST /pre-register                                      # Criar pré-cadastro (landing page)
POST /webhook/asaas/pre-registration                   # Webhook do Asaas para pré-cadastros
```

### Controle de Acesso por Módulo

As rotas abaixo exigem o módulo correspondente no campo `modules` do usuário:

- `tenants` - Acesso a `/Platform/tenants/*`
- `clinic_networks` - Acesso a `/Platform/clinic-networks/*`
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
| `ClinicNetworkController` | CRUD de redes de clínicas + vinculação de tenants | `/Platform/clinic-networks` | `clinic_networks` |
| `PlanController` | CRUD de planos de assinatura | `/Platform/plans` | `plans` |
| `PlanAccessManagerController` | Gerenciamento de regras de acesso por plano | `/Platform/subscription-access` | `plans` |
| `SubscriptionController` | CRUD de assinaturas + renovação + sincronização | `/Platform/subscriptions` | `subscriptions` |
| `InvoiceController` | CRUD de faturas + sincronização manual + envio WhatsApp | `/Platform/invoices` | `invoices` |
| `UserController` | CRUD de usuários da platform + reset de senha + toggle status | `/Platform/users` | `users` |
| `MedicalSpecialtyCatalogController` | Catálogo de especialidades médicas | `/Platform/medical_specialties_catalog` | `medical_specialties_catalog` |
| `NotificationOutboxController` | Histórico de notificações enviadas | `/Platform/notifications_outbox` | `notifications_outbox` |
| `SystemNotificationController` | Notificações do sistema (read-only) | `/Platform/system_notifications` | `system_notifications` |
| `NotificationTemplateController` | Templates de notificação | `/Platform/notification-templates` | `notification_templates` |
| `SystemSettingsController` | Configurações gerais e integrações + testes de conexão | `/Platform/settings` | `settings` |
| `PaisController` | Listar e visualizar países | `/Platform/paises` | `locations` |
| `EstadoController` | Listar e visualizar estados | `/Platform/estados` | `locations` |
| `CidadeController` | Listar e visualizar cidades | `/Platform/cidades` | `locations` |
| `LocationController` | API de localização (estados/cidades) | `/Platform/api/estados/{pais}`, `/Platform/api/cidades/{estado}` | Sempre acessível |
| `WhatsAppController` | Envio de mensagens WhatsApp | `/Platform/whatsapp/send`, `/Platform/whatsapp/invoice/{invoice}` | Sempre acessível |
| `PreTenantController` | Gerenciamento de pré-cadastros | `/Platform/pre-tenants` | `pre_tenants` |
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
- CRUD completo de tenants centralizado no `TenantCreatorService`
- Criação automática de banco de dados PostgreSQL
- Criação automática de usuário admin padrão
- **Localização**: Focada no Brasil (ID 31), com campos obrigatórios de endereço (Logradouro, Bairro, CEP, Estado, Cidade)
- Sincronização com gateway de pagamento Asaas (para planos comerciais)
- Visualização de informações do usuário admin do tenant

#### ClinicNetworkController
- CRUD de redes de clínicas
- Vinculação de tenants a redes
- **Regras de Acesso**: Se a rede for inativada, todos os seus tenants perdem o acesso automaticamente.

#### NetworkTenantImportController
- Importação em lote de tenants via arquivo CSV
- **Colunas permitidas**: `legal_name`, `trade_name`, `document`, `email`, `phone`, `subdomain`, `endereco`, `n_endereco`, `complemento`, `bairro`, `cep`, `estado`, `cidade`
- **Resolução Automática**: Identifica o ID do estado pela sigla ou nome, e a cidade pelo nome (focado no Brasil).
- **Opção de Documento Duplicado**: Permite importar múltiplas clínicas com o mesmo CNPJ/CPF se habilitado.
- **Segurança**: Bloqueia reimportação do mesmo arquivo (via hash) e colunas técnicas proibidas.
- **Processamento**: Cada linha é processada de forma isolada; erros em uma linha não interrompem a importação.
- **E-mail automático**: Envia credenciais de acesso para cada clínica criada com sucesso.

#### PlanController
- CRUD de planos com categorias:
  - `commercial`: Planos padrão para clínicas avulsas.
  - `contractual`: Exclusivos para redes de clínicas (liberação sem assinatura).
  - `sandbox`: Para testes e demonstrações.
- Filtra planos disponíveis na criação de tenant com base na vinculação ou não a uma rede.

#### PlanAccessManagerController
- Gerenciamento de regras de acesso por plano
- Definição de limites:
  - Máximo de usuários admin
  - Máximo de usuários comuns
  - Máximo de médicos
- Associação de funcionalidades aos planos
- Funcionalidades "default" sempre permitidas
- Validação de exclusão (verifica assinaturas ativas)
- Uma regra por plano (validação de duplicidade)

#### SubscriptionController
- CRUD completo de assinaturas
- Renovação de assinaturas
- Sincronização com Asaas
- Listagem de assinaturas por tenant
- Aplicação automática de regras de acesso ao tenant

#### PlanChangeRequestController
- Gerenciamento de solicitações de mudança de plano dos tenants
- Listagem de todas as solicitações (pendentes, aprovadas, rejeitadas)
- Visualização detalhada de cada solicitação
- **Aprovação de solicitações:**
  - Atualiza o plano da assinatura imediatamente
  - Aplica novas regras de acesso ao tenant automaticamente
  - Atualiza todas as faturas pendentes com o novo valor do plano
  - Sincroniza faturas com Asaas (se tiverem provider_id)
  - Atualiza assinatura no Asaas (se gerenciada por cartão de crédito)
  - **Mudança de forma de pagamento:**
    - Se mudou de PIX para Cartão: cancela assinatura PIX e cria nova com cartão
    - Se mudou de Cartão para PIX: cancela assinatura com cartão e gera link de pagamento PIX
    - Se já estiver na forma solicitada: não faz alterações
    - Links de pagamento são gerados seguindo a data de vencimento da fatura
- **Rejeição de solicitações:**
  - Requer motivo obrigatório
  - Registra notas do administrador

#### InvoiceController
- CRUD completo de faturas
- Sincronização manual com Asaas
- Envio automático de notificações via WhatsApp ao criar fatura

#### SystemSettingsController
- Configurações gerais: timezone, país padrão, idioma
- Integrações: Asaas, WhatsApp (Meta), Email (SMTP)
- Teste de conexão para cada serviço
- Atualização de variáveis de ambiente

#### PreTenantController
- Gerenciamento de pré-cadastros de novos tenants
- Listagem com filtros (status, email)
- Visualização detalhada com logs
- Aprovação manual de pré-cadastros
- Cancelamento de pré-cadastros
- Integração com webhook do Asaas

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
| `Tenant` | `tenants` | Clientes (clínicas) - UUID como chave primária (possui `network_id` nullable) |
| `User` | `users` | Usuários da plataforma administrativa |
| `ClinicNetwork` | `clinic_networks` | Redes de clínicas (agrupamento de tenants) |
| `NetworkUser` | `network_users` | Usuários da área administrativa das redes (guard separado) |
| `Plan` | `plans` | Planos de assinatura |
| `PlanAccessRule` | `plan_access_rules` | Regras de acesso por plano (limites e funcionalidades) |
| `SubscriptionFeature` | `subscription_features` | Funcionalidades disponíveis para planos |
| `PlanAccessRuleFeature` | `plan_access_rule_feature` | Relação entre regras e funcionalidades (pivot) |
| `PreTenant` | `pre_tenants` | Pré-cadastros de novos tenants |
| `PreTenantLog` | `pre_tenant_logs` | Logs de eventos dos pré-cadastros |
| `Subscription` | `subscriptions` | Assinaturas dos tenants |
| `Invoices` | `invoices` | Faturas geradas |
| `NotificationOutbox` | `notifications_outbox` | Histórico de notificações |
| `SystemNotification` | `system_notifications` | Notificações do sistema |
| `NotificationTemplate` | `notification_templates` | Templates de notificação |
| `MedicalSpecialtyCatalog` | `medical_specialties_catalog` | Catálogo global de especialidades |
| `Pais`, `Estado`, `Cidade` | `paises`, `estados`, `cidades` | Dados de localização |
| `TenantLocalizacao` | `tenant_localizacoes` | Localização dos tenants |
| `SystemSetting` | `system_settings` | Configurações do sistema |
| `WebhookLog` | `webhook_logs` | Logs de webhooks recebidos |
| `Module` | - | Módulos de acesso (helper) |

### Características Importantes

- `Tenant` estende `Spatie\Multitenancy\Models\Tenant`
- `Tenant` possui métodos para configuração de banco: `getDatabaseName()`, `getDatabaseHost()`, etc.
- `Tenant` possui relacionamento `network()` (belongsTo) - pode pertencer a uma rede ou não (`network_id` nullable)
- `User` (Platform) possui campo `modules` (JSON) para controle de acesso
- `ClinicNetwork` possui relacionamentos `tenants()` (hasMany) e `users()` (hasMany)
- `NetworkUser` utiliza guard `network` separado (não é usuário da Platform nem do Tenant)
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
1. Acesse `/Platform/plans` (requer módulo `plans`)
2. Clique em "Criar Plano"
3. Defina:
   - Nome do plano
   - Descrição
   - Valor mensal (em reais, convertido automaticamente para centavos)
   - Recursos incluídos (texto multilinha, convertido em array)
   - Status (ativo/inativo)

**Gerenciar Regras de Acesso por Plano:**
1. Acesse `/Platform/subscription-access` (requer módulo `plans`)
2. **Criar Regra de Acesso:**
   - Clique em "Criar Regra"
   - Selecione um plano (apenas planos ativos)
   - Defina limites:
     - Máximo de usuários admin
     - Máximo de usuários comuns
     - Máximo de médicos
   - Selecione funcionalidades permitidas:
     - Funcionalidades marcadas como "default" são sempre permitidas
     - Outras funcionalidades podem ser selecionadas
3. **Editar Regra:**
   - Acesse a regra → Editar
   - Modifique limites e funcionalidades
   - **Nota:** O plano não pode ser alterado após criação
4. **Excluir Regra:**
   - A regra só pode ser excluída se não houver assinaturas ativas usando o plano
   - O sistema valida antes de permitir exclusão

**Funcionalidades do Sistema de Regras:**
- Cada plano pode ter apenas uma regra de acesso
- As regras definem limites de recursos (usuários, médicos)
- As regras controlam quais funcionalidades estão disponíveis
- Funcionalidades "default" são sempre permitidas para todos os planos
- O sistema valida se o tenant está dentro dos limites ao criar recursos

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

### 4.1. Solicitações de Mudança de Plano

**Gerenciar Solicitações:**
1. Acesse `/Platform/plan-change-requests` (requer módulo `subscriptions`)
2. Visualize todas as solicitações de mudança de plano dos tenants
3. Filtre por status (pendente, aprovada, rejeitada, cancelada)

**Visualizar Detalhes:**
- Clique em uma solicitação para ver:
  - Dados do tenant
  - Plano atual e plano solicitado
  - Forma de pagamento atual e solicitada
  - Motivo da solicitação
  - Histórico de revisão

**Aprovar Solicitação:**
1. Acesse os detalhes da solicitação
2. Clique em "Aprovar"
3. Opcionalmente, adicione notas do administrador
4. O sistema automaticamente:
   - Atualiza o plano da assinatura
   - Aplica novas regras de acesso ao tenant
   - Atualiza todas as faturas pendentes com o novo valor
   - Sincroniza faturas com Asaas (se aplicável)
   - **Se a forma de pagamento mudou:**
     - PIX → Cartão: Cancela assinatura PIX e cria nova com cartão no Asaas
     - Cartão → PIX: Cancela assinatura com cartão e gera link de pagamento PIX
     - Outras mudanças: Gera link de pagamento apropriado
   - Atualiza assinatura no Asaas (se gerenciada por cartão)

**Rejeitar Solicitação:**
1. Acesse os detalhes da solicitação
2. Clique em "Rejeitar"
3. **Obrigatório:** Informe o motivo da rejeição
4. A solicitação será marcada como rejeitada e o tenant será notificado

**Importante:**
- Apenas solicitações com status `pending` podem ser aprovadas ou rejeitadas
- Ao aprovar, todas as mudanças são aplicadas imediatamente
- Faturas pendentes são atualizadas automaticamente
- Se a forma de pagamento não mudou, nenhuma alteração é feita

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
   - **WhatsApp (Meta / Z-API / WAHA)**: credenciais específicas de cada provedor
   - **Email (SMTP)**: Host, Porta, Username, Password, From Address, From Name
4. **Testes de Integração WhatsApp (Meta / Z-API / WAHA)**:
   - Cada provedor agora possui dois botões:
     1. **Testar Conexão** (`GET /Platform/settings/test/{service}`) que valida credenciais e resposta básica da API.
     2. **Testar Envio** que abre um formulário inline com número, mensagem, badge de status e feedback textual (Meta/Z-API usam o mesmo layout do WAHA). O formulário envia via AJAX para:
        - `POST /Platform/settings/test/meta/send` (Meta)
        - `POST /Platform/settings/test/zapi/send` (Z-API)
        - `POST /Platform/settings/test/waha/send` (WAHA)
   - Os IDs usados na view são exatamente os esperados pelo JavaScript (`btn-toggle-*`, `*-send-form`, `*-test-number`, `*-test-message-input`, `btn-send-*-test`, `*-send-badge`, `*-send-message`), garantindo comportamento uniforme entre os três providers.
   - Os métodos do `SystemSettingsController` (`testMetaSend`, `testZapiSend`, `testWahaSend`) respondem sempre com JSON e usam apenas o provider específico, ignorando `WHATSAPP_PROVIDER` global.

**Nota:** Configurações definidas aqui têm prioridade sobre variáveis de ambiente. As configurações são salvas na tabela `system_settings` e também atualizadas no arquivo `.env`.

### 7. Templates de Notificação

**Gerenciar Templates:**
1. Acesse `/Platform/notification-templates` (requer módulo `notification_templates`)
2. Visualize todos os templates disponíveis no sistema
3. Edite templates existentes
4. Ative/desative templates
5. Teste envio de templates
6. Restaure templates deletados (soft delete)

**Funcionalidades:**
- Criação e edição de templates de notificação
- Ativação/desativação de templates
- Teste de envio de templates
- Soft delete (templates podem ser restaurados)
- Templates podem ser usados pelos tenants para envio de notificações personalizadas

### 8. Catálogo de Especialidades Médicas

**Gerenciar Especialidades:**
1. Acesse `/Platform/medical_specialties_catalog` (requer módulo `medical_specialties_catalog`)
2. Crie, edite ou remova especialidades médicas
3. As especialidades ficam disponíveis para todos os tenants
4. Os tenants podem importar especialidades deste catálogo

### 9. Localização (Países, Estados, Cidades)

**Visualizar Localizações:**
1. Acesse `/Platform/paises`, `/Platform/estados` ou `/Platform/cidades` (requer módulo `locations`)
2. Visualize dados de localização cadastrados
3. **Nota:** Estas rotas são read-only (apenas visualização)

**API de Localização:**
- `GET /Platform/api/estados/{pais}` - Retorna estados de um país (JSON)
- `GET /Platform/api/cidades/{estado}` - Retorna cidades de um estado (JSON)
- Utilizadas em formulários para seleção dinâmica

### 10. Gerenciamento de Usuários da Platform

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

### 11. Perfil do Usuário

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

### 12. Módulo de Pré-Cadastro

**Visão Geral:**
O módulo de pré-cadastro permite que novos clientes se cadastrem através de uma landing page pública. Após o pagamento via Asaas, o sistema cria automaticamente o tenant e a assinatura.

**Fluxo do Pré-Cadastro:**
1. **Cadastro Público** (`POST /pre-register`):
   - Cliente preenche formulário na landing page
   - Validações:
     - Subdomain deve ser único (verifica em `tenants`)
     - Plano deve estar ativo
     - Rate limit: 10 requisições por minuto
   - Sistema cria registro em `pre_tenants` com status `pending`
   - Cria cliente no Asaas automaticamente
   - Gera cobrança PIX com:
     - Vencimento: 5 dias
     - Valor: baseado no plano selecionado
     - Descrição: "Pré-cadastro - Plano {nome}"
   - Retorna JSON com:
     - `payment_url` - Link para pagamento
     - `payment_id` - ID do pagamento no Asaas
     - `pre_tenant_id` - ID do pré-cadastro

2. **Pagamento Confirmado** (via Webhook):
   - Webhook do Asaas (`POST /webhook/asaas/pre-registration`) recebe eventos:
     - `PAYMENT_CONFIRMED` ou `PAYMENT_RECEIVED` → Processa criação do tenant
     - `PAYMENT_REFUNDED` ou `PAYMENT_CANCELED` → Cancela pré-cadastro
   - Sistema processa automaticamente via `PreTenantProcessorService`:
     - Marca pré-cadastro como `paid`
     - Cria banco de dados PostgreSQL do tenant
     - Cria tenant completo com todos os dados
     - Cria assinatura ativa vinculada ao plano
     - Envia email de boas-vindas com credenciais de acesso
     - Registra todos os eventos em logs

3. **Gerenciamento na Platform**:
   - Acesse `/Platform/pre-tenants` (requer módulo `pre_tenants`)
   - Visualize todos os pré-cadastros com filtros
   - Aprove manualmente se necessário
   - Cancele pré-cadastros

**Gerenciar Pré-Cadastros:**
1. Acesse `/Platform/pre-tenants` (requer módulo `pre_tenants`)
2. **Filtros disponíveis:**
   - Por status (pending, paid, canceled)
   - Por email
3. **Visualizar Detalhes:**
   - Clique em um pré-cadastro
   - Visualize:
     - Dados cadastrais completos
     - Plano selecionado
     - Localização
     - Status do pagamento
     - Logs de eventos
     - IDs do Asaas (customer_id, payment_id)
4. **Aprovar Manualmente:**
   - Acesse o pré-cadastro → Ações → "Aprovar"
   - Força criação do tenant mesmo sem pagamento confirmado
   - Útil para casos especiais ou testes
5. **Cancelar:**
   - Acesse o pré-cadastro → Ações → "Cancelar"
   - Marca como cancelado
   - Registra evento no log

**Status dos Pré-Cadastros:**
- `pending` - Aguardando pagamento
- `paid` - Pago e processado (tenant criado)
- `canceled` - Cancelado

**Logs de Eventos:**
- Cada ação gera um log em `pre_tenant_logs`
- Eventos registrados:
  - `pre_register_created` - Pré-cadastro criado
  - `payment_created` - Pagamento criado no Asaas
  - `payment_confirmed` - Pagamento confirmado
  - `tenant_created` - Tenant criado
  - `subscription_created` - Assinatura criada
  - `manual_approval` - Aprovação manual
  - `manual_cancellation` - Cancelamento manual
  - `payment_canceled` - Pagamento cancelado/estornado
  - `processing_error` - Erro no processamento

**Processamento Automático:**
O serviço `PreTenantProcessorService` executa as seguintes etapas ao processar um pré-cadastro pago:
1. Valida e sanitiza subdomain (gera automaticamente se não fornecido)
2. Verifica disponibilidade do subdomain (adiciona sufixo aleatório se necessário)
3. Gera configuração do banco de dados (nome, usuário, senha)
4. Cria tenant no banco central
5. Cria banco de dados PostgreSQL isolado
6. Executa migrations do tenant
7. Cria usuário admin padrão
8. Cria localização do tenant (se informada)
9. Cria assinatura ativa vinculada ao plano
10. Sincroniza tenant com Asaas
11. Envia email de boas-vindas com credenciais

**Validações:**
- Subdomain deve ser único (verifica em `tenants`)
- Plano deve estar ativo
- Rate limit: 10 requisições por minuto na rota pública
- Webhook valida token do Asaas antes de processar

### 13. Monitor de Kiosk

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
  ASAAS_API_URL=https://api-sandbox.asaas.com/v3
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
- `plan_access_rules` - Regras de acesso por plano
- `subscription_features` - Funcionalidades disponíveis
- `plan_access_rule_feature` - Relação entre regras e funcionalidades
- `pre_tenants` - Pré-cadastros de novos tenants
- `pre_tenant_logs` - Logs de eventos dos pré-cadastros
- `subscriptions` - Assinaturas ativas
- `invoices` - Faturas geradas
- `plan_change_requests` - Solicitações de mudança de plano
- `users` - Usuários da platform
- `paises`, `estados`, `cidades` - Dados de localização
- `medical_specialties_catalog` - Catálogo de especialidades
- `notifications_outbox` - Histórico de notificações
- `system_notifications` - Notificações do sistema
- `system_settings` - Configurações
- `webhook_logs` - Logs de webhooks
- `tenant_localizacoes` - Localização dos tenants
- `plan_change_requests` - Solicitações de mudança de plano

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

**Última atualização:** 2025-12-14

**Nota:** Esta documentação foi revisada e atualizada com base no código implementado, incluindo:
- Todas as rotas atuais da Platform
- Detalhamento completo dos controllers e suas funcionalidades
- Informações sobre controle de acesso por módulos
- Dashboard com estatísticas detalhadas
- Funcionalidades de sincronização com Asaas
- Monitor de kiosk público
- APIs auxiliares de localização
- Configurações do sistema com testes de conexão
- Sistema de gerenciamento de regras de acesso por plano (`PlanAccessManagerController`)
- Models `PlanAccessRule`, `SubscriptionFeature` e `PlanAccessRuleFeature`
- Rotas `/Platform/subscription-access` para gerenciar limites e funcionalidades dos planos
- **NOVO:** Módulo de Pré-Cadastro (`PreTenantController`)
- **NOVO:** Models `PreTenant` e `PreTenantLog`
- **NOVO:** Rotas públicas `/pre-register` e `/webhook/asaas/pre-registration`
- **NOVO:** Serviço `PreTenantProcessorService` para processamento automático
- **NOVO:** Integração completa com Asaas para pagamentos de pré-cadastro
- **NOVO:** Sistema de Solicitações de Mudança de Plano (`PlanChangeRequestController`)
- **NOVO:** Model `PlanChangeRequest` para gerenciar solicitações de mudança de plano
- **NOVO:** Aprovação automática de mudanças com atualização de faturas e forma de pagamento
- **NOVO:** Suporte a mudança de forma de pagamento (PIX, Cartão de Crédito, etc.)
- **NOVO:** Geração automática de links de pagamento ao mudar forma de pagamento
- Lista completa e atualizada de módulos disponíveis
