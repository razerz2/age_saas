# 🏥 Documentação - Área Tenant (Clínicas)

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Acesso e Autenticação](#acesso-e-autenticação)
3. [Estrutura de Rotas](#estrutura-de-rotas)
4. [Controllers](#controllers)
5. [Models](#models)
6. [Funcionalidades Principais](#funcionalidades-principais)
7. [Área Pública de Agendamento](#área-pública-de-agendamento)
8. [Portal do Paciente](#portal-do-paciente)
9. [Guia de Uso](#guia-de-uso)

---

## 🎯 Visão Geral

A **Tenant** é a área específica de cada cliente (clínica) do sistema SaaS de agendamento médico. Cada tenant possui seu próprio banco de dados PostgreSQL isolado, garantindo total separação de dados.

### Funcionalidades Principais

- ✅ Dashboard com estatísticas
- ✅ Gerenciamento de usuários
- ✅ Cadastro de médicos e especialidades
- ✅ Cadastro de pacientes
- ✅ Calendários de agendamento
- ✅ Horários comerciais
- ✅ Tipos de consulta
- ✅ Agendamentos
- ✅ Formulários personalizados
- ✅ Respostas de formulários
- ✅ Integrações (Google Calendar, etc.)
- ✅ Sincronização de calendário
- ✅ Área pública de agendamento
- ✅ Portal do paciente

### Banco de Dados

Cada tenant possui seu **próprio banco de dados PostgreSQL**, que armazena:
- Usuários do tenant
- Médicos e especialidades
- Pacientes
- Calendários e horários comerciais
- Tipos de consulta
- Agendamentos
- Formulários e respostas
- Integrações e OAuth

---

## 🔐 Acesso e Autenticação

### URL de Acesso

**Login:**
```
http://localhost/t/{subdomain}/login
```

**Área Autenticada:**
```
http://localhost/t/{subdomain}/tenant/dashboard
```

Onde `{subdomain}` é o subdomain único do tenant (ex: `odontovida`, `clinica-teste`).

### Autenticação

- **Guard**: `tenant`
- **Model**: `App\Models\Tenant\User`
- **Middleware**: `tenant.auth` (obrigatório para área autenticada)

### Controle de Acesso

Os usuários do tenant possuem um campo `modules` (JSON) que define quais módulos podem acessar:

- `users` - Gerenciamento de usuários
- `doctors` - Gerenciamento de médicos
- `specialties` - Gerenciamento de especialidades
- `patients` - Gerenciamento de pacientes
- `calendars` - Gerenciamento de calendários
- `business-hours` - Horários comerciais
- `appointment-types` - Tipos de consulta
- `appointments` - Gerenciamento de agendamentos
- `forms` - Gerenciamento de formulários
- `responses` - Respostas de formulários
- `integrations` - Integrações
- `settings` - Configurações

O middleware `module.access:{modulo}` verifica o acesso antes de permitir a rota.

---

## 🛣️ Estrutura de Rotas

### Rotas Públicas (sem autenticação)

```php
GET  /t/{tenant}/login              # Formulário de login
POST /t/{tenant}/login              # Processar login
POST /t/{tenant}/logout             # Logout

# Área pública de agendamento
GET  /t/{tenant}/agendamento/identificar    # Identificar paciente
POST /t/{tenant}/agendamento/identificar    # Processar identificação
GET  /t/{tenant}/agendamento/cadastro      # Cadastro de paciente
POST /t/{tenant}/agendamento/cadastro      # Processar cadastro
GET  /t/{tenant}/agendamento/criar         # Criar agendamento
POST /t/{tenant}/agendamento/criar         # Processar agendamento
GET  /t/{tenant}/agendamento/sucesso       # Página de sucesso
```

### Rotas Autenticadas (área administrativa do tenant)

```php
/tenant/dashboard                   # Dashboard do tenant
/tenant/users                       # CRUD de usuários do tenant
/tenant/doctors                     # CRUD de médicos
/tenant/specialties                  # CRUD de especialidades médicas
/tenant/patients                     # CRUD de pacientes
/tenant/calendars                    # CRUD de calendários
/tenant/business-hours               # CRUD de horários comerciais
/tenant/appointment-types            # CRUD de tipos de consulta
/tenant/appointments                 # CRUD de agendamentos
/tenant/forms                        # CRUD de formulários
/tenant/responses                    # CRUD de respostas de formulários
/tenant/integrations                 # CRUD de integrações
/tenant/oauth-accounts               # CRUD de contas OAuth
/tenant/calendar-sync                # Sincronização de calendário
/tenant/settings                     # Configurações do tenant
```

### Portal do Paciente

```php
GET  /t/{tenant}/portal/login        # Login do portal
POST /t/{tenant}/portal/login        # Processar login
GET  /t/{tenant}/portal/dashboard    # Dashboard do paciente
GET  /t/{tenant}/portal/appointments # Agendamentos do paciente
GET  /t/{tenant}/portal/profile     # Perfil do paciente
```

---

## 🎮 Controllers

### Controllers dos Tenants (`app/Http/Controllers/Tenant/`)

| Controller | Responsabilidade | Rotas Principais |
|------------|------------------|------------------|
| `Auth/LoginController` | Autenticação específica do tenant | `/t/{tenant}/login` |
| `DashboardController` | Dashboard do tenant | `/tenant/dashboard` |
| `UserController` | CRUD de usuários do tenant | `/tenant/users` |
| `DoctorController` | CRUD de médicos | `/tenant/doctors` |
| `MedicalSpecialtyController` | Especialidades médicas do tenant | `/tenant/specialties` |
| `PatientController` | CRUD de pacientes | `/tenant/patients` |
| `CalendarController` | CRUD de calendários | `/tenant/calendars` |
| `BusinessHourController` | Horários comerciais | `/tenant/business-hours` |
| `AppointmentTypeController` | Tipos de consulta | `/tenant/appointment-types` |
| `AppointmentController` | CRUD de agendamentos + eventos do calendário | `/tenant/appointments` |
| `FormController` | CRUD de formulários + seções/perguntas/opções | `/tenant/forms` |
| `FormResponseController` | Respostas de formulários + respostas individuais | `/tenant/responses` |
| `IntegrationController` | Integrações (Google Calendar, etc.) | `/tenant/integrations` |
| `OAuthAccountController` | Contas OAuth conectadas | `/tenant/oauth-accounts` |
| `CalendarSyncStateController` | Estado de sincronização de calendário | `/tenant/calendar-sync` |
| `SettingsController` | Configurações do tenant | `/tenant/settings` |
| `PublicPatientController` | Identificação de paciente (área pública) | `/t/{tenant}/agendamento/identificar` |
| `PublicPatientRegisterController` | Cadastro de paciente (área pública) | `/t/{tenant}/agendamento/cadastro` |
| `PublicAppointmentController` | Criação de agendamento (área pública) | `/t/{tenant}/agendamento/criar` |
| `PatientPortal/*` | Portal do paciente | `/t/{tenant}/portal/*` |

---

## 🗄️ Models

### Models dos Tenants (`app/Models/Tenant/`)

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

### Características Importantes

- Todos os models usam `protected $connection = 'tenant'`
- `User` (Tenant) possui relacionamento `belongsTo` com `Platform\Tenant`
- `User` possui campo `modules` (JSON) para controle de acesso interno
- `Patient` possui campo `login_enabled` para habilitar acesso ao portal

---

## ⚙️ Funcionalidades Principais

### 1. Gerenciamento de Médicos

**Criar Médico:**
1. Acesse `/tenant/doctors`
2. Clique em "Criar Médico"
3. Preencha:
   - Nome completo
   - CRM (Conselho Regional de Medicina)
   - Especialidades (múltiplas)
   - Status (ativo/inativo)

### 2. Gerenciamento de Pacientes

**Criar Paciente:**
1. Acesse `/tenant/patients`
2. Clique em "Criar Paciente"
3. Preencha:
   - Nome completo
   - CPF
   - Data de nascimento
   - Email
   - Telefone
   - Endereço (opcional)
   - Habilitar login no portal (opcional)

**Login do Paciente:**
- Se `login_enabled = true`, o paciente pode acessar o portal
- Credenciais são enviadas por email automaticamente

### 3. Calendários e Horários

**Criar Calendário:**
1. Acesse `/tenant/calendars`
2. Clique em "Criar Calendário"
3. Associe a um médico
4. Configure horários comerciais

**Horários Comerciais:**
1. Acesse `/tenant/business-hours`
2. Configure horários por dia da semana
3. Defina intervalos de tempo disponíveis

### 4. Tipos de Consulta

**Criar Tipo de Consulta:**
1. Acesse `/tenant/appointment-types`
2. Clique em "Criar Tipo"
3. Defina:
   - Nome
   - Duração (em minutos)
   - Médico associado
   - Descrição (opcional)

### 5. Agendamentos

**Criar Agendamento:**
1. Acesse `/tenant/appointments`
2. Clique em "Criar Agendamento"
3. Selecione:
   - Paciente
   - Médico
   - Calendário
   - Tipo de consulta
   - Data e horário
   - Observações (opcional)

**Visualizar Calendário:**
- Acesse `/tenant/appointments`
- Visualize agendamentos em formato de calendário
- Filtre por médico, data, etc.

### 6. Formulários Personalizados

**Criar Formulário:**
1. Acesse `/tenant/forms`
2. Clique em "Criar Formulário"
3. Preencha:
   - Nome
   - Descrição
   - Médico associado
   - Especialidade (opcional)
   - Status (ativo/inativo)
4. Clique em "Construir Formulário" para adicionar:
   - Seções
   - Perguntas
   - Opções de resposta

**Ver Guia Completo:** [GUIA_CRIAR_FORMULARIO.md](GUIA_CRIAR_FORMULARIO.md)

### 7. Respostas de Formulários

**Visualizar Respostas:**
1. Acesse `/tenant/responses`
2. Visualize todas as respostas coletadas
3. Filtre por formulário, paciente, data, etc.
4. Clique em "Ver" para visualizar resposta completa

### 8. Integrações

**Google Calendar:**
1. Acesse `/tenant/integrations`
2. Configure integração com Google Calendar
3. Conecte conta OAuth
4. Sincronize agendamentos automaticamente

---

## 🌐 Área Pública de Agendamento

A área pública permite que pacientes façam agendamentos sem precisar estar logados no sistema administrativo.

### Fluxo de Agendamento Público

1. **Identificação do Paciente**
   - URL: `/t/{tenant}/agendamento/identificar`
   - Paciente informa CPF ou Email
   - Sistema verifica se já está cadastrado

2. **Cadastro (se necessário)**
   - URL: `/t/{tenant}/agendamento/cadastro`
   - Se paciente não encontrado, pode criar cadastro
   - Campos: Nome, CPF, Data de nascimento, Email, Telefone

3. **Criar Agendamento**
   - URL: `/t/{tenant}/agendamento/criar`
   - Seleciona médico, calendário, tipo de consulta
   - Escolhe data e horário disponível
   - Adiciona observações (opcional)

4. **Confirmação**
   - URL: `/t/{tenant}/agendamento/sucesso`
   - Exibe mensagem de confirmação
   - Mostra detalhes do agendamento

### Guia de Teste

Para testar a área pública, consulte: [GUIA_TESTE_PUBLICO.md](GUIA_TESTE_PUBLICO.md)

---

## 👤 Portal do Paciente

O portal permite que pacientes acessem suas informações e agendamentos.

### Funcionalidades do Portal

- **Dashboard**: Visão geral de agendamentos
- **Agendamentos**: Lista de agendamentos do paciente
- **Perfil**: Dados pessoais do paciente
- **Notificações**: Notificações recebidas

### Acesso ao Portal

1. O paciente deve ter `login_enabled = true`
2. Credenciais são enviadas por email automaticamente
3. Acesse: `/t/{tenant}/portal/login`
4. Após login, redireciona para `/t/{tenant}/portal/dashboard`

### Login do Paciente

O sistema possui uma tabela `patient_logins` que armazena:
- `patient_id` - ID do paciente
- `email` - Email de login
- `password` - Senha criptografada
- `remember_token` - Token de "lembrar-me"

---

## 📚 Guia de Uso

### Criar um Formulário Completo

1. **Criar Formulário Básico**
   - Acesse `/tenant/forms`
   - Clique em "Criar Formulário"
   - Preencha nome, descrição, médico, status
   - Salve

2. **Construir Formulário**
   - Clique em "Construir Formulário"
   - Adicione seções (opcional)
   - Adicione perguntas
   - Configure opções de resposta (se necessário)

3. **Testar Formulário**
   - Clique em "Preencher Formulário"
   - Teste o preenchimento
   - Verifique validações

**Ver Guia Completo:** [GUIA_CRIAR_FORMULARIO.md](GUIA_CRIAR_FORMULARIO.md)

### Configurar Horários Comerciais

1. Acesse `/tenant/business-hours`
2. Para cada dia da semana:
   - Defina se está aberto
   - Configure horário de abertura
   - Configure horário de fechamento
   - Defina intervalo entre consultas (opcional)
3. Salve

### Criar Agendamento via Área Pública

1. Acesse `/t/{tenant}/agendamento/identificar`
2. Informe CPF ou Email
3. Se não cadastrado, crie cadastro
4. Selecione médico, calendário, tipo, data e horário
5. Confirme agendamento

**Ver Guia de Teste:** [GUIA_TESTE_PUBLICO.md](GUIA_TESTE_PUBLICO.md)

### Habilitar Login do Paciente

1. Acesse `/tenant/patients`
2. Edite o paciente
3. Marque "Habilitar Login no Portal"
4. Salve
5. Credenciais serão enviadas por email automaticamente

---

## 🔄 Migrações

### Migrações dos Tenants (`database/migrations/tenant/`)

Executadas automaticamente quando um tenant é criado via `TenantProvisioner`:

1. `create_users_table` - Usuários do tenant
2. `create_doctors_table` - Médicos
3. `create_medical_specialties_table` - Especialidades
4. `create_doctor_specialty_table` - Relação muitos-para-muitos
5. `create_patients_table` - Pacientes
6. `create_patient_logins_table` - Login de pacientes
7. `create_calendars_and_business_hours_tables` - Calendários e horários
8. `create_appointment_types_table` - Tipos de consulta
9. `create_appointments_table` - Agendamentos
10. `create_forms_tables` - Formulários, seções, perguntas, opções
11. `create_form_responses_tables` - Respostas de formulários
12. `create_integrations_tables` - Integrações e OAuth

**Nota:** As migrações são executadas automaticamente ao criar um tenant. Para executar manualmente em um tenant existente, use:

```bash
php artisan tenants:migrate
```

---

## 🛡️ Segurança

1. **Isolamento de Dados**: Cada tenant possui banco de dados isolado
2. **Autenticação Separada**: Guard `tenant` específico para usuários do tenant
3. **Validação de Tenant**: Middlewares garantem que tenant correto está ativo
4. **Controle de Acesso**: Sistema de módulos para restringir funcionalidades
5. **Validação de Dados**: Form Requests validam todos os dados de entrada

---

## 🔄 Fluxo de Detecção do Tenant

1. Request chega em `/t/{tenant}/login`
2. `PathTenantFinder` detecta o tenant pelo path
3. `SwitchTenantTask` configura a conexão dinâmica
4. Middleware persiste o tenant na sessão
5. Request continua com tenant ativo

### Middlewares Aplicados

**Para login do Tenant (`/t/{tenant}/login`):**
```
tenant-web middleware group
  → DetectTenantFromPath (detecta e ativa tenant)
  → PersistTenantInSession (salva na sessão)
  → EnsureCorrectGuard (usa guard 'tenant')
  → Session, Cookies, CSRF
```

**Para área autenticada do Tenant (`/tenant/*`):**
```
web middleware group
  → persist.tenant (reativa tenant da sessão)
  → tenant.from.guard (ativa tenant do usuário logado)
  → ensure.guard (garante guard 'tenant')
  → tenant.auth (verifica autenticação)
```

---

## 📝 Observações Importantes

1. **Conexão Dinâmica**: A conexão `tenant` é configurada dinamicamente a cada request
2. **Persistência na Sessão**: O tenant é persistido na sessão para evitar re-detecção
3. **Relacionamento com Platform**: `User` (Tenant) possui `tenant_id` que referencia `Platform\Tenant`
4. **Login de Pacientes**: Sistema possui tabela separada `patient_logins` para autenticação de pacientes
5. **Formulários por Médico**: Formulários são vinculados a médicos, não a especialidades

---

## 🔗 Links Relacionados

- [README.md](README.md) - Documentação geral do projeto
- [PLATFORM.md](PLATFORM.md) - Documentação da área Platform
- [ARQUITETURA.md](ARQUITETURA.md) - Documentação técnica da arquitetura
- [GUIA_CRIAR_FORMULARIO.md](GUIA_CRIAR_FORMULARIO.md) - Guia completo de criação de formulários
- [GUIA_TESTE_PUBLICO.md](GUIA_TESTE_PUBLICO.md) - Guia de teste da área pública
- [INSTRUCOES_MIGRATION.md](INSTRUCOES_MIGRATION.md) - Instruções para migrações manuais

---

**Última atualização:** 2025-01-27

