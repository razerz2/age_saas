# 📐 Arquitetura do Sistema - Agendamento SaaS

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Estrutura de Pastas](#estrutura-de-pastas)
3. [Frontend Tenant (Views/Assets)](#frontend-tenant-viewsassets)
4. [Rotas (Platform e Tenant)](#rotas-platform-e-tenant)
5. [Controllers](#controllers)
6. [Models](#models)
7. [Migrações](#migrações)
8. [Middlewares](#middlewares)
9. [Lógica Multitenant](#lógica-multitenant)

---

## 🎯 Visão Geral

Este é um sistema **SaaS (Software as a Service)** de agendamento médico construído com **Laravel 10** e utilizando o pacote **Spatie Laravel Multitenancy**. O sistema possui três áreas principais:

- **Platform**: Área administrativa central para gerenciar tenants, planos, assinaturas, faturas, etc.
- **Tenant**: Área específica de cada cliente (clínica) com funcionalidades de agendamento, pacientes, médicos, etc.
- **Rede de Clínicas**: Área administrativa para redes de clínicas agregarem dados de múltiplos tenants (majoritariamente read-only)

O sistema utiliza **multitenancy com banco de dados separado por tenant**, onde cada cliente possui seu próprio banco de dados PostgreSQL isolado. As redes de clínicas permitem que múltiplos tenants sejam agrupados e visualizados de forma unificada sem quebrar o isolamento de dados.

---

## 📁 Estrutura de Pastas

```
agendamento-saas/
├── app/
│   ├── Console/Commands/          # Comandos Artisan customizados
│   ├── Exceptions/                # Tratamento de exceções
│   ├── Helpers/                   # Funções auxiliares (helpers.php)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/              # Controllers de autenticação (Laravel Breeze)
│   │   │   ├── Platform/          # Controllers da área administrativa
│   │   │   ├── Tenant/            # Controllers da área do tenant
│   │   │   ├── NetworkAdmin/      # Controllers da área administrativa da rede
│   │   │   ├── Public/            # Controllers públicos (rede de clínicas)
│   │   │   └── Webhook/           # Controllers de webhooks (Asaas)
│   │   ├── Middleware/            # Middlewares customizados
│   │   ├── Requests/              # Form Requests (validação)
│   │   └── Kernel.php             # Registro de middlewares
│   ├── Logging/
│   │   └── TenantLogChannel.php   # Canal de log específico para tenants
│   ├── Models/
│   │   ├── Platform/              # Models do banco central (landlord)
│   │   └── Tenant/                # Models do banco do tenant
│   ├── Multitenancy/
│   │   └── Tasks/
│   │       └── SwitchTenantTask.php  # Task para trocar conexão de banco
│   ├── Observers/                 # Model Observers (ex: InvoiceObserver)
│   ├── Providers/                 # Service Providers
│   │   └── TenantOverrideProvider.php  # Override do model Tenant do Spatie
│   ├── Services/                  # Serviços de negócio
│   │   ├── AsaasService.php       # Integração com gateway de pagamento
│   │   ├── SystemNotificationService.php
│   │   ├── TenantProvisioner.php  # Provisionamento de banco de dados
│   │   ├── Platform/
│   │   │   ├── TenantCreatorService.php # Serviço central de criação de tenants
│   │   │   └── TenantPlanService.php    # Aplicação de regras de plano
│   │   ├── WhatsAppService.php    # Integração WhatsApp (global)
│   │   ├── MailTenantService.php   # Envio de emails (tenant ou global)
│   │   ├── NotificationService.php # Notificações centralizadas
│   │   └── WhatsappTenantService.php # Envio WhatsApp (tenant ou global)
│   ├── TenantFinder/
│   │   └── PathTenantFinder.php   # Identifica tenant pelo path (/t/{tenant})
│   └── View/Components/            # Blade Components
├── config/
│   ├── multitenancy.php           # Configuração do Spatie Multitenancy
│   ├── auth.php                   # Guards (web e tenant)
│   └── database.php               # Conexões de banco (landlord e tenant)
├── database/
│   ├── migrations/                # Migrações do banco central
│   │   └── tenant/                # Migrações dos tenants
│   └── seeders/                   # Seeders
├── routes/
│   ├── web.php                    # Rotas da Platform
│   ├── tenant.php                 # Rotas dos Tenants
│   ├── network.php                # Rotas públicas da rede de clínicas
│   ├── network_admin.php          # Rotas administrativas da rede
│   ├── api.php                    # Rotas da API (Sanctum)
│   └── auth.php                   # Rotas de autenticação (Breeze)
└── resources/views/                # Views Blade
|-- resources/css/tenant/           # CSS da area tenant (app.css + pages/*.css)
|-- resources/js/tenant/            # JS da area tenant (app.js + pages/*.js)
```

---

## 🎨 Frontend Tenant (Views/Assets)

### Padrão de Views
- Cada view Tenant deve declarar `@section('page', '<modulo>')`.
- Não usar `<style>`/`<script>` inline nas views migradas.
- Não usar `@push('styles')`/`@push('scripts')` nas views migradas.
- Eventos devem usar `data-*` e serem vinculados no JS do módulo.

### Assets por Módulo
- JS: `resources/js/tenant/pages/<modulo>.js` com `export function init()`.
- CSS: `resources/css/tenant/pages/<modulo>.css`.
- Imports CSS centralizados em `resources/css/tenant/app.css`.

### Carregamento Dinâmico
- `resources/js/tenant/app.js` lê `data-page` e faz import dinâmico do módulo.

---

## 🛣️ Rotas (Platform e Tenant)

### **Rotas da Platform** (`routes/web.php`)

A área administrativa central utiliza o prefixo `/Platform` e o guard `web`:

```php
// Estrutura geral:
/Platform/dashboard                    # Dashboard principal
/Platform/tenants                      # CRUD de tenants
/Platform/plans                        # CRUD de planos
/Platform/subscriptions                # CRUD de assinaturas
/Platform/invoices                     # CRUD de faturas
/Platform/users                        # CRUD de usuários da platform
/Platform/settings                     # Configurações do sistema
/Platform/profile                      # Perfil do usuário logado

// Rotas especiais:
POST /Platform/tenants/{tenant}/sync           # Sincronizar tenant com Asaas
POST /Platform/tenants/{tenant}/send-credentials # Enviar credenciais do tenant
GET  /Platform/tenants/{tenant}/api-tokens     # Tokens de API do tenant
GET  /Platform/clinic-networks/import-all      # Importação geral de tenants
POST /Platform/clinic-networks/import-all      # Processar importação geral
GET  /Platform/clinic-networks/{network}/import # Importação para rede específica
POST /Platform/clinic-networks/{network}/import # Processar importação para rede
POST /Platform/subscriptions/{id}/renew        # Renovar assinatura
POST /Platform/subscriptions/{subscription}/sync # Sincronizar assinatura com Asaas
POST /Platform/invoices/{invoice}/sync         # Sincronizar fatura manualmente
GET  /Platform/plan-change-requests            # Listar solicitações de mudança de plano
GET  /Platform/plan-change-requests/{id}       # Visualizar detalhes da solicitação
POST /Platform/plan-change-requests/{id}/approve # Aprovar solicitação
POST /Platform/plan-change-requests/{id}/reject  # Rejeitar solicitação
POST /Platform/whatsapp/send                   # Enviar mensagem WhatsApp
POST /Platform/whatsapp/invoice/{invoice}      # Enviar notificação de fatura
GET  /Platform/zapi                            # Interface Z-API
POST /Platform/zapi/send                       # Enviar mensagem via Z-API
GET  /Platform/api/estados/{pais}              # API: Estados por país
GET  /Platform/api/cidades/{estado}            # API: Cidades por estado
GET  /Platform/system_notifications/json        # API: Notificações em JSON (últimas 5)
GET  /Platform/two-factor                      # Configuração de 2FA
POST /Platform/two-factor/generate-secret      # Gerar secret 2FA
POST /Platform/two-factor/confirm              # Confirmar 2FA
POST /Platform/two-factor/disable              # Desabilitar 2FA
GET  /Platform/email-layouts                   # Gerenciar layouts de email
POST /Platform/notification-templates/{id}/restore # Restaurar template
POST /Platform/notification-templates/{id}/test # Testar envio de template
POST /Platform/notification-templates/{id}/toggle # Alternar status do template

// Rotas Públicas (sem autenticação):
GET  /                                       # Landing page (home)
GET  /funcionalidades                        # Landing page (funcionalidades)
GET  /planos                                 # Landing page (planos)
GET  /planos/json/{id}                       # API: Dados do plano em JSON
GET  /contato                                # Landing page (contato)
GET  /manual                                 # Landing page (manual)
POST /pre-cadastro                           # Criar pré-cadastro (landing page)
GET  /kiosk/monitor                          # Monitor de kiosk
GET  /kiosk/monitor/data                     # Dados do monitor (API)
POST /webhook/asaas                          # Webhook do Asaas (platform)
POST /webhook/asaas/pre-registration         # Webhook do Asaas para pré-cadastros
GET  /google/callback                        # Callback do Google Calendar OAuth (rota global)
GET  /politica-de-privacidade                # Política de privacidade
GET  /termos-de-servico                      # Termos de serviço
```

**Middleware aplicado:**
- `auth` - Autenticação obrigatória
- `module.access:{modulo}` - Controle de acesso por módulo (ex: `tenants`, `plans`, `invoices`)

### **Rotas dos Tenants** (`routes/tenant.php`)

As rotas dos tenants são divididas em seções baseadas no prefixo da URL:

#### 1. **Login do Tenant** (`/customer/{slug}/login`)
```php
GET  /customer/{slug}/login              # Formulário de login
POST /customer/{slug}/login              # Processar login
POST /customer/{slug}/logout             # Logout
GET  /customer/{slug}/two-factor-challenge # Desafio 2FA
POST /customer/{slug}/two-factor-challenge # Validar 2FA
```

**Middleware:** `tenant-web` (detecta tenant pelo path)

#### 2. **Área Autenticada do Tenant** (`/workspace/{slug}/*`)
```php
/workspace/{slug}/dashboard                   # Dashboard do tenant
/workspace/{slug}/profile                     # Perfil do usuário
/workspace/{slug}/users                       # CRUD de usuários do tenant
/workspace/{slug}/doctors                     # CRUD de médicos
/workspace/{slug}/specialties                 # CRUD de especialidades médicas
/workspace/{slug}/patients                    # CRUD de pacientes
/workspace/{slug}/calendars                   # CRUD de calendários
/workspace/{slug}/business-hours              # CRUD de horários comerciais
/workspace/{slug}/appointment-types           # CRUD de tipos de consulta
/workspace/{slug}/appointments                # CRUD de agendamentos
/workspace/{slug}/forms                       # CRUD de formulários
/workspace/{slug}/responses                   # CRUD de respostas de formulários
/workspace/{slug}/integrations                # CRUD de integrações
/workspace/{slug}/integrations/google         # Integração Google Calendar
/workspace/{slug}/integrations/apple          # Integração Apple Calendar
/workspace/{slug}/oauth-accounts              # CRUD de contas OAuth
/workspace/{slug}/calendar-sync               # Sincronização de calendário
/workspace/{slug}/notifications               # Notificações do tenant
/workspace/{slug}/settings                    # Configurações do tenant
/workspace/{slug}/subscription                # Detalhes da assinatura (apenas admins)
/workspace/{slug}/plan-change-request/create  # Solicitar mudança de plano
/workspace/{slug}/plan-change-request         # POST: Processar solicitação
/workspace/{slug}/agendamentos/recorrentes    # Agendamentos recorrentes
/workspace/{slug}/appointments/online         # Agendamentos online
/workspace/{slug}/atendimento                 # Atendimento médico
/workspace/{slug}/finance                     # Dashboard financeiro
/workspace/{slug}/finance/accounts            # Contas financeiras
/workspace/{slug}/finance/categories          # Categorias financeiras
/workspace/{slug}/finance/transactions        # Transações financeiras
/workspace/{slug}/finance/charges             # Cobranças
/workspace/{slug}/finance/commissions         # Comissões
/workspace/{slug}/finance/reports             # Relatórios financeiros
/workspace/{slug}/two-factor                  # Configuração 2FA
```

**Middleware aplicado (em ordem):**
1. `web` - Sessão e cookies
2. `persist.tenant` - Persiste tenant na sessão
3. `tenant.from.guard` - Ativa tenant a partir do usuário autenticado
4. `ensure.guard` - Garante uso do guard correto (`tenant`)
5. `tenant.auth` - Verifica autenticação do tenant

#### 3. **Área Pública de Agendamento** (`/customer/{slug}/agendamento/*`)

**Rotas Públicas (sem autenticação):**
```php
GET  /customer/{slug}/agendamento/identificar    # Identificar paciente
POST /customer/{slug}/agendamento/identificar    # Processar identificação
GET  /customer/{slug}/agendamento/cadastro       # Cadastro de paciente
POST /customer/{slug}/agendamento/cadastro       # Processar cadastro
GET  /customer/{slug}/agendamento/criar          # Criar agendamento
POST /customer/{slug}/agendamento/criar          # Processar agendamento
GET  /customer/{slug}/agendamento/sucesso/{appointment_id?}  # Página de sucesso
GET  /customer/{slug}/agendamento/{appointment_id} # Visualizar agendamento

# APIs públicas para agendamento
GET  /customer/{slug}/agendamento/api/doctors/{doctorId}/calendars
GET  /customer/{slug}/agendamento/api/doctors/{doctorId}/appointment-types
GET  /customer/{slug}/agendamento/api/doctors/{doctorId}/specialties
GET  /customer/{slug}/agendamento/api/doctors/{doctorId}/available-slots
GET  /customer/{slug}/agendamento/api/doctors/{doctorId}/business-hours

# Formulários públicos
GET  /customer/{slug}/formulario/{form}/responder                    # Responder formulário
POST /customer/{slug}/formulario/{form}/responder                    # Salvar resposta
GET  /customer/{slug}/formulario/{form}/resposta/{response}/sucesso   # Página de sucesso
```

**Middleware:** `tenant-web` (detecta tenant pelo path)

#### 4. **Webhooks e Páginas Públicas do Financeiro** (`/t/{slug}/*`)

**Rotas Públicas (webhooks e pagamentos):**
```php
POST /t/{slug}/webhooks/asaas                  # Webhook do Asaas (financeiro)
GET  /t/{slug}/pagamento/{charge}              # Página pública de pagamento
GET  /t/{slug}/pagamento/{charge}/sucesso      # Página de sucesso do pagamento
GET  /t/{slug}/pagamento/{charge}/erro         # Página de erro do pagamento
```

**Middleware:** `tenant-web`, `throttle.asaas.webhook`, `verify.asaas.webhook.secret`, `verify.asaas.webhook.ip`

#### 5. **Portal do Paciente** (`routes/patient_portal.php`)

**Rotas Públicas (autenticação com slug na URL):**
```php
GET  /customer/{slug}/paciente/login              # Formulário de login
POST /customer/{slug}/paciente/login              # Processar login
GET  /customer/{slug}/paciente/esqueci-senha      # Formulário de recuperação de senha
GET  /customer/{slug}/paciente/resetar-senha/{token} # Formulário de resetar senha
```

**Rotas Autenticadas (com slug na URL):**
```php
GET  /workspace/{slug}/paciente/dashboard                      # Dashboard do paciente
GET  /workspace/{slug}/paciente/agendamentos                   # Lista de agendamentos
GET  /workspace/{slug}/paciente/agendamentos/criar             # Criar agendamento
POST /workspace/{slug}/paciente/agendamentos                   # Processar criação
GET  /workspace/{slug}/paciente/agendamentos/{id}/editar       # Editar agendamento
PUT  /workspace/{slug}/paciente/agendamentos/{id}              # Atualizar agendamento
POST /workspace/{slug}/paciente/agendamentos/{id}/cancelar     # Cancelar agendamento
GET  /workspace/{slug}/paciente/notificacoes                   # Notificações do paciente
GET  /workspace/{slug}/paciente/perfil                         # Perfil do paciente
POST /workspace/{slug}/paciente/perfil                         # Atualizar perfil
POST /workspace/{slug}/paciente/logout                         # Logout
GET  /workspace/{slug}/paciente/logout                         # Logout (GET)
```

**Middleware aplicado (rotas públicas):**
- `tenant-web`, `ensure.guard`

**Middleware aplicado (rotas autenticadas - em ordem):**
1. `web` - Sessão e cookies
2. `persist.tenant` - Persiste tenant na sessão
3. `tenant.from.guard` - Ativa tenant a partir do usuário autenticado
4. `ensure.guard` - Garante uso do guard correto (`tenant`)
5. `patient.auth` - Verifica autenticação do paciente

#### 6. **Rede de Clínicas - Pública** (`routes/network.php`)

**Rotas Públicas (acessadas via subdomínio da rede):**
```php
GET  /                           # Home da rede (institucional)
GET  /medicos                    # Lista pública de médicos (agregado)
GET  /unidades                   # Lista de unidades (tenants da rede)
```

**Acesso:** Via subdomínio (ex: `rede.allsync.com.br`)
**Middleware:** `require.network` - Garante que rede foi detectada

#### 7. **Rede de Clínicas - Área Administrativa** (`routes/network_admin.php`)

**Rotas Públicas (login):**
```php
GET  /login                      # Formulário de login
POST /login                      # Processar login
POST /logout                     # Logout
```

**Rotas Autenticadas (área administrativa):**
```php
GET  /dashboard                  # Dashboard com KPIs agregados
GET  /clinicas                   # Lista de clínicas (read-only)
GET  /medicos                    # Lista de médicos (read-only)
GET  /agendamentos               # Métricas de agendamentos (read-only)
GET  /financeiro                 # Indicadores financeiros (read-only, se permitido)
GET  /configuracoes              # Configurações da rede (edição permitida)
POST /configuracoes              # Atualizar configurações
```

**Acesso:** Via subdomínio administrativo (ex: `admin.rede.allsync.com.br`)
**Guard:** `network` (separado de Platform e Tenant)
**Middleware aplicado:**
1. `web` - Sessão e cookies
2. `ensure.network.context` - Garante que rede foi detectada
3. `network.auth` - Verifica autenticação do usuário da rede

**Características:**
- Área **majoritariamente read-only** - apenas configurações podem ser editadas
- Agrega dados de múltiplos tenants usando serviços especializados
- Nunca edita dados clínicos diretamente
- Mantém isolamento de bancos de dados

---

## 🎮 Controllers

### **Controllers da Platform** (`app/Http/Controllers/Platform/`)

| Controller | Responsabilidade |
|------------|------------------|
| `DashboardController` | Dashboard principal com estatísticas |
| `TenantController` | CRUD de tenants + sincronização com Asaas |
| `ClinicNetworkController` | CRUD de redes de clínicas + vinculação de tenants |
| `ApiTenantTokenController` | Gerenciamento de tokens de API para bots |
| `PlanController` | CRUD de planos de assinatura |
| `SubscriptionController` | CRUD de assinaturas + renovação |
| `InvoiceController` | CRUD de faturas + sincronização manual |
| `UserController` | CRUD de usuários da platform + reset de senha |
| `MedicalSpecialtyCatalogController` | Catálogo de especialidades médicas |
| `NotificationOutboxController` | Histórico de notificações enviadas |
| `SystemNotificationController` | Notificações do sistema |
| `NotificationTemplateController` | Templates de notificação |
| `EmailLayoutController` | Gerenciamento de layouts de email |
| `SystemSettingsController` | Configurações gerais e integrações |
| `PaisController`, `EstadoController`, `CidadeController` | CRUD de localização |
| `LocationController` | API de localização (estados/cidades) |
| `WhatsAppController` | Envio de mensagens WhatsApp |
| `ZApiController` | Integração com Z-API (WhatsApp) |
| `PlanAccessManagerController` | Gerenciamento de regras de acesso por plano |
| `PlanChangeRequestController` | Gerenciamento de solicitações de mudança de plano |
| `PreTenantController` | Gerenciamento de pré-cadastros |
| `KioskMonitorController` | Monitor de kiosk |
| `LandingController` | Landing page pública |
| `BotApi/AppointmentBotApiController` | API de agendamentos para bots |
| `BotApi/AvailabilityBotApiController` | API de disponibilidade para bots |
| `BotApi/PatientBotApiController` | API de pacientes para bots |

### **Controllers da Rede de Clínicas**

#### **Controllers Públicos** (`app/Http/Controllers/Public/`)

| Controller | Responsabilidade |
|------------|------------------|
| `NetworkPublicController` | Páginas públicas da rede (home, médicos, unidades) |

#### **Controllers Administrativos da Rede** (`app/Http/Controllers/NetworkAdmin/`)

| Controller | Responsabilidade |
|------------|------------------|
| `NetworkAuthController` | Autenticação exclusiva da rede (login/logout) |
| `NetworkDashboardController` | Dashboard com KPIs agregados |
| `NetworkClinicController` | Lista de clínicas da rede (read-only) |
| `NetworkDoctorController` | Lista de médicos agregados (read-only) |
| `NetworkAppointmentController` | Métricas de agendamentos (read-only) |
| `NetworkFinanceController` | Indicadores financeiros agregados (read-only, com permissão) |
| `NetworkSettingsController` | Configurações da rede (edição permitida) |

### **Controllers dos Tenants** (`app/Http/Controllers/Tenant/`)

| Controller | Responsabilidade |
|------------|------------------|
| `Auth/LoginController` | Autenticação específica do tenant |
| `Auth/TwoFactorChallengeController` | Desafio de autenticação de dois fatores |
| `TwoFactorController` | Configuração de 2FA |
| `DashboardController` | Dashboard do tenant |
| `ProfileController` | Perfil do usuário autenticado |
| `UserController` | CRUD de usuários do tenant |
| `UserDoctorPermissionController` | Permissões de médicos para usuários |
| `DoctorController` | CRUD de médicos |
| `DoctorSettingsController` | Configurações específicas de médicos |
| `MedicalSpecialtyController` | Especialidades médicas do tenant |
| `PatientController` | CRUD de pacientes + gerenciamento de login |
| `CalendarController` | CRUD de calendários |
| `BusinessHourController` | Horários comerciais |
| `AppointmentTypeController` | Tipos de consulta |
| `AppointmentController` | CRUD de agendamentos + eventos do calendário |
| `RecurringAppointmentController` | Agendamentos recorrentes |
| `OnlineAppointmentController` | Agendamentos online com instruções |
| `MedicalAppointmentController` | Atendimento médico (sessão de atendimento) |
| `FormController` | CRUD de formulários + seções/perguntas/opções |
| `FormResponseController` | Respostas de formulários + respostas individuais |
| `PublicFormController` | Formulários públicos para pacientes responderem |
| `PublicAppointmentController` | Agendamento público (página pública) |
| `PublicPatientController` | Identificação de paciente (público) |
| `PublicPatientRegisterController` | Cadastro de paciente (público) |
| `IntegrationController` | Integrações gerais |
| `Integrations/GoogleCalendarController` | Integração Google Calendar |
| `Integrations/AppleCalendarController` | Integração Apple Calendar (iCloud) |
| `OAuthAccountController` | Contas OAuth conectadas |
| `CalendarSyncStateController` | Estado de sincronização de calendário |
| `NotificationController` | Notificações do tenant |
| `SettingsController` | Configurações do tenant |
| `SubscriptionController` | Detalhes da assinatura do tenant |
| `PlanChangeRequestController` | Solicitação de mudança de plano |
| `PaymentController` | Páginas públicas de pagamento |
| `AsaasWebhookController` | Webhook do Asaas (módulo financeiro) |
| `FinanceController` | Dashboard do módulo financeiro |
| `FinanceSettingsController` | Configurações financeiras |
| `Finance/FinancialAccountController` | Contas financeiras |
| `Finance/FinancialCategoryController` | Categorias financeiras |
| `Finance/FinancialTransactionController` | Transações financeiras |
| `Finance/FinancialChargeController` | Cobranças |
| `Finance/DoctorCommissionController` | Comissões de médicos |
| `Finance/Reports/FinanceReportController` | Relatórios financeiros (índice) |
| `Finance/Reports/CashFlowReportController` | Relatório de fluxo de caixa |
| `Finance/Reports/IncomeExpenseReportController` | Relatório de receitas e despesas |
| `Finance/Reports/ChargesReportController` | Relatório de cobranças |
| `Finance/Reports/PaymentsReportController` | Relatório de pagamentos |
| `Finance/Reports/CommissionsReportController` | Relatório de comissões |
| `Reports/ReportController` | Índice de relatórios |
| `Reports/AppointmentReportController` | Relatório de agendamentos |
| `Reports/DoctorReportController` | Relatório de médicos |
| `Reports/FormReportController` | Relatório de formulários |
| `Reports/NotificationReportController` | Relatório de notificações |
| `Reports/PatientReportController` | Relatório de pacientes |
| `Reports/PortalReportController` | Relatório do portal |
| `Reports/RecurringReportController` | Relatório de agendamentos recorrentes |
| `PatientPortal/AuthController` | Autenticação do portal do paciente |
| `PatientPortal/DashboardController` | Dashboard do portal do paciente |
| `PatientPortal/AppointmentController` | Agendamentos do portal do paciente |
| `PatientPortal/NotificationController` | Notificações do portal do paciente |
| `PatientPortal/ProfileController` | Perfil do paciente |

---

## 🗄️ Models

### **Models da Platform** (`app/Models/Platform/`)

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
| `NotificationTemplate` | `notification_templates` | Templates de notificação |
| `MedicalSpecialtyCatalog` | `medical_specialties_catalog` | Catálogo global de especialidades |
| `Pais`, `Estado`, `Cidade` | `paises`, `estados`, `cidades` | Dados de localização |
| `TenantLocalizacao` | `tenant_localizacoes` | Localização dos tenants |
| `SystemSetting` | `system_settings` | Configurações do sistema |
| `WebhookLog` | `webhook_logs` | Logs de webhooks recebidos |
| `PlanAccessRule` | `plan_access_rules` | Regras de acesso por plano |
| `SubscriptionFeature` | `subscription_features` | Funcionalidades disponíveis para planos |
| `PlanAccessRuleFeature` | `plan_access_rule_feature` | Relação entre regras e funcionalidades |
| `PreTenant` | `pre_tenants` | Pré-cadastros de novos tenants |
| `PreTenantLog` | `pre_tenant_logs` | Logs de eventos dos pré-cadastros |
| `PlanChangeRequest` | `plan_change_requests` | Solicitações de mudança de plano |
| `EmailLayout` | `email_layouts` | Layouts de email personalizados |
| `ApiTenantToken` | `api_tenant_tokens` | Tokens de API para bots |
| `TenantAdmin` | `tenant_admins` | Administradores de tenants |
| `TwoFactorCode` | `two_factor_codes` | Códigos de autenticação de dois fatores |
| `ClinicNetwork` | `clinic_networks` | Redes de clínicas (agrupamento de tenants) |
| `NetworkUser` | `network_users` | Usuários da área administrativa da rede |
| `Module` | - | Módulos de acesso (helper) |

**Características importantes:**
- `Tenant` estende `Spatie\Multitenancy\Models\Tenant`
- `Tenant` possui métodos para configuração de banco: `getDatabaseName()`, `getDatabaseHost()`, etc.
- `Tenant` possui relacionamento `network()` (belongsTo) e `network_id` (nullable)
- `User` (Platform) possui campo `modules` (JSON) para controle de acesso
- `ClinicNetwork` possui relacionamentos `tenants()` (hasMany) e `users()` (hasMany)
- `NetworkUser` utiliza guard `network` separado (não é usuário da Platform nem do Tenant)

### **Models dos Tenants** (`app/Models/Tenant/`)

Armazenados no **banco do tenant** (conexão `tenant`):

| Model | Tabela | Descrição |
|-------|--------|-----------|
| `User` | `users` | Usuários do tenant (com `tenant_id` FK) |
| `Doctor` | `doctors` | Médicos cadastrados |
| `MedicalSpecialty` | `medical_specialties` | Especialidades do tenant |
| `Patient` | `patients` | Pacientes |
| `Calendar` | `calendars` | Calendários de agendamento |
| `BusinessHour` | `business_hours` | Horários comerciais |
| `AppointmentType` | `appointment_types` | Tipos de consulta |
| `Appointment` | `appointments` | Agendamentos |
| `Form` | `forms` | Formulários |
| `FormSection` | `form_sections` | Seções de formulários |
| `FormQuestion` | `form_questions` | Perguntas dos formulários |
| `QuestionOption` | `question_options` | Opções de perguntas |
| `FormResponse` | `form_responses` | Respostas de formulários |
| `ResponseAnswer` | `response_answers` | Respostas individuais |
| `Integrations` | `integrations` | Integrações configuradas |
| `OauthAccount` | `oauth_accounts` | Contas OAuth |
| `CalendarSyncState` | `calendar_sync_states` | Estado de sincronização |
| `GoogleCalendarToken` | `google_calendar_tokens` | Tokens do Google Calendar |
| `AppleCalendarToken` | `apple_calendar_tokens` | Tokens do Apple Calendar |
| `Notification` | `notifications` | Notificações do tenant |
| `TenantSetting` | `tenant_settings` | Configurações específicas do tenant |
| `RecurringAppointment` | `recurring_appointments` | Agendamentos recorrentes |
| `RecurringAppointmentRule` | `recurring_appointment_rules` | Regras de recorrência |
| `OnlineAppointmentInstruction` | `online_appointment_instructions` | Instruções de agendamento online |
| `PatientLogin` | `patient_logins` | Credenciais de login dos pacientes |
| `PatientAddress` | `patient_addresses` | Endereços dos pacientes |
| `Gender` | `genders` | Gêneros (helper) |
| `UserDoctorPermission` | `user_doctor_permissions` | Permissões de médicos para usuários |
| `DoctorBillingPrice` | `doctor_billing_prices` | Preços de cobrança por médico |
| `FinancialAccount` | `financial_accounts` | Contas financeiras |
| `FinancialCategory` | `financial_categories` | Categorias financeiras |
| `FinancialTransaction` | `financial_transactions` | Transações financeiras |
| `FinancialCharge` | `financial_charges` | Cobranças |
| `DoctorCommission` | `doctor_commissions` | Comissões de médicos |
| `AsaasWebhookEvent` | `asaas_webhook_events` | Eventos de webhook do Asaas |
| `TenantPlanLimit` | `tenant_plan_limits` | Limites do plano do tenant |
| `TwoFactorCode` | `two_factor_codes` | Códigos de autenticação de dois fatores |
| `Module` | - | Módulos de acesso (helper) |

**Características importantes:**
- Todos os models usam `protected $connection = 'tenant'`
- `User` (Tenant) possui relacionamento `belongsTo` com `Platform\Tenant`
- `User` possui campo `modules` (JSON) para controle de acesso interno

---

## 🔄 Migrações

### **Migrações do Banco Central** (`database/migrations/`)

Tabelas principais:
- `tenants` - Registro de todos os tenants (com `network_id` nullable)
- `clinic_networks` - Redes de clínicas
- `network_users` - Usuários da área administrativa das redes
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
- `plan_change_requests` - Solicitações de mudança de plano

### **Migrações dos Tenants** (`database/migrations/tenant/`)

Executadas automaticamente quando um tenant é criado via `TenantProvisioner`:

1. `create_users_table` - Usuários do tenant
2. `create_doctors_table` - Médicos
3. `create_medical_specialties_table` - Especialidades
4. `create_doctor_specialty_table` - Relação muitos-para-muitos
5. `create_patients_table` - Pacientes
6. `create_calendars_and_business_hours_tables` - Calendários e horários
7. `create_appointment_types_table` - Tipos de consulta
8. `create_appointments_table` - Agendamentos
9. `create_forms_tables` - Formulários, seções, perguntas, opções
10. `create_form_responses_tables` - Respostas de formulários
11. `create_integrations_tables` - Integrações e OAuth

---

## 🛡️ Middlewares

### **Middlewares Customizados** (`app/Http/Middleware/`)

| Middleware | Responsabilidade | Onde é usado |
|------------|------------------|--------------|
| `DetectTenantFromPath` | Detecta tenant pelo path `/customer/{slug}` ou `/workspace/{slug}` e ativa | `tenant-web` group |
| `DetectTenantForPatientPortal` | Detecta tenant para portal do paciente | Portal do paciente |
| `PersistTenantInSession` | Persiste tenant na sessão entre requests | `tenant-web` group, `persist.tenant` alias |
| `EnsureTenantFromGuard` | Ativa tenant a partir do usuário autenticado | `tenant.from.guard` alias |
| `EnsureTenantFromPatientGuard` | Ativa tenant a partir do paciente autenticado | Portal do paciente |
| `EnsureCorrectGuard` | Garante uso do guard correto (`web` ou `tenant`) | `ensure.guard` alias |
| `RedirectIfTenantUnauthenticated` | Redireciona para login se não autenticado | `tenant.auth` alias |
| `RedirectIfPatientUnauthenticated` | Redireciona paciente não autenticado para login | `patient.auth` alias |
| `CheckModuleAccess` | Verifica acesso a módulos específicos | `module.access` alias |
| `TenantModulePermissions` | Verifica permissões de módulos do tenant | Tenant autenticado |
| `EnsureFeatureAccess` | Garante acesso a funcionalidades específicas | `ensure.feature` alias |
| `EnsureAnyFeatureAccess` | Garante acesso a pelo menos uma funcionalidade | `ensure.any.feature` alias |
| `VerifyAsaasToken` | Valida token do webhook do Asaas (platform) | `verify.asaas.token` alias |
| `VerifyAsaasWebhookSecret` | Valida secret do webhook do Asaas (tenant) | `verify.asaas.webhook.secret` alias |
| `VerifyAsaasWebhookIpWhitelist` | Valida IP do webhook do Asaas | `verify.asaas.webhook.ip` alias |
| `ThrottleAsaasWebhook` | Rate limiting para webhooks do Asaas | `throttle.asaas.webhook` alias |
| `Platform\BotApiTokenMiddleware` | Valida token de API para bots | Rotas de API de bots |
| `DetectClinicNetworkFromSubdomain` | Detecta rede de clínicas pelo subdomínio | `web` group (antes de tenant) |
| `RequireNetworkContext` | Garante que rede foi detectada | `require.network` alias |
| `EnsureNetworkContext` | Garante contexto de rede (alias) | `ensure.network.context` alias |
| `EnsureNetworkUser` | Verifica autenticação do usuário da rede | `network.auth` alias |

### **Fluxo de Middlewares**

#### **Para rotas da Platform:**
```
web middleware group
  → auth
  → module.access:{modulo}
```

#### **Para login do Tenant (`/customer/{slug}/login`):**
```
tenant-web middleware group
  → DetectTenantFromPath (detecta e ativa tenant)
  → PersistTenantInSession (salva na sessão)
  → EnsureCorrectGuard (usa guard 'tenant')
  → Session, Cookies, CSRF
```

#### **Para área autenticada do Tenant (`/workspace/{slug}/*`):**
```
web middleware group
  → persist.tenant (reativa tenant da sessão)
  → tenant.from.guard (ativa tenant do usuário logado)
  → ensure.guard (garante guard 'tenant')
  → tenant.auth (verifica autenticação)
  → module.access:{modulo} (verifica acesso ao módulo, quando aplicável)
```

#### **Para portal do paciente (`/workspace/{slug}/paciente/*`):**
```
web middleware group
  → persist.tenant (reativa tenant da sessão)
  → tenant.from.guard (ativa tenant do paciente logado)
  → ensure.guard (garante guard 'tenant')
  → patient.auth (verifica autenticação do paciente)
```

#### **Para webhooks do Asaas (`/t/{slug}/webhooks/asaas`):**
```
tenant-web middleware group
  → DetectTenantFromPath (detecta e ativa tenant)
  → throttle.asaas.webhook (rate limiting)
  → verify.asaas.webhook.secret (valida secret)
  → verify.asaas.webhook.ip (valida IP whitelist)
```

#### **Para rede de clínicas (pública - `routes/network.php`):**
```
web middleware group
  → DetectClinicNetworkFromSubdomain (detecta rede, NUNCA ativa tenant)
  → require.network (garante que rede foi detectada)
```

#### **Para área administrativa da rede (`routes/network_admin.php`):**
```
web middleware group
  → DetectClinicNetworkFromSubdomain (detecta rede)
  → ensure.network.context (garante contexto)
  → network.auth (verifica autenticação com guard 'network')
```

---

## 🏢 Lógica Multitenant

### **Arquitetura Multitenant**

O sistema utiliza **multitenancy com banco de dados separado** (database-per-tenant):

- **Banco Central (Landlord)**: PostgreSQL com dados da plataforma
- **Bancos dos Tenants**: Cada tenant possui seu próprio banco PostgreSQL isolado

### **Rede de Clínicas e Acesso Contratual**

Tenants vinculados a uma **Rede de Clínicas** possuem um comportamento diferenciado:

1.  **Planos Contratuais**: Utilizam obrigatoriamente planos da categoria `contractual`.
2.  **Acesso Direto**: O acesso é liberado diretamente através do campo `plan_id` no model `Tenant`, sem a necessidade de um registro na tabela `subscriptions` (evitando cobranças recorrentes automáticas pelo sistema).
3.  **Inativação de Rede**: Se uma rede de clínicas for marcada como **inativa**, todos os tenants vinculados a ela perdem o acesso ao sistema imediatamente, independentemente do plano configurado.

### **Componentes Principais**

#### 1. **PathTenantFinder** (`app/TenantFinder/PathTenantFinder.php`)

Identifica o tenant pelo segundo segmento da URL:
- URL: `/t/{tenant}/login`
- Busca: `Tenant::where('subdomain', $subdomain)->first()`

#### 2. **SwitchTenantTask** (`app/Multitenancy/Tasks/SwitchTenantTask.php`)

Executado quando um tenant é ativado (`makeCurrent()`):

```php
1. Valida UUID do tenant
2. Busca tenant no banco central
3. Configura conexão dinâmica:
   - Host/Port: Fixos (do .env)
   - Database: Dinâmico (do tenant)
   - Username/Password: Dinâmicos (do tenant)
4. Purga e reconecta conexão 'tenant'
```

#### 3. **TenantProvisioner** (`app/Services/TenantProvisioner.php`)

Serviço responsável por criar/remover tenants:

**Criação (`createDatabase`):**
1. Cria banco de dados PostgreSQL
2. Cria usuário do banco
3. Concede permissões
4. Configura conexão dinâmica
5. Executa migrations do tenant
6. Cria usuário admin padrão via seeder

**Remoção (`destroyTenant`):**
1. Encerra conexões ativas
2. Remove banco de dados
3. Remove usuário do banco
4. Remove registro do tenant

#### 4. **Configuração** (`config/multitenancy.php`)

```php
'tenant_finder' => PathTenantFinder::class,
'switch_tenant_tasks' => [SwitchTenantTask::class],
'tenant_model' => App\Models\Platform\Tenant::class,
'tenant_database_connection_name' => 'tenant',
'landlord_database_connection_name' => env('DB_CONNECTION', 'pgsql'),
```

#### 5. **Conexões de Banco** (`config/database.php`)

```php
// Banco central (landlord)
'pgsql' => [
    'host' => env('DB_HOST'),
    'database' => env('DB_DATABASE'),
    // ...
]

// Banco do tenant (dinâmico)
'tenant' => [
    'driver' => 'pgsql',
    'host' => env('DB_TENANT_HOST'),      // Fixo
    'port' => env('DB_TENANT_PORT'),      // Fixo
    'database' => null,                    // Preenchido dinamicamente
    'username' => null,                    // Preenchido dinamicamente
    'password' => null,                    // Preenchido dinamicamente
]
```

### **Fluxo de Detecção e Ativação do Tenant**

```
1. Request chega em /customer/{slug}/login ou /workspace/{slug}/*
   ↓
2. DetectTenantFromPath detecta segment(2) = {slug}
   ↓
3. Busca Tenant::where('subdomain', $slug)->first()
   ↓
4. Chama $tenant->makeCurrent()
   ↓
5. SwitchTenantTask::makeCurrent() é executado
   ↓
6. Configura conexão 'tenant' com credenciais do tenant
   ↓
7. PersistTenantInSession salva 'tenant_slug' na sessão (se aplicável)
   ↓
8. EnsureCorrectGuard define Auth::shouldUse('tenant')
   ↓
9. Request continua com tenant ativo
```

### **Autenticação Tripla**

O sistema possui **três guards de autenticação**:

1. **Guard `web`**: Usuários da platform (`App\Models\Platform\User`)
2. **Guard `tenant`**: Usuários dos tenants (`App\Models\Tenant\User`)
3. **Guard `network`**: Usuários das redes de clínicas (`App\Models\Platform\NetworkUser`)

Configuração em `config/auth.php`:
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',  // Platform\User
    ],
    'tenant' => [
        'driver' => 'session',
        'provider' => 'tenant_users',  // Tenant\User
    ],
    'network' => [
        'driver' => 'session',
        'provider' => 'network_users',  // Platform\NetworkUser
    ],
],
```

### **Controle de Acesso por Módulos**

Tanto usuários da platform quanto dos tenants possuem campo `modules` (JSON) que define quais módulos podem acessar:

- **Platform**: `tenants`, `plans`, `subscriptions`, `invoices`, `users`, `settings`, etc.
- **Tenant**: `users`, `doctors`, `patients`, `appointments`, `forms`, etc.

O middleware `CheckModuleAccess` verifica se o usuário possui acesso ao módulo antes de permitir a rota.

---

## 🔐 Segurança

1. **Isolamento de Dados**: Cada tenant possui banco de dados isolado
2. **Autenticação Separada**: Guards diferentes para platform e tenant
3. **Validação de Tenant**: Middlewares garantem que tenant correto está ativo
4. **Controle de Acesso**: Sistema de módulos para restringir funcionalidades
5. **Webhook Seguro**: Validação de token para webhooks do Asaas

---

## 📦 Dependências Principais

- **Laravel 10**: Framework PHP
- **Spatie Laravel Multitenancy 3.2**: Gerenciamento de multitenancy
- **Laravel Sanctum**: Autenticação API
- **Laravel Breeze**: Autenticação web
- **PostgreSQL**: Banco de dados (tanto landlord quanto tenants)

---

## 🚀 Fluxo de Criação de Tenant

O fluxo de criação foi centralizado no `TenantCreatorService` para garantir consistência entre o cadastro manual e a importação em lote:

```
1. Solicitação de criação (Controller Manual ou Importação)
   ↓
2. TenantCreatorService::create()
   ↓
3. Validação de regras de negócio (Plano vs Rede, Documento Único)
   ↓
4. TenantProvisioner::prepareDatabaseConfig() gera credenciais
   ↓
5. Tenant é salvo no banco central
   ↓
6. TenantProvisioner::createDatabase() cria o banco e executa migrations
   ↓
7. Usuário admin padrão é criado no banco do tenant
   ↓
8. Se não for rede: Cria assinatura (Subscription) e sincroniza Asaas
   ↓
9. Se for rede: Vincula plano diretamente ao tenant (Acesso Contratual)
   ↓
10. TenantPlanService::applyPlanRules() configura limites no banco do tenant
   ↓
11. Notificação: Envia credenciais por e-mail para o administrador
```

---

## 📝 Observações Importantes

1. **UUID como Chave Primária**: O modelo `Tenant` usa UUID (string) como chave primária, não auto-incremento
2. **Conexão Dinâmica**: A conexão `tenant` é configurada dinamicamente a cada request
3. **Persistência na Sessão**: O tenant é persistido na sessão para evitar re-detecção a cada request
4. **Logs Extensivos**: O sistema possui logs detalhados para debug do fluxo multitenant
5. **Integração Asaas**: Sistema de pagamento integrado com sincronização de clientes e faturas (tanto na platform quanto no módulo financeiro dos tenants)
6. **Formulários Públicos**: Sistema de envio automático de links de formulários aos pacientes quando agendamentos são criados
7. **Notificações Flexíveis**: Sistema de notificações com suporte a provedores globais ou específicos por tenant (email e WhatsApp)
8. **Envio Automático**: O `AppointmentObserver` envia automaticamente links de formulários quando um agendamento é criado e existe formulário ativo correspondente
9. **Estrutura de URLs**: O sistema utiliza diferentes prefixes baseados no contexto:
   - `/customer/{slug}` - Área pública e login do tenant
   - `/workspace/{slug}` - Área autenticada do tenant e portal do paciente
   - `/t/{slug}` - Webhooks e páginas públicas de pagamento do financeiro
10. **Autenticação de Dois Fatores (2FA)**: Implementada tanto na platform quanto nos tenants, com suporte a TOTP e SMS
11. **Módulo Financeiro**: Sistema completo de gestão financeira com contas, categorias, transações, cobranças, comissões e relatórios
12. **Integrações de Calendário**: Suporte a Google Calendar e Apple Calendar (iCloud) com sincronização bidirecional
13. **Agendamentos Online**: Sistema de agendamentos online com instruções personalizáveis
14. **Portal do Paciente**: Área autenticada para pacientes gerenciarem seus agendamentos
15. **API para Bots**: Sistema de tokens de API para integração com bots externos
16. **Relatórios**: Sistema extensivo de relatórios para agendamentos, financeiro, pacientes, médicos, etc.

---

**Documentação gerada em:** 2026-02-17
**Última atualização:** 2026-02-17

**Nota:** Esta documentação foi revisada e atualizada para refletir todas as rotas e funcionalidades atuais do sistema, incluindo:
- Estrutura correta de URLs (`/customer/{slug}`, `/workspace/{slug}`, `/t/{slug}`)
- Rotas do Portal do Paciente
- Rota global do Google Calendar callback (`/google/callback`)
- Rotas de agendamentos recorrentes
- Rotas de permissões de médicos para usuários
- Formulários públicos e envio automático de links
- Serviços de notificação (MailTenantService, NotificationService, WhatsappTenantService)
- Integração com Apple Calendar (iCloud)
- Sistema completo de relatórios
- Módulo de atendimento médico
- Agendamentos online com instruções
- Módulo financeiro completo (contas, categorias, transações, cobranças, comissões, relatórios)
- Autenticação de dois fatores (2FA)
- API para bots com tokens
- Layouts de email personalizáveis
