# 📐 Arquitetura do Sistema - Agendamento SaaS

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Estrutura de Pastas](#estrutura-de-pastas)
3. [Rotas (Platform e Tenant)](#rotas-platform-e-tenant)
4. [Controllers](#controllers)
5. [Models](#models)
6. [Migrações](#migrações)
7. [Middlewares](#middlewares)
8. [Lógica Multitenant](#lógica-multitenant)

---

## 🎯 Visão Geral

Este é um sistema **SaaS (Software as a Service)** de agendamento médico construído com **Laravel 10** e utilizando o pacote **Spatie Laravel Multitenancy**. O sistema possui duas áreas principais:

- **Platform**: Área administrativa central para gerenciar tenants, planos, assinaturas, faturas, etc.
- **Tenant**: Área específica de cada cliente (clínica) com funcionalidades de agendamento, pacientes, médicos, etc.

O sistema utiliza **multitenancy com banco de dados separado por tenant**, onde cada cliente possui seu próprio banco de dados PostgreSQL isolado.

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
│   │   ├── TenantProvisioner.php  # Criação/remoção de tenants
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
│   ├── api.php                    # Rotas da API (Sanctum)
│   └── auth.php                   # Rotas de autenticação (Breeze)
└── resources/views/                # Views Blade
```

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
POST /Platform/subscriptions/{id}/renew        # Renovar assinatura
POST /Platform/invoices/{invoice}/sync         # Sincronizar fatura manualmente
POST /Platform/whatsapp/send                   # Enviar mensagem WhatsApp
POST /Platform/whatsapp/invoice/{invoice}      # Enviar notificação de fatura
GET  /Platform/api/estados/{pais}              # API: Estados por país
GET  /Platform/api/cidades/{estado}            # API: Cidades por estado
GET  /kiosk/monitor                             # Monitor de kiosk
GET  /kiosk/monitor/data                        # Dados do monitor (API)
POST /webhook/asaas                            # Webhook do Asaas
GET  /google/callback                           # Callback do Google Calendar OAuth (rota global)
```

**Middleware aplicado:**
- `auth` - Autenticação obrigatória
- `module.access:{modulo}` - Controle de acesso por módulo (ex: `tenants`, `plans`, `invoices`)

### **Rotas dos Tenants** (`routes/tenant.php`)

As rotas dos tenants são divididas em duas seções:

#### 1. **Login do Tenant** (`/t/{tenant}/login`)
```php
GET  /t/{tenant}/login              # Formulário de login
POST /t/{tenant}/login              # Processar login
POST /t/{tenant}/logout             # Logout
```

**Middleware:** `tenant-web` (detecta tenant pelo path)

#### 2. **Área Autenticada do Tenant** (`/tenant/*`)
```php
/tenant/dashboard                   # Dashboard do tenant
/tenant/users                       # CRUD de usuários do tenant
/tenant/doctors                     # CRUD de médicos
/tenant/specialties                 # CRUD de especialidades médicas
/tenant/patients                    # CRUD de pacientes
/tenant/calendars                   # CRUD de calendários
/tenant/business-hours              # CRUD de horários comerciais
/tenant/appointment-types           # CRUD de tipos de consulta
/tenant/appointments                # CRUD de agendamentos
/tenant/forms                       # CRUD de formulários
/tenant/responses                   # CRUD de respostas de formulários
/tenant/integrations                # CRUD de integrações
/tenant/integrations/google         # Integração Google Calendar
/tenant/oauth-accounts              # CRUD de contas OAuth
/tenant/calendar-sync               # Sincronização de calendário
/tenant/notifications               # Notificações do tenant
/tenant/settings                    # Configurações do tenant
/tenant/agendamentos/recorrentes    # Agendamentos recorrentes
```

#### 3. **Área Pública de Agendamento** (`/t/{tenant}/agendamento/*`)

**Rotas Públicas (sem autenticação):**
```php
GET  /t/{tenant}/agendamento/identificar    # Identificar paciente
POST /t/{tenant}/agendamento/identificar    # Processar identificação
GET  /t/{tenant}/agendamento/cadastro      # Cadastro de paciente
POST /t/{tenant}/agendamento/cadastro      # Processar cadastro
GET  /t/{tenant}/agendamento/criar         # Criar agendamento
POST /t/{tenant}/agendamento/criar         # Processar agendamento
GET  /t/{tenant}/agendamento/sucesso/{appointment_id?}  # Página de sucesso
GET  /t/{tenant}/agendamento/{appointment_id} # Visualizar agendamento

# APIs públicas para agendamento
GET  /t/{tenant}/agendamento/api/doctors/{doctorId}/calendars
GET  /t/{tenant}/agendamento/api/doctors/{doctorId}/appointment-types
GET  /t/{tenant}/agendamento/api/doctors/{doctorId}/specialties
GET  /t/{tenant}/agendamento/api/doctors/{doctorId}/available-slots

# Formulários públicos
GET  /t/{tenant}/formulario/{form}/responder                    # Responder formulário
POST /t/{tenant}/formulario/{form}/responder                    # Salvar resposta
GET  /t/{tenant}/formulario/{form}/resposta/{response}/sucesso   # Página de sucesso
```

**Middleware:** `tenant-web` (detecta tenant pelo path)

#### 4. **Portal do Paciente** (`routes/patient_portal.php`)

**Rotas Públicas (com tenant na URL):**
```php
GET  /t/{tenant}/paciente/login              # Formulário de login
POST /t/{tenant}/paciente/login              # Processar login
GET  /t/{tenant}/paciente/esqueci-senha       # Formulário de recuperação de senha
GET  /t/{tenant}/paciente/resetar-senha/{token} # Formulário de resetar senha
```

**Rotas Autenticadas (sem tenant na URL):**
```php
GET  /paciente/dashboard                      # Dashboard do paciente
GET  /paciente/agendamentos                    # Lista de agendamentos
GET  /paciente/agendamentos/criar              # Criar agendamento
POST /paciente/agendamentos                    # Processar criação
GET  /paciente/agendamentos/{id}/editar        # Editar agendamento
PUT  /paciente/agendamentos/{id}               # Atualizar agendamento
POST /paciente/agendamentos/{id}/cancelar      # Cancelar agendamento
GET  /paciente/notificacoes                    # Notificações do paciente
GET  /paciente/perfil                          # Perfil do paciente
POST /paciente/perfil                           # Atualizar perfil
POST /paciente/logout                           # Logout
GET  /paciente/logout                           # Logout (GET)
```

**Middleware aplicado (em ordem):**
1. `web` - Sessão e cookies
2. `persist.tenant` - Persiste tenant na sessão
3. `tenant.from.guard` - Ativa tenant a partir do usuário autenticado
4. `ensure.guard` - Garante uso do guard correto (`tenant`)
5. `tenant.auth` - Verifica autenticação do tenant

---

## 🎮 Controllers

### **Controllers da Platform** (`app/Http/Controllers/Platform/`)

| Controller | Responsabilidade |
|------------|------------------|
| `DashboardController` | Dashboard principal com estatísticas |
| `TenantController` | CRUD de tenants + sincronização com Asaas |
| `PlanController` | CRUD de planos de assinatura |
| `SubscriptionController` | CRUD de assinaturas + renovação |
| `InvoiceController` | CRUD de faturas + sincronização manual |
| `UserController` | CRUD de usuários da platform + reset de senha |
| `MedicalSpecialtyCatalogController` | Catálogo de especialidades médicas |
| `NotificationOutboxController` | Histórico de notificações enviadas |
| `SystemNotificationController` | Notificações do sistema |
| `SystemSettingsController` | Configurações gerais e integrações |
| `PaisController`, `EstadoController`, `CidadeController` | CRUD de localização |
| `LocationController` | API de localização (estados/cidades) |
| `WhatsAppController` | Envio de mensagens WhatsApp |

### **Controllers dos Tenants** (`app/Http/Controllers/Tenant/`)

| Controller | Responsabilidade |
|------------|------------------|
| `Auth/LoginController` | Autenticação específica do tenant |
| `DashboardController` | Dashboard do tenant |
| `UserController` | CRUD de usuários do tenant |
| `DoctorController` | CRUD de médicos |
| `MedicalSpecialtyController` | Especialidades médicas do tenant |
| `PatientController` | CRUD de pacientes |
| `CalendarController` | CRUD de calendários |
| `BusinessHourController` | Horários comerciais |
| `AppointmentTypeController` | Tipos de consulta |
| `AppointmentController` | CRUD de agendamentos + eventos do calendário |
| `FormController` | CRUD de formulários + seções/perguntas/opções |
| `FormResponseController` | Respostas de formulários + respostas individuais |
| `PublicFormController` | Formulários públicos para pacientes responderem |
| `IntegrationController` | Integrações (Google Calendar, etc.) |
| `OAuthAccountController` | Contas OAuth conectadas |
| `CalendarSyncStateController` | Estado de sincronização de calendário |

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
| `MedicalSpecialtyCatalog` | `medical_specialties_catalog` | Catálogo global de especialidades |
| `Pais`, `Estado`, `Cidade` | `paises`, `estados`, `cidades` | Dados de localização |
| `TenantLocalizacao` | `tenant_localizacoes` | Localização dos tenants |
| `SystemSetting` | `system_settings` | Configurações do sistema |
| `WebhookLog` | `webhook_logs` | Logs de webhooks recebidos |
| `Module` | - | Módulos de acesso (helper) |

**Características importantes:**
- `Tenant` estende `Spatie\Multitenancy\Models\Tenant`
- `Tenant` possui métodos para configuração de banco: `getDatabaseName()`, `getDatabaseHost()`, etc.
- `User` (Platform) possui campo `modules` (JSON) para controle de acesso

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
| `Module` | - | Módulos de acesso (helper) |

**Características importantes:**
- Todos os models usam `protected $connection = 'tenant'`
- `User` (Tenant) possui relacionamento `belongsTo` com `Platform\Tenant`
- `User` possui campo `modules` (JSON) para controle de acesso interno

---

## 🔄 Migrações

### **Migrações do Banco Central** (`database/migrations/`)

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
| `DetectTenantFromPath` | Detecta tenant pelo path `/t/{tenant}` e ativa | `tenant-web` group |
| `PersistTenantInSession` | Persiste tenant na sessão entre requests | `tenant-web` group, `persist.tenant` alias |
| `EnsureTenantFromGuard` | Ativa tenant a partir do usuário autenticado | `tenant.from.guard` alias |
| `EnsureCorrectGuard` | Garante uso do guard correto (`web` ou `tenant`) | `ensure.guard` alias |
| `RedirectIfTenantUnauthenticated` | Redireciona para login se não autenticado | `tenant.auth` alias |
| `CheckModuleAccess` | Verifica acesso a módulos específicos | `module.access` alias |
| `VerifyAsaasToken` | Valida token do webhook do Asaas | `verify.asaas.token` alias |

### **Fluxo de Middlewares**

#### **Para rotas da Platform:**
```
web middleware group
  → auth
  → module.access:{modulo}
```

#### **Para login do Tenant (`/t/{tenant}/login`):**
```
tenant-web middleware group
  → DetectTenantFromPath (detecta e ativa tenant)
  → PersistTenantInSession (salva na sessão)
  → EnsureCorrectGuard (usa guard 'tenant')
  → Session, Cookies, CSRF
```

#### **Para área autenticada do Tenant (`/tenant/*`):**
```
web middleware group
  → persist.tenant (reativa tenant da sessão)
  → tenant.from.guard (ativa tenant do usuário logado)
  → ensure.guard (garante guard 'tenant')
  → tenant.auth (verifica autenticação)
```

---

## 🏢 Lógica Multitenant

### **Arquitetura Multitenant**

O sistema utiliza **multitenancy com banco de dados separado** (database-per-tenant):

- **Banco Central (Landlord)**: PostgreSQL com dados da plataforma
- **Bancos dos Tenants**: Cada tenant possui seu próprio banco PostgreSQL isolado

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
1. Request chega em /t/{tenant}/login
   ↓
2. DetectTenantFromPath detecta segment(2) = {tenant}
   ↓
3. Busca Tenant::where('subdomain', $tenant)->first()
   ↓
4. Chama $tenant->makeCurrent()
   ↓
5. SwitchTenantTask::makeCurrent() é executado
   ↓
6. Configura conexão 'tenant' com credenciais do tenant
   ↓
7. PersistTenantInSession salva 'tenant_slug' na sessão
   ↓
8. EnsureCorrectGuard define Auth::shouldUse('tenant')
   ↓
9. Request continua com tenant ativo
```

### **Autenticação Dual**

O sistema possui **dois guards de autenticação**:

1. **Guard `web`**: Usuários da platform (`App\Models\Platform\User`)
2. **Guard `tenant`**: Usuários dos tenants (`App\Models\Tenant\User`)

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

```
1. Admin cria tenant via Platform/TenantController
   ↓
2. TenantProvisioner::prepareDatabaseConfig() gera credenciais
   ↓
3. Tenant é salvo no banco central
   ↓
4. TenantProvisioner::createDatabase() é chamado
   ↓
5. Banco PostgreSQL é criado
   ↓
6. Migrations do tenant são executadas
   ↓
7. Usuário admin padrão é criado
   ↓
8. Tenant está pronto para uso
```

---

## 📝 Observações Importantes

1. **UUID como Chave Primária**: O modelo `Tenant` usa UUID (string) como chave primária, não auto-incremento
2. **Conexão Dinâmica**: A conexão `tenant` é configurada dinamicamente a cada request
3. **Persistência na Sessão**: O tenant é persistido na sessão para evitar re-detecção a cada request
4. **Logs Extensivos**: O sistema possui logs detalhados para debug do fluxo multitenant
5. **Integração Asaas**: Sistema de pagamento integrado com sincronização de clientes e faturas
6. **Formulários Públicos**: Sistema de envio automático de links de formulários aos pacientes quando agendamentos são criados
7. **Notificações Flexíveis**: Sistema de notificações com suporte a provedores globais ou específicos por tenant (email e WhatsApp)
8. **Envio Automático**: O `AppointmentObserver` envia automaticamente links de formulários quando um agendamento é criado e existe formulário ativo correspondente

---

**Documentação gerada em:** 2025-01-27
**Última atualização:** 2025-01-27

**Nota:** Esta documentação foi revisada e atualizada para refletir todas as rotas e funcionalidades atuais do sistema, incluindo:
- Rotas do Portal do Paciente
- Rota global do Google Calendar callback (`/google/callback`)
- Rotas de agendamentos recorrentes
- Rotas de permissões de médicos para usuários
- Formulários públicos e envio automático de links
- Serviços de notificação (MailTenantService, NotificationService, WhatsappTenantService)

