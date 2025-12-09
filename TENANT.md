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
- ✅ Agendamentos (presencial e online)
- ✅ Agendamentos online com instruções e links de reunião
- ✅ Agendamentos recorrentes
- ✅ Atendimento Médico (sessão de atendimento do dia)
- ✅ Formulários personalizados
- ✅ Respostas de formulários
- ✅ Integrações (Google Calendar, etc.)
- ✅ Sincronização de calendário
- ✅ Relatórios completos (agendamentos, pacientes, médicos, etc.)
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

O sistema possui dois níveis de controle de acesso:

#### 1. **Sistema de Roles (Papéis)**

Os usuários do tenant possuem um campo `role` que define seu papel no sistema:

- **`admin`**: Administrador com acesso completo a todos os médicos e funcionalidades
  - Vê todos os médicos cadastrados
  - Pode gerenciar todos os dados do sistema
  - Sem restrições de acesso

- **`doctor`**: Médico que só acessa seus próprios dados
  - Vê apenas seu próprio perfil de médico
  - Acessa apenas seus próprios agendamentos, calendários, formulários, etc.
  - Restrito aos seus próprios dados

- **`user`**: Usuário comum com acesso restrito a médicos permitidos
  - Vê apenas médicos que têm permissão explícita (`UserDoctorPermission`)
  - Pode ser vinculado a um ou mais médicos específicos
  - Se não tiver médicos permitidos, não vê nenhum dado

**Filtros Automáticos:**
- O sistema aplica filtros automáticos baseados no role em todas as listagens
- Os filtros são aplicados automaticamente via trait `HasDoctorFilter` nos controllers
- Médicos com role `doctor` só veem seus próprios dados
- Usuários com role `user` só veem dados dos médicos permitidos
- Administradores veem tudo (sem filtro)

#### 2. **Sistema de Módulos**

Os usuários também possuem um campo `modules` (JSON) que define quais módulos podem acessar:

- `appointments` - Atendimentos
- `online_appointments` - Consultas Online
- `medical_appointments` - Atendimento Médico
- `patients` - Pacientes
- `doctors` - Médicos
- `calendar` - Agenda
- `specialties` - Especialidades
- `users` - Usuários
- `business_hours` - Horários Médicos
- `forms` - Formulários
- `reports` - Relatórios
- `integrations` - Integrações
- `settings` - Configurações

O middleware `module.access:{modulo}` verifica o acesso antes de permitir a rota.

**Nota:** O controle por módulos funciona em conjunto com o sistema de roles. Um médico (role `doctor`) pode ter acesso ao módulo `appointments`, mas só verá seus próprios agendamentos devido ao filtro de role.

---

## 🛣️ Estrutura de Rotas

### Rotas Públicas (sem autenticação)

**Login do Tenant:**
```php
GET  /t/{tenant}/login              # Formulário de login
POST /t/{tenant}/login              # Processar login
POST /t/{tenant}/logout             # Logout
```

**Área pública de agendamento:**
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

### Rotas Autenticadas (área administrativa do tenant)

```php
/tenant/dashboard                   # Dashboard do tenant
/tenant/profile                     # Perfil do usuário do tenant
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
/tenant/notifications                # Notificações do tenant
/tenant/settings                     # Configurações do tenant
/tenant/settings/general             # Atualizar configurações gerais
/tenant/settings/appointments        # Atualizar configurações de agendamentos
/tenant/settings/calendar            # Atualizar configurações de calendário
/tenant/settings/notifications       # Atualizar configurações de notificações
/tenant/settings/integrations        # Atualizar configurações de integrações
/tenant/settings/user-defaults       # Atualizar configurações de padrões de usuário
/tenant/settings/professionals       # Atualizar configurações de profissionais
/tenant/agendamentos/recorrentes     # Agendamentos recorrentes
/tenant/appointments/online          # Lista de agendamentos online
/tenant/atendimento                  # Atendimento Médico - escolher dia
/tenant/atendimento/iniciar          # Iniciar sessão de atendimento
/tenant/atendimento/dia/{date}       # Sessão de atendimento do dia
/tenant/atendimento/{appointment}/detalhes # Detalhes do agendamento (AJAX)
/tenant/atendimento/{appointment}/status # Alterar status do atendimento
/tenant/atendimento/{appointment}/concluir # Concluir atendimento
/tenant/atendimento/{appointment}/formulario-resposta # Buscar resposta do formulário
/tenant/reports                       # Página inicial de relatórios
/tenant/reports/appointments          # Relatório de agendamentos
/tenant/reports/appointments/data     # POST: Dados do relatório de agendamentos (AJAX)
/tenant/reports/appointments/export/excel # Exportar relatório de agendamentos (Excel)
/tenant/reports/appointments/export/pdf   # Exportar relatório de agendamentos (PDF)
/tenant/reports/appointments/export/csv  # Exportar relatório de agendamentos (CSV)
/tenant/reports/patients              # Relatório de pacientes
/tenant/reports/patients/data         # POST: Dados do relatório de pacientes (AJAX)
/tenant/reports/patients/export/excel # Exportar relatório de pacientes (Excel)
/tenant/reports/patients/export/pdf   # Exportar relatório de pacientes (PDF)
/tenant/reports/patients/export/csv  # Exportar relatório de pacientes (CSV)
/tenant/reports/doctors               # Relatório de médicos
/tenant/reports/doctors/data          # POST: Dados do relatório de médicos (AJAX)
/tenant/reports/doctors/export/excel  # Exportar relatório de médicos (Excel)
/tenant/reports/doctors/export/pdf    # Exportar relatório de médicos (PDF)
/tenant/reports/doctors/export/csv   # Exportar relatório de médicos (CSV)
/tenant/reports/recurring             # Relatório de recorrências
/tenant/reports/recurring/data        # POST: Dados do relatório de recorrências (AJAX)
/tenant/reports/recurring/export/excel # Exportar relatório de recorrências (Excel)
/tenant/reports/recurring/export/pdf  # Exportar relatório de recorrências (PDF)
/tenant/reports/recurring/export/csv # Exportar relatório de recorrências (CSV)
/tenant/reports/forms                 # Relatório de formulários
/tenant/reports/forms/data            # POST: Dados do relatório de formulários (AJAX)
/tenant/reports/forms/export/excel    # Exportar relatório de formulários (Excel)
/tenant/reports/forms/export/pdf      # Exportar relatório de formulários (PDF)
/tenant/reports/forms/export/csv      # Exportar relatório de formulários (CSV)
/tenant/reports/portal                # Relatório do portal do paciente
/tenant/reports/portal/data           # POST: Dados do relatório do portal (AJAX)
/tenant/reports/portal/export/excel   # Exportar relatório do portal (Excel)
/tenant/reports/portal/export/pdf     # Exportar relatório do portal (PDF)
/tenant/reports/portal/export/csv     # Exportar relatório do portal (CSV)
/tenant/reports/notifications         # Relatório de notificações
/tenant/reports/notifications/data    # POST: Dados do relatório de notificações (AJAX)
/tenant/reports/notifications/export/excel # Exportar relatório de notificações (Excel)
/tenant/reports/notifications/export/pdf   # Exportar relatório de notificações (PDF)
/tenant/reports/notifications/export/csv  # Exportar relatório de notificações (CSV)
/tenant/appointments/online/{id}     # Visualizar/configurar agendamento online
/tenant/appointments/online/{id}/save # Salvar instruções do agendamento online
/tenant/appointments/online/{id}/send-email # Enviar instruções por email
/tenant/appointments/online/{id}/send-whatsapp # Enviar instruções por WhatsApp

# APIs para agendamentos
/tenant/api/doctors/{doctorId}/calendars
/tenant/api/doctors/{doctorId}/appointment-types
/tenant/api/doctors/{doctorId}/specialties
/tenant/api/doctors/{doctorId}/available-slots

# APIs para agendamentos recorrentes
/tenant/api/doctors/{doctorId}/business-hours
/tenant/api/doctors/{doctorId}/available-slots-recurring

# Rotas de relatórios (com exportação)
/tenant/reports/appointments/data          # POST: Dados do relatório (AJAX)
/tenant/reports/appointments/export/excel  # Exportar Excel
/tenant/reports/appointments/export/pdf   # Exportar PDF
/tenant/reports/appointments/export/csv   # Exportar CSV
# (mesmo padrão para: patients, doctors, recurring, forms, portal, notifications)

# Rotas especiais
/tenant/profile                      # GET: Editar perfil do usuário logado
/tenant/profile                      # PUT: Atualizar perfil do usuário logado
/tenant/users/{id}/change-password   # Alterar senha de usuário
/tenant/users/{id}/doctor-permissions # Permissões de médicos para usuários
/tenant/users/{id}/allowed-doctors    # API: Médicos permitidos para usuário
/tenant/patients/{id}/login            # GET: Formulário para gerenciar login do paciente
/tenant/patients/{id}/login            # POST: Criar/atualizar credenciais de login
/tenant/patients/{id}/login            # DELETE: Remover credenciais de login
/tenant/patients/{id}/login/toggle    # POST: Ativar/desativar login do paciente
/tenant/patients/{id}/login/show      # GET: Visualizar credenciais do paciente
/tenant/patients/{id}/login/send-email # POST: Enviar credenciais por email
/tenant/patients/{id}/login/send-whatsapp # POST: Enviar credenciais por WhatsApp
/tenant/calendars/events              # Redirecionar para eventos do calendário
/tenant/calendars/{id}/events         # Eventos do calendário
/tenant/forms/{id}/builder            # Construir formulário
/tenant/forms/{id}/preview            # Visualizar formulário
/tenant/forms/{id}/clear-content      # Limpar conteúdo do formulário
/tenant/forms/{id}/sections           # Adicionar seção ao formulário
/tenant/sections/{id}                 # Atualizar/deletar seção
/tenant/forms/{id}/questions          # Adicionar pergunta ao formulário
/tenant/questions/{id}                # Atualizar/deletar pergunta
/tenant/questions/{id}/options        # Adicionar opção à pergunta
/tenant/options/{id}                  # Atualizar/deletar opção
/tenant/doctors/{doctorId}/specialties # API: Especialidades do médico
/tenant/doctor-settings                # Configurações do médico (página única)
/tenant/doctor-settings/calendar       # PUT: Atualizar calendário do médico
/tenant/doctor-settings/business-hour  # POST: Criar horário comercial
/tenant/doctor-settings/business-hour/{id} # PUT: Atualizar horário comercial
/tenant/doctor-settings/business-hour/{id} # DELETE: Deletar horário comercial
/tenant/doctor-settings/appointment-type # POST: Criar tipo de consulta
/tenant/doctor-settings/appointment-type/{id} # PUT: Atualizar tipo de consulta
/tenant/doctor-settings/appointment-type/{id} # DELETE: Deletar tipo de consulta
/tenant/agendamento-publico            # Link público de agendamento
/tenant/forms/{form_id}/responses/create # Criar resposta de formulário
/tenant/forms/{form_id}/responses     # Salvar resposta de formulário
/tenant/responses/{id}/answer         # Adicionar resposta individual
/tenant/answers/{id}                   # Atualizar resposta individual
/tenant/integrations/google            # Lista médicos e status de integração Google
/tenant/integrations/google/{doctor}/connect # Conectar conta Google do médico
/tenant/integrations/google/{doctor}/disconnect # Desconectar conta Google
/tenant/integrations/google/{doctor}/status # Status da integração (JSON)
/tenant/integrations/google/api/{doctor}/events # Eventos do Google Calendar (JSON)
/tenant/integrations/apple             # Lista médicos e status de integração Apple Calendar
/tenant/integrations/apple/{doctor}/connect # GET: Formulário de conexão Apple Calendar
/tenant/integrations/apple/{doctor}/connect # POST: Conectar conta Apple do médico
/tenant/integrations/apple/{doctor}/disconnect # Desconectar conta Apple
/tenant/integrations/apple/{doctor}/status # Status da integração (JSON)
/tenant/integrations/apple/api/{doctor}/events # Eventos do Apple Calendar (JSON)
/tenant/notifications/json             # API: Notificações em JSON
/tenant/notifications/{id}/read      # Marcar notificação como lida
/tenant/notifications/mark-all-read   # Marcar todas como lidas
```

### Portal do Paciente

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
GET  /paciente/agendamentos                   # Lista de agendamentos
GET  /paciente/agendamentos/criar             # Criar agendamento
POST /paciente/agendamentos                   # Processar criação
GET  /paciente/agendamentos/{id}/editar       # Editar agendamento
PUT  /paciente/agendamentos/{id}              # Atualizar agendamento
POST /paciente/agendamentos/{id}/cancelar     # Cancelar agendamento
GET  /paciente/notificacoes                   # Notificações do paciente
GET  /paciente/perfil                         # Perfil do paciente
POST /paciente/perfil                         # Atualizar perfil
POST /paciente/logout                         # Logout
GET  /paciente/logout                         # Logout (GET)
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
| `Integrations/GoogleCalendarController` | Integração Google Calendar | `/tenant/integrations/google` |
| `Integrations/AppleCalendarController` | Integração Apple Calendar (iCloud) | `/tenant/integrations/apple` |
| `CalendarSyncStateController` | Estado de sincronização de calendário | `/tenant/calendar-sync` |
| `SettingsController` | Configurações do tenant | `/tenant/settings` |
| `RecurringAppointmentController` | Agendamentos recorrentes | `/tenant/agendamentos/recorrentes` |
| `UserDoctorPermissionController` | Permissões de médicos para usuários | `/tenant/users/{id}/doctor-permissions` |
| `ProfileController` | Perfil do usuário do tenant | `/tenant/profile` |
| `NotificationController` | Notificações do tenant | `/tenant/notifications` |
| `OnlineAppointmentController` | Agendamentos online e instruções | `/tenant/appointments/online` |
| `PublicPatientController` | Identificação de paciente (área pública) | `/t/{tenant}/agendamento/identificar` |
| `PublicPatientRegisterController` | Cadastro de paciente (área pública) | `/t/{tenant}/agendamento/cadastro` |
| `PublicAppointmentController` | Criação de agendamento (área pública) | `/t/{tenant}/agendamento/criar` |
| `PublicFormController` | Formulários públicos para pacientes | `/t/{tenant}/formulario/{form}/responder` |
| `PatientPortal/AuthController` | Autenticação do portal do paciente | `/t/{tenant}/paciente/login` |
| `PatientPortal/DashboardController` | Dashboard do portal do paciente | `/paciente/dashboard` |
| `PatientPortal/AppointmentController` | Agendamentos do portal do paciente | `/paciente/agendamentos` |
| `PatientPortal/NotificationController` | Notificações do portal do paciente | `/paciente/notificacoes` |
| `PatientPortal/ProfileController` | Perfil do portal do paciente | `/paciente/perfil` |
| `DoctorSettingsController` | Configurações do médico (página única) | `/tenant/doctor-settings` |
| `Reports/ReportController` | Página inicial de relatórios | `/tenant/reports` |
| `Reports/AppointmentReportController` | Relatório de agendamentos | `/tenant/reports/appointments` |
| `Reports/PatientReportController` | Relatório de pacientes | `/tenant/reports/patients` |
| `Reports/DoctorReportController` | Relatório de médicos | `/tenant/reports/doctors` |
| `Reports/RecurringReportController` | Relatório de recorrências | `/tenant/reports/recurring` |
| `Reports/FormReportController` | Relatório de formulários | `/tenant/reports/forms` |
| `Reports/PortalReportController` | Relatório do portal do paciente | `/tenant/reports/portal` |
| `Reports/NotificationReportController` | Relatório de notificações | `/tenant/reports/notifications` |

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
| `RecurringAppointment` | `recurring_appointments` | Agendamentos recorrentes |
| `RecurringAppointmentRule` | `recurring_appointment_rules` | Regras de recorrência |
| `UserDoctorPermission` | `user_doctor_permissions` | Permissões de médicos para usuários |
| `PatientLogin` | `patient_logins` | Credenciais de login dos pacientes |
| `Notification` | `notifications` | Notificações do tenant |
| `TenantSetting` | `tenant_settings` | Configurações específicas do tenant |
| `GoogleCalendarToken` | `google_calendar_tokens` | Tokens OAuth do Google Calendar por médico |
| `AppleCalendarToken` | `apple_calendar_tokens` | Tokens CalDAV do Apple Calendar (iCloud) por médico |
| `OnlineAppointmentInstruction` | `online_appointment_instructions` | Instruções para consultas online |
| `Module` | - | Módulos de acesso (helper) |

### Características Importantes

- Todos os models usam `protected $connection = 'tenant'`
- `User` (Tenant) possui relacionamento `belongsTo` com `Platform\Tenant`
- `User` possui campo `modules` (JSON) para controle de acesso interno
- `Patient` possui relacionamento com `PatientLogin` para acesso ao portal
- `RecurringAppointment` possui relacionamento com `RecurringAppointmentRule` para definir regras de recorrência
- `UserDoctorPermission` gerencia quais médicos cada usuário pode acessar
- `Notification` usa UUID como chave primária e possui relacionamento polimórfico
- `TenantSetting` armazena configurações específicas do tenant em formato chave-valor
- `GoogleCalendarToken` armazena tokens OAuth do Google Calendar vinculados a médicos (`doctor_id`)
- `AppleCalendarToken` armazena credenciais CalDAV do Apple Calendar (iCloud) vinculadas a médicos (`doctor_id`)
- `Doctor` possui relacionamentos com `GoogleCalendarToken` e `AppleCalendarToken` para integrações de calendário
- `Appointment` possui campo `appointment_mode` (presencial/online) e relacionamento com `OnlineAppointmentInstruction`
- `OnlineAppointmentInstruction` armazena instruções para consultas online (link de reunião, aplicativo, instruções)
- `RecurringAppointment` também possui campo `appointment_mode` para definir se a recorrência é presencial ou online
- `User` possui campo `role` que define o papel do usuário (`admin`, `doctor`, `user`) e controla o acesso a dados
- `Doctor` possui campos de personalização: `signature`, `label_singular`, `label_plural`, `registration_label`, `registration_value`
- O sistema aplica filtros automáticos baseados no role do usuário através do trait `HasDoctorFilter`

---

## ⚙️ Funcionalidades Principais

### 1. Gerenciamento de Médicos

**Criar Médico:**
1. Acesse `/tenant/doctors`
2. Clique em "Criar Médico"
3. Preencha:
   - **Usuário**: Selecione um usuário existente (usuários que já são médicos não aparecem)
   - **Número de Registro**: CRM, CRP, CRO ou outro número de registro profissional
   - **Estado do Registro**: Sigla do estado (ex: SP, RJ)
   - **Especialidades**: Selecione uma ou mais especialidades médicas
   - **Assinatura**: Upload da assinatura digital do médico (opcional)
   - **Labels Personalizados** (opcional):
     - **Label Singular**: Nome no singular (ex: "Médico", "Dentista", "Psicólogo")
     - **Label Plural**: Nome no plural (ex: "Médicos", "Dentistas", "Psicólogos")
   - **Campos de Registro Personalizados** (opcional):
     - **Label do Registro**: Nome do campo de registro (ex: "CRM", "CRP", "CRO")
     - **Valor do Registro**: Valor do registro profissional

**Vinculação Automática de Permissões:**
- Quando um usuário comum (role `user`) cadastra um médico, ele **automaticamente recebe permissão** para visualizar e gerenciar esse médico
- Isso facilita o workflow onde um usuário cria o médico e já pode trabalhar com ele

**Personalização de Labels:**
- Os labels personalizados permitem adaptar a terminologia do sistema para diferentes tipos de profissionais
- Por exemplo, uma clínica odontológica pode usar "Dentista" ao invés de "Médico"
- Os labels são usados na interface do sistema para exibição personalizada

**Campos de Registro:**
- Permite personalizar o tipo de registro profissional (CRM, CRP, CRO, etc.)
- Útil para clínicas que atendem diferentes categorias de profissionais de saúde

**Restrições de Acesso:**
- Apenas usuários com módulo `doctors` podem acessar o gerenciamento de médicos
- Os filtros baseados em role são aplicados automaticamente na listagem
- Médicos (role `doctor`) só veem seu próprio perfil
- Usuários comuns (role `user`) só veem médicos aos quais têm permissão

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

**Configurações do Médico (Página Única):**
- Para médicos ou usuários com acesso a apenas um médico, existe uma página única de configurações:
- Acesse `/tenant/doctor-settings`
- Nesta página você pode:
  - Atualizar calendário do médico
  - Gerenciar horários comerciais (criar, editar, deletar)
  - Gerenciar tipos de consulta (criar, editar, deletar)
- Esta página facilita o gerenciamento quando há apenas um médico no contexto

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
   - Modo de atendimento (presencial/online) - se habilitado nas configurações
   - Data e horário
   - Observações (opcional)

**Modos de Atendimento:**
- **Presencial**: Consulta física na clínica
- **Online**: Consulta virtual via videoconferência
- A configuração padrão pode ser definida em `/tenant/settings` → **Configurações de Agendamentos** → `default_appointment_mode`
  - `presencial`: Apenas agendamentos presenciais
  - `online`: Apenas agendamentos online
  - `user_choice`: Usuário escolhe no momento do agendamento

**Visualizar Calendário:**
- Acesse `/tenant/appointments`
- Visualize agendamentos em formato de calendário
- Filtre por médico, data, modo de atendimento, etc.

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

**Ver Guia Completo:** [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md)

**Formulários Públicos e Envio Automático:**

O sistema possui funcionalidade de **envio automático de links de formulários** aos pacientes quando um agendamento é criado:

- **Prioridade de Seleção**: O sistema busca automaticamente um formulário ativo para o agendamento seguindo esta ordem:
  1. Formulário vinculado ao médico do agendamento
  2. Formulário vinculado à especialidade do agendamento
  3. Se nenhum for encontrado, nenhum link é enviado

- **Envio Automático**: Quando um agendamento é criado e existe um formulário ativo correspondente:
  - O sistema gera automaticamente um link público para o paciente responder o formulário
  - O link é enviado por **email** e/ou **WhatsApp** conforme as configurações do tenant
  - O link inclui o ID do agendamento, permitindo vincular a resposta ao agendamento

- **Configurações de Notificação** (em `/tenant/settings`):
  - `notifications.form_send_email`: Habilita/desabilita envio de formulário por email (padrão: `false`)
  - `notifications.form_send_whatsapp`: Habilita/desabilita envio de formulário por WhatsApp (padrão: `false`)
  - `notifications.send_email_to_patients`: Habilita/desabilita envio de emails aos pacientes (padrão: `false`)
  - `notifications.send_whatsapp_to_patients`: Habilita/desabilita envio de WhatsApp aos pacientes (padrão: `false`)

- **URL do Formulário Público**: 
  - Formato: `/t/{tenant}/formulario/{form}/responder?appointment={appointment_id}`
  - O paciente pode responder o formulário sem precisar estar logado
  - A resposta é automaticamente vinculada ao agendamento quando o `appointment_id` está presente

- **Resposta do Formulário**:
  - Após responder, o paciente é redirecionado para uma página de sucesso
  - A resposta fica disponível em `/tenant/responses` para visualização pela clínica

### 7. Respostas de Formulários

**Visualizar Respostas:**
1. Acesse `/tenant/responses`
2. Visualize todas as respostas coletadas
3. Filtre por formulário, paciente, data, etc.
4. Clique em "Ver" para visualizar resposta completa

### 8. Agendamentos Online

**Gerenciar Agendamentos Online:**
1. Acesse `/tenant/appointments/online`
2. Visualize apenas agendamentos com modo "online"
3. Clique em um agendamento para configurar instruções
4. Configure:
   - **Link da reunião**: URL da videoconferência (Zoom, Google Meet, etc.)
   - **Aplicativo**: Nome do aplicativo utilizado (opcional)
   - **Instruções gerais**: Informações para o paciente sobre a consulta
   - **Instruções específicas**: Orientações personalizadas

**Enviar Instruções:**
- Após configurar, envie as instruções por:
  - **Email**: Clique em "Enviar por Email" (requer `notifications.send_email_to_patients` habilitado)
  - **WhatsApp**: Clique em "Enviar por WhatsApp" (requer `notifications.send_whatsapp_to_patients` habilitado)
- O sistema registra quando e por qual canal as instruções foram enviadas

**Configurações Necessárias:**
- O módulo `online_appointments` deve estar habilitado para o usuário
- O modo padrão de agendamento deve permitir consultas online (`online` ou `user_choice`)
- Para envio automático, configure notificações em `/tenant/settings`

**Importante:**
- Agendamentos online são automaticamente identificados pelo campo `appointment_mode = 'online'`
- Cada agendamento online pode ter instruções específicas vinculadas
- As instruções são enviadas apenas manualmente pelo administrador/clínica
- O paciente recebe as informações necessárias para participar da consulta virtual

### 9. Atendimento Médico

O módulo de **Atendimento Médico** permite realizar sessões de atendimento do dia, facilitando o fluxo de trabalho durante o atendimento aos pacientes.

**Acessar Atendimento Médico:**
1. Acesse `/tenant/atendimento`
2. Selecione o dia desejado para iniciar a sessão de atendimento
3. O sistema exibirá todos os agendamentos do dia filtrados conforme permissões do usuário

**Funcionalidades:**
- **Visualização de Agendamentos do Dia**: Lista todos os agendamentos agendados, confirmados, chegados ou em atendimento
- **Detalhes do Agendamento**: Clique em um agendamento para ver:
  - Dados do paciente
  - Dados do médico
  - Tipo de consulta e especialidade
  - Observações
  - **Resposta do Formulário**: Se o paciente respondeu um formulário, pode ser visualizado diretamente no modal
- **Gerenciamento de Status**: Alterar status do atendimento:
  - `scheduled` - Agendado
  - `arrived` - Paciente chegou
  - `in_service` - Em atendimento
  - `completed` - Concluído
  - `cancelled` - Cancelado
- **Navegação entre Agendamentos**: Após concluir um atendimento, o sistema pode redirecionar automaticamente para o próximo agendamento do dia

**Controle de Acesso:**
- Requer módulo `medical_appointments` habilitado
- Filtros automáticos baseados em roles são aplicados:
  - **Admin**: Vê todos os agendamentos do dia
  - **Doctor**: Vê apenas seus próprios agendamentos
  - **User**: Vê apenas agendamentos dos médicos permitidos

**Integração com Formulários:**
- Se o agendamento possui um formulário respondido pelo paciente, ele é exibido automaticamente no modal de detalhes
- Permite visualizar as respostas antes ou durante o atendimento

### 10. Agendamentos Recorrentes

**Criar Agendamento Recorrente:**
1. Acesse `/tenant/agendamentos/recorrentes`
2. Clique em "Criar Agendamento Recorrente"
3. Preencha:
   - Paciente
   - Médico
   - Tipo de consulta
   - **Modo de atendimento** (presencial/online) - se habilitado nas configurações
   - Data de início
   - Tipo de término (data final ou número de sessões)
   - Regras de recorrência (diária, semanal, mensal, etc.)
4. O sistema gerará automaticamente os agendamentos conforme as regras

**Gerenciar Agendamentos Recorrentes:**
- Visualize todos os agendamentos recorrentes ativos
- Edite regras de recorrência
- Cancele agendamentos recorrentes
- Visualize agendamentos gerados a partir da recorrência

**Importante:**
- Agendamentos recorrentes também suportam modo online/presencial
- Se o modo padrão estiver configurado como `presencial` ou `online`, todos os agendamentos gerados seguirão esse modo
- Se o modo padrão for `user_choice`, você pode escolher o modo ao criar a recorrência

### 11. Relatórios

O sistema possui um módulo completo de **Relatórios** que permite gerar análises detalhadas de diversos aspectos da clínica.

**Acessar Relatórios:**
1. Acesse `/tenant/reports`
2. Selecione o tipo de relatório desejado
3. Configure filtros (data, médico, status, etc.)
4. Visualize os dados e exporte se necessário

**Tipos de Relatórios Disponíveis:**

1. **Relatório de Agendamentos** (`/tenant/reports/appointments`)
   - Lista todos os agendamentos com filtros avançados
   - Filtros: Período, médico, paciente, status, modo de atendimento, etc.
   - Exportação: Excel, PDF, CSV

2. **Relatório de Pacientes** (`/tenant/reports/patients`)
   - Lista todos os pacientes cadastrados
   - Filtros: Período de cadastro, médicos atendidos, etc.
   - Exportação: Excel, PDF, CSV

3. **Relatório de Médicos** (`/tenant/reports/doctors`)
   - Lista todos os médicos e estatísticas
   - Filtros: Especialidade, status, etc.
   - Exportação: Excel, PDF, CSV

4. **Relatório de Recorrências** (`/tenant/reports/recurring`)
   - Lista agendamentos recorrentes
   - Filtros: Período, médico, paciente, status, etc.
   - Exportação: Excel, PDF, CSV

5. **Relatório de Formulários** (`/tenant/reports/forms`)
   - Lista formulários e respostas
   - Filtros: Formulário, médico, paciente, período, etc.
   - Exportação: Excel, PDF, CSV

6. **Relatório do Portal do Paciente** (`/tenant/reports/portal`)
   - Estatísticas de uso do portal do paciente
   - Filtros: Período, ações realizadas, etc.
   - Exportação: Excel, PDF, CSV

7. **Relatório de Notificações** (`/tenant/reports/notifications`)
   - Lista notificações enviadas
   - Filtros: Tipo, destinatário, período, status, etc.
   - Exportação: Excel, PDF, CSV

**Exportação de Dados:**
- Todos os relatórios suportam exportação em múltiplos formatos:
  - **Excel** (`.xlsx`): Formato adequado para análises e planilhas
  - **PDF** (`.pdf`): Formato adequado para impressão e arquivamento
  - **CSV** (`.csv`): Formato adequado para importação em outros sistemas
- As exportações são geradas dinamicamente com base nos filtros aplicados
- Cada relatório possui rotas específicas para exportação: `/tenant/reports/{tipo}/export/{formato}`

**Controle de Acesso:**
- Requer módulo `reports` habilitado
- Filtros baseados em roles são aplicados automaticamente:
  - **Admin**: Vê todos os dados
  - **Doctor**: Vê apenas seus próprios dados
  - **User**: Vê apenas dados dos médicos permitidos

### 12. Sistema de Roles e Permissões

O sistema possui um controle de acesso baseado em roles (papéis) que define automaticamente o que cada usuário pode ver e acessar.

#### Roles Disponíveis

1. **Administrador (`admin`)**:
   - Acesso completo a todos os médicos e funcionalidades
   - Vê todos os dados do sistema sem restrições
   - Pode gerenciar qualquer médico, agendamento, paciente, etc.
   - Não possui filtros de acesso

2. **Médico (`doctor`)**:
   - Acesso restrito apenas aos seus próprios dados
   - Vê apenas seu próprio perfil de médico
   - Acessa apenas seus próprios agendamentos, calendários, formulários, pacientes, etc.
   - Filtros automáticos são aplicados para garantir que só veja seus dados

3. **Usuário Comum (`user`)**:
   - Acesso restrito a médicos específicos com permissão
   - Vê apenas médicos aos quais foi explicitamente permitido
   - Se não tiver médicos permitidos, não vê nenhum dado
   - Pode ser vinculado a um ou mais médicos via permissões

#### Filtros Automáticos

O sistema aplica filtros automáticos baseados no role em todas as listagens:

- **Controllers**: Usam o trait `HasDoctorFilter` para aplicar filtros automaticamente
- **Queries**: Filtros são aplicados antes de buscar dados do banco
- **Transparente**: Os filtros funcionam automaticamente sem necessidade de configuração manual

#### Como Funciona

- Quando um usuário acessa uma listagem (médicos, agendamentos, calendários, etc.)
- O sistema identifica o role do usuário
- Aplica o filtro apropriado:
  - `admin`: Sem filtro (vê tudo)
  - `doctor`: Filtra por `doctor_id = usuário.doctor.id`
  - `user`: Filtra por `doctor_id IN (médicos_permitidos)`

### 13. Permissões de Médicos para Usuários

**Gerenciar Permissões:**
1. Acesse `/tenant/users/{id}/doctor-permissions`
2. Selecione quais médicos o usuário pode gerenciar
3. Salve as permissões
4. O usuário terá acesso apenas aos médicos permitidos

**Vinculação Automática:**
- Quando um usuário comum (role `user`) **cadastra um novo médico**, ele **automaticamente recebe permissão** para visualizar e gerenciar esse médico
- Isso facilita o workflow: o usuário cria o médico e já pode trabalhar com ele sem precisar configurar permissões manualmente
- Permissões adicionais podem ser adicionadas posteriormente através da página de gerenciamento de permissões

**Importante:**
- Permissões são necessárias apenas para usuários com role `user`
- Administradores (role `admin`) veem todos os médicos automaticamente
- Médicos (role `doctor`) só veem seus próprios dados, independente de permissões

### 14. Integrações

O sistema suporta integrações com calendários externos para sincronização automática de agendamentos:

- **Google Calendar**: Sincronização via Google Calendar API (OAuth 2.0)
- **Apple Calendar (iCloud)**: Sincronização via protocolo CalDAV

#### Google Calendar

A integração com Google Calendar permite sincronizar automaticamente os agendamentos com o calendário do Google de cada médico.

**Características:**
- ✅ Cada médico pode conectar sua própria conta do Google Calendar
- ✅ Sincronização automática ao criar, editar ou excluir agendamentos
- ✅ Tokens armazenados de forma segura no banco do tenant
- ✅ Renovação automática de tokens expirados
- ✅ Integração com FullCalendar (opcional)

**Configuração:**

1. **Configurar Credenciais Google OAuth:**
   - Acesse o [Google Cloud Console](https://console.cloud.google.com/)
   - Crie um projeto ou selecione um existente
   - Ative a API do Google Calendar
   - Crie credenciais OAuth 2.0 (tipo: Aplicativo Web)
   - **IMPORTANTE:** Configure a URI de redirecionamento como uma rota **global** (não dentro do grupo tenant):
     - **URI de redirecionamento:** `{APP_URL}/google/callback`
     - Exemplos:
       - Local: `http://localhost:8000/google/callback`
       - Produção: `https://seudominio.com/google/callback`
       - Ngrok: `https://seu-id.ngrok-free.app/google/callback`
   - Adicione as credenciais no arquivo `.env`:
     ```
     GOOGLE_CLIENT_ID=seu_client_id
     GOOGLE_CLIENT_SECRET=seu_client_secret
     ```
     **Nota:** O sistema usa automaticamente a rota `route('google.callback')` que resolve para `/google/callback` baseado no `APP_URL`. Certifique-se de que a URI configurada no Google Cloud Console corresponda exatamente à URL completa (incluindo domínio e porta). A URI deve ser **sem barra final** e **sem parâmetros**.

2. **Conectar Conta do Médico:**
   - Acesse `/tenant/integrations/google`
   - Clique em "Conectar Google" para o médico desejado
   - Será redirecionado para o Google OAuth
   - Autorize o acesso ao Google Calendar
   - O token será salvo automaticamente vinculado ao médico (não ao usuário)
   - Cada médico pode conectar sua própria conta Google individualmente

3. **Sincronização Automática:**
   - ✅ **Totalmente automática** via Observers - sincroniza TODOS os agendamentos criados, editados ou excluídos, independente de onde sejam criados (área administrativa, área pública, portal do paciente, etc.)
   - **Agendamentos Normais:** Sincronizados via `AppointmentObserver` quando criados, editados, cancelados ou deletados
   - **Agendamentos Recorrentes:** Sincronizados via `RecurringAppointmentObserver` quando criados, editados, cancelados ou deletados
   - Ao criar um agendamento, o evento é criado no Google Calendar do médico
   - **Ao editar um agendamento:** O evento antigo é deletado e um novo é criado com as informações atualizadas (estratégia mais simples e confiável que garante consistência)
   - **Ao cancelar um agendamento:** O evento é removido do Google Calendar do médico
   - Ao excluir um agendamento, o evento é removido do Google Calendar do médico
   - A sincronização só ocorre se o médico (dono do calendário) tiver token conectado
   - O sistema busca o token através do `doctor_id` do calendário do agendamento
   - **Importante:** A sincronização funciona para agendamentos criados em qualquer lugar do sistema (área administrativa, área pública, portal do paciente, comandos, etc.)

4. **Sincronização de Agendamentos Recorrentes:**
   - ✅ **Eventos Recorrentes no Google Calendar** - Agendamentos recorrentes são sincronizados como eventos recorrentes (RRULE) no Google Calendar
   - Quando uma recorrência é criada, um evento recorrente é criado no Google Calendar automaticamente
   - **IMPORTANTE:** Agendamentos individuais gerados por recorrências NÃO são sincronizados separadamente (evita duplicação)
   - **Ao editar uma recorrência:** Os eventos antigos são deletados e novos são criados com as informações atualizadas (estratégia mais simples e confiável que garante consistência)
   - **Ao reativar uma recorrência (active = true):** Os eventos recorrentes são criados novamente no Google Calendar
   - **Ao cancelar uma recorrência (active = false):** 
     - ✅ **Mantém histórico:** Eventos passados são mantidos no Google Calendar como histórico
     - ✅ **Remove apenas futuros:** Apenas eventos futuros são removidos (atualiza data fim para hoje)
     - ✅ **Funciona para TODOS os tipos:** Aplica-se tanto para recorrências com data fim quanto sem data fim
     - Exemplo: Recorrência criada em 05/06/2025, cancelada em 29/11/2025 → eventos de 05/06/2025 até 29/11/2025 permanecem, eventos após 29/11/2025 são removidos
     - **Com data fim:** Se tinha data fim em 05/06/2026 e foi cancelada em 29/11/2025, eventos até 29/11/2025 permanecem
     - **Sem data fim:** Se não tinha data fim e foi cancelada em 29/11/2025, eventos até 29/11/2025 permanecem
   - **Proteção contra eventos infinitos:**
     - Recorrências com data fim: usa a data fim definida
     - Recorrências com número de sessões: calcula data fim aproximada
     - Recorrências sem data fim: usa data fim padrão de **1 ano** (evita criação infinita)
   - **Renovação Automática:** Para recorrências sem data fim, o sistema renova automaticamente os eventos recorrentes no Google Calendar:
     - Comando `php artisan google-calendar:renew-recurring-events` deve ser agendado no cron para rodar mensalmente
     - Renova eventos que estão próximos do fim (criados há 11+ meses)
     - Estende a data fim por mais 1 ano automaticamente
     - Exemplo: Recorrência criada em 2025 → evento até 2026 → renovado automaticamente em 2026 → evento até 2027
     - **Configuração do Cron:** Adicione ao crontab: `0 0 1 * * cd /path-to-project && php artisan google-calendar:renew-recurring-events`
   - Cada regra de recorrência (ex: segunda e quarta) cria um evento recorrente separado no Google Calendar
   - O sistema armazena os IDs dos eventos recorrentes para permitir renovação e remoção
   - **Proteção contra duplicação:** O sistema verifica se já existe evento recorrente antes de criar. Se existe, deleta o antigo antes de criar novo. Cada evento é identificado por `recurring_appointment_id` + `rule_id` armazenados como propriedades privadas

5. **Proteção contra Duplicação (Todos os Tipos de Agendamento):**
   - ✅ **Agendamentos Normais:**
     - Verifica se `google_event_id` já existe antes de criar
     - Se existe, deleta o evento antigo do Google Calendar e cria um novo (estratégia mais simples e confiável)
     - Se o evento não existe mais no Google Calendar (foi removido manualmente), limpa o ID inválido e cria novo evento
     - Cada evento é identificado por: `appointment_id` armazenado como propriedade privada (`extendedProperties.private.appointment_id`) e na descrição
     - **Estratégia de Edição:** Para garantir consistência, ao editar um agendamento, o sistema sempre deleta o evento antigo e cria um novo ao invés de atualizar
   - ✅ **Agendamentos Recorrentes:**
     - Verifica se `google_recurring_event_ids` já contém evento para a regra
     - Se existe, deleta o evento antigo do Google Calendar antes de criar novo
     - Cada evento é identificado por: `recurring_appointment_id` + `rule_id` armazenados como propriedades privadas (`extendedProperties.private`)
     - **Estratégia de Edição:** Para garantir consistência, ao editar uma recorrência, o sistema sempre deleta os eventos antigos e cria novos ao invés de atualizar
   - ✅ **Proteção Completa:**
     - Mesmo se o Observer for disparado múltiplas vezes, não cria duplicatas
     - Se evento foi removido manualmente do Google Calendar, detecta e cria novo
     - Todos os eventos têm metadados de identificação para rastreamento
     - Uso de `withoutEvents()` para evitar loops infinitos ao atualizar `google_event_id` no banco

6. **Desconectar:**
   - Acesse `/tenant/integrations/google`
   - Clique em "Desconectar" para o médico desejado
   - O token será removido do banco de dados
   - **Importante:** Os eventos já criados no Google Calendar **não** serão removidos automaticamente ao desconectar
   - Se desejar remover os eventos do Google Calendar, faça isso manualmente ou remova os agendamentos do sistema

**Rotas Disponíveis:**

**Rotas Autenticadas (dentro do tenant):**
- `GET /tenant/integrations/google` - Lista médicos e status de integração (requer módulo `integrations`)
- `GET /tenant/integrations/google/{doctor}/connect` - Inicia conexão OAuth (requer módulo `integrations`)
- `DELETE /tenant/integrations/google/{doctor}/disconnect` - Remove integração (requer módulo `integrations`)
- `GET /tenant/integrations/google/{doctor}/status` - Status da integração (JSON, requer módulo `integrations`)
- `GET /tenant/integrations/google/api/{doctor}/events` - Eventos do Google Calendar (JSON para FullCalendar, requer módulo `integrations`)

**Rota Global (pública, sem tenant na URL):**
- `GET /google/callback` - Callback do Google OAuth (rota global, não requer autenticação, processa automaticamente o tenant através do parâmetro `state`)

**Estrutura de Dados:**
- Tabela `google_calendar_tokens`: Armazena tokens OAuth por médico (vinculado a `doctor_id`, não `user_id`)
  - Campos: `id` (UUID), `doctor_id` (UUID, FK para `doctors`), `access_token` (JSON), `refresh_token` (text), `expires_at` (timestamp), `timestamps`
  - Relacionamento: `belongsTo(Doctor::class)`
- Campo `appointments.google_event_id`: ID do evento no Google Calendar para agendamentos normais (text, nullable)
- Campo `recurring_appointments.google_recurring_event_ids`: JSON com IDs dos eventos recorrentes por regra (text, nullable)
  - Formato: `{"rule_id_1": "google_event_id_1", "rule_id_2": "google_event_id_2"}`
- Campo `appointments.recurring_appointment_id`: Relacionamento com agendamentos recorrentes (UUID, nullable, FK para `recurring_appointments`)
- Cada token é único por médico e não é compartilhado entre médicos

**Fluxo de Autenticação OAuth:**
1. Usuário clica em "Conectar Google" no médico desejado
2. Sistema redireciona para Google OAuth com parâmetro `state` contendo: `{tenant: "subdomain", doctor: "doctor_id"}`
3. Google redireciona para `/google/callback` (rota global, sem tenant na URL)
4. Sistema recupera o `state`, identifica o tenant e o médico
5. Sistema troca o código de autorização por tokens
6. Tokens são salvos na tabela `google_calendar_tokens` vinculados ao `doctor_id`

**Serviços e Observers:**
- `GoogleCalendarService`: Serviço principal que gerencia todas as operações com o Google Calendar API
- `AppointmentObserver`: Observer que sincroniza agendamentos normais com o Google Calendar
- `RecurringAppointmentObserver`: Observer que sincroniza agendamentos recorrentes com o Google Calendar
- Observers são registrados automaticamente pelo Laravel através do `EventServiceProvider`

**Importante:**
- Os tokens são vinculados ao médico (`doctor_id`), não ao usuário
- Cada médico deve conectar sua própria conta Google Calendar individualmente
- O sistema usa o parâmetro `state` do OAuth para identificar qual tenant e médico estão conectando durante o callback
- O callback (`/google/callback`) é uma rota global que processa automaticamente o tenant correto através do `state`
- A sincronização busca o token através do relacionamento `calendar -> doctor -> googleCalendarToken`
- Tokens expirados são renovados automaticamente usando o `refresh_token` quando necessário
- Agendamentos individuais gerados por recorrências **NÃO** são sincronizados separadamente (evita duplicação)
- Agendamentos recorrentes são sincronizados como eventos recorrentes (RRULE) no Google Calendar

#### Apple Calendar (iCloud)

A integração com Apple Calendar permite sincronizar automaticamente os agendamentos com o calendário iCloud de cada médico usando o protocolo CalDAV.

**Características:**
- ✅ Cada médico pode conectar sua própria conta do iCloud
- ✅ Sincronização automática ao criar, editar ou excluir agendamentos
- ✅ Protocolo CalDAV padrão para comunicação com iCloud
- ✅ Formato iCalendar (.ics) para eventos
- ✅ Credenciais armazenadas de forma segura (senha criptografada)

**Configuração:**

1. **Conectar Conta do Médico:**
   - Acesse `/tenant/integrations/apple`
   - Clique em "Conectar" para o médico desejado
   - Preencha o formulário:
     - **E-mail**: Seu endereço de e-mail do iCloud
     - **Senha**: Senha do iCloud OU Senha de App Específica (recomendado)
     - **URL do Servidor**: (Opcional) Deixe em branco para usar `https://caldav.icloud.com`
     - **URL do Calendário**: (Opcional) Deixe em branco para descobrir automaticamente
   - O sistema tentará descobrir os calendários disponíveis automaticamente
   - Se bem-sucedido, o token será salvo vinculado ao médico

2. **Sincronização Automática:**
   - ✅ **Totalmente automática** via Observers - sincroniza TODOS os agendamentos criados, editados ou excluídos
   - **Agendamentos Normais:** Sincronizados via `AppointmentObserver` quando criados, editados, cancelados ou deletados
   - **Agendamentos Recorrentes:** Sincronizados via `RecurringAppointmentObserver` quando criados, editados, cancelados ou deletados
   - Ao criar um agendamento, o evento é criado no Apple Calendar do médico
   - **Ao editar um agendamento:** O evento antigo é deletado e um novo é criado com as informações atualizadas
   - **Ao cancelar um agendamento:** O evento é removido do Apple Calendar do médico
   - A sincronização só ocorre se o médico tiver token Apple configurado
   - O sistema busca o token através do `doctor_id` do calendário do agendamento

3. **Desconectar:**
   - Acesse `/tenant/integrations/apple`
   - Clique em "Desconectar" para o médico desejado
   - O token será removido do banco de dados
   - **Importante:** Os eventos já criados no Apple Calendar **não** serão removidos automaticamente ao desconectar

**Rotas Disponíveis:**

**Rotas Autenticadas (dentro do tenant):**
- `GET /tenant/integrations/apple` - Lista médicos e status de integração (requer módulo `integrations`)
- `GET /tenant/integrations/apple/{doctor}/connect` - Mostra formulário de conexão (requer módulo `integrations`)
- `POST /tenant/integrations/apple/{doctor}/connect` - Conecta conta Apple (requer módulo `integrations`)
- `DELETE /tenant/integrations/apple/{doctor}/disconnect` - Remove integração (requer módulo `integrations`)
- `GET /tenant/integrations/apple/{doctor}/status` - Status da integração (JSON, requer módulo `integrations`)
- `GET /tenant/integrations/apple/api/{doctor}/events` - Eventos do Apple Calendar (JSON, requer módulo `integrations`)

**Estrutura de Dados:**
- Tabela `apple_calendar_tokens`: Armazena credenciais CalDAV por médico (vinculado a `doctor_id`)
  - Campos: `id` (UUID), `doctor_id` (UUID, FK para `doctors`), `username` (email iCloud), `password` (criptografado), `server_url` (padrão: `https://caldav.icloud.com`), `calendar_url` (nullable), `timestamps`
  - Relacionamento: `belongsTo(Doctor::class)`
- Campo `appointments.apple_event_id`: UID do evento no Apple Calendar para agendamentos normais (text, nullable)
- Cada token é único por médico e não é compartilhado entre médicos

**Serviços e Observers:**
- `AppleCalendarService`: Serviço principal que gerencia todas as operações com Apple Calendar via CalDAV
- `AppointmentObserver`: Observer que sincroniza agendamentos normais com o Apple Calendar
- `RecurringAppointmentObserver`: Observer que sincroniza agendamentos recorrentes com o Apple Calendar

**Importante:**
- Os tokens são vinculados ao médico (`doctor_id`), não ao usuário
- Cada médico deve conectar sua própria conta iCloud individualmente
- Recomenda-se usar uma **Senha de App Específica** ao invés da senha normal do iCloud (maior segurança e evita problemas com autenticação de dois fatores)
- A sincronização busca o token através do relacionamento `calendar -> doctor -> appleCalendarToken`
- Agendamentos individuais gerados por recorrências **NÃO** são sincronizados separadamente (evita duplicação)
- Para mais detalhes, consulte: [docs/INTEGRACAO_APPLE_CALENDAR.md](docs/INTEGRACAO_APPLE_CALENDAR.md)

### 15. Notificações do Tenant

**Visualizar Notificações:**
1. Acesse `/tenant/notifications`
2. Visualize todas as notificações do sistema
3. Marque como lidas individualmente ou todas de uma vez
4. Filtre por tipo ou status

**API de Notificações:**
- `GET /tenant/notifications/json` - Retorna notificações em JSON
- `POST /tenant/notifications/{id}/read` - Marcar notificação como lida
- `POST /tenant/notifications/mark-all-read` - Marcar todas como lidas

### 16. Configurações de Agendamentos

**Configurações de Modo de Atendimento:**
1. Acesse `/tenant/settings`
2. Clique na aba "Agendamentos"
3. Configure o **Modo Padrão de Atendimento**:
   - **Apenas Presencial**: Todos os agendamentos serão presenciais (módulo online desabilitado)
   - **Apenas Online**: Todos os agendamentos serão online
   - **Escolha do Usuário**: Usuário escolhe no momento do agendamento (presencial ou online)

**Impacto das Configurações:**
- Se configurado como `presencial`: O módulo de agendamentos online fica inacessível (404)
- Se configurado como `online`: Todos os agendamentos criados serão online por padrão
- Se configurado como `user_choice`: Campo de seleção aparece no formulário de agendamento

**Chave de Configuração:**
- `appointments.default_appointment_mode`: Valores possíveis: `presencial`, `online`, `user_choice` (padrão: `user_choice`)

### 17. Configurações de Notificações e Comunicação

O sistema possui configurações flexíveis para envio de notificações aos pacientes:

**Configurações Disponíveis** (em `/tenant/settings`):

- **Notificações de Email:**
  - `notifications.send_email_to_patients`: Habilita/desabilita envio de emails aos pacientes (padrão: `false`)
  - `notifications.form_send_email`: Habilita/desabilita envio de link de formulário por email (padrão: `false`)

- **Notificações de WhatsApp:**
  - `notifications.send_whatsapp_to_patients`: Habilita/desabilita envio de WhatsApp aos pacientes (padrão: `false`)
  - `notifications.form_send_whatsapp`: Habilita/desabilita envio de link de formulário por WhatsApp (padrão: `false`)

**Provedores de Email e WhatsApp:**

O sistema suporta dois tipos de provedores:

1. **Provedor Global**: Usa as configurações do sistema (definidas em `/Platform/settings` ou `.env`)
2. **Provedor do Tenant**: Cada tenant pode configurar seu próprio SMTP e API de WhatsApp

**Configuração de Email do Tenant:**
- Acesse `/tenant/settings`
- Configure:
  - Driver (global ou tenancy)
  - Host SMTP
  - Porta
  - Usuário e senha
  - Email e nome do remetente

**Configuração de WhatsApp do Tenant:**
- Acesse `/tenant/settings`
- Configure:
  - Driver (global ou tenancy)
  - URL da API
  - Token de autenticação
  - Remetente (número de telefone)

**Envio Automático de Formulários:**

Quando um agendamento é criado:
1. O sistema verifica se existe um formulário ativo para o agendamento (médico ou especialidade)
2. Se existir e as configurações estiverem habilitadas:
   - Gera um link público do formulário
   - Envia por email (se `form_send_email` estiver habilitado)
   - Envia por WhatsApp (se `form_send_whatsapp` estiver habilitado)
3. O paciente recebe o link e pode responder sem precisar estar logado
4. A resposta é automaticamente vinculada ao agendamento

### 18. Configurações de Profissionais

O sistema permite personalizar rótulos globais para profissionais de saúde, adaptando a terminologia do sistema para diferentes tipos de clínicas (médicas, odontológicas, psicológicas, etc.).

**Configurações Disponíveis** (em `/tenant/settings`):

- **Personalização Global de Rótulos:**
  - `professional.customization_enabled`: Habilita/desabilita personalização global (padrão: `false`)
  - `professional.label_singular`: Rótulo no singular (ex: "Médico", "Dentista", "Psicólogo")
  - `professional.label_plural`: Rótulo no plural (ex: "Médicos", "Dentistas", "Psicólogos")
  - `professional.registration_label`: Label do campo de registro (ex: "CRM", "CRP", "CRO")

**Como Funciona:**

1. **Habilitar Personalização:**
   - Acesse `/tenant/settings`
   - Vá para a aba "Profissionais"
   - Marque "Habilitar personalização global"
   - Preencha os rótulos desejados
   - Salve

2. **Rótulos Globais vs. Individuais:**
   - Se a personalização global estiver habilitada, os rótulos globais são usados como padrão
   - Cada médico também pode ter seus próprios rótulos individuais que sobrescrevem os globais
   - Útil para clínicas que atendem múltiplos tipos de profissionais

3. **Personalização por Médico:**
   - Cada médico pode ter rótulos personalizados individuais no cadastro
   - Se não definidos, os rótulos globais são usados (se habilitados)
   - Se nem global nem individual estiver configurado, usa os padrões do sistema

**Nota:** As configurações de profissionais permitem adaptar o sistema para diferentes contextos profissionais, mantendo a flexibilidade de personalização individual quando necessário.

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
   - **Se houver formulário ativo**, o link é enviado automaticamente por email/WhatsApp

5. **Responder Formulário (se aplicável)**
   - O paciente recebe um link por email ou WhatsApp
   - URL: `/t/{tenant}/formulario/{form}/responder?appointment={appointment_id}`
   - Preenche o formulário sem precisar estar logado
   - Após responder, é redirecionado para página de sucesso

### Guia de Teste

Para testar a área pública, consulte: [docs/GUIA_TESTE_PUBLICO.md](docs/GUIA_TESTE_PUBLICO.md)

---

## 👤 Portal do Paciente

O portal permite que pacientes acessem suas informações e agendamentos.

### Funcionalidades do Portal

- **Dashboard**: Visão geral de agendamentos
- **Agendamentos**: Lista de agendamentos do paciente
- **Perfil**: Dados pessoais do paciente
- **Notificações**: Notificações recebidas

### Acesso ao Portal

1. O paciente deve ter `login_enabled = true` (gerenciado através de `PatientLogin`)
2. Credenciais são enviadas por email ou WhatsApp automaticamente
3. Acesse: `/t/{tenant}/paciente/login`
4. Após login, redireciona para `/paciente/dashboard`

### Login do Paciente

O sistema possui uma tabela `patient_logins` que armazena:
- `patient_id` - ID do paciente
- `email` - Email de login
- `password` - Senha criptografada
- `remember_token` - Token de "lembrar-me"
- `last_login_at` - Data do último login
- `is_active` - Status ativo/inativo

**Gerenciar Login do Paciente:**
1. Acesse `/tenant/patients/{id}/login` (GET: formulário de gerenciamento)
2. Crie credenciais de login para o paciente (POST: `/tenant/patients/{id}/login`)
3. Envie credenciais por email (POST: `/tenant/patients/{id}/login/send-email`) ou WhatsApp (POST: `/tenant/patients/{id}/login/send-whatsapp`)
4. Ative/desative o acesso do paciente (POST: `/tenant/patients/{id}/login/toggle`)
5. Visualize credenciais (GET: `/tenant/patients/{id}/login/show`)
6. Remova credenciais se necessário (DELETE: `/tenant/patients/{id}/login`)

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

**Ver Guia Completo:** [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md)

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

**Ver Guia de Teste:** [docs/GUIA_TESTE_PUBLICO.md](docs/GUIA_TESTE_PUBLICO.md)

### Habilitar Login do Paciente

1. Acesse `/tenant/patients`
2. Clique em "Gerenciar Login" no paciente desejado
3. Crie credenciais de login (email e senha)
4. Envie credenciais por email ou WhatsApp
5. O paciente poderá acessar o portal em `/t/{tenant}/paciente/login` (rota pública com tenant na URL)
6. Após login, será redirecionado para `/paciente/dashboard` (rota autenticada sem tenant na URL)

### Criar Agendamento Recorrente

1. Acesse `/tenant/agendamentos/recorrentes`
2. Clique em "Criar Agendamento Recorrente"
3. Selecione paciente, médico e tipo de consulta
4. Defina data de início
5. Configure tipo de término (data final ou número de sessões)
6. Defina regras de recorrência (frequência, dias da semana, etc.)
7. Salve
8. O sistema gerará os agendamentos automaticamente

### Gerenciar Permissões de Médicos

1. Acesse `/tenant/users/{id}/doctor-permissions`
2. Selecione quais médicos o usuário pode gerenciar
3. Salve as permissões
4. O usuário terá acesso restrito apenas aos médicos permitidos

---

## 🔄 Migrações

### Migrações dos Tenants (`database/migrations/tenant/`)

Executadas automaticamente quando um tenant é criado via `TenantProvisioner`:

1. `create_users_table` - Usuários do tenant
2. `create_doctors_table` - Médicos
3. `create_medical_specialties_table` - Especialidades
4. `create_doctor_specialty_table` - Relação muitos-para-muitos entre médicos e especialidades
5. `create_patients_table` - Pacientes
6. `create_calendars_and_business_hours_tables` - Calendários e horários comerciais
7. `create_appointment_types_table` - Tipos de consulta
8. `create_appointments_table` - Agendamentos
9. `create_forms_tables` - Formulários, seções, perguntas e opções
10. `create_form_responses_tables` - Respostas de formulários e respostas individuais
11. `create_integrations_tables` - Integrações, contas OAuth e estado de sincronização
12. `create_recurring_appointments_table` - Agendamentos recorrentes
13. `create_recurring_appointment_rules_table` - Regras de recorrência
14. `create_user_doctor_permissions_table` - Permissões de médicos para usuários
15. `create_patient_logins_table` - Credenciais de login dos pacientes
16. `create_notifications_table` - Notificações do tenant
17. `create_tenant_settings_table` - Configurações específicas do tenant
18. `create_google_calendar_tokens_table` - Tokens OAuth do Google Calendar
19. `add_role_to_users_table` - Campo `role` em usuários
20. `add_avatar_to_users_table` - Campo `avatar` em usuários
21. `add_role_to_users_table` - Campo `role` em usuários (admin, doctor, user)
22. `add_recurring_appointment_id_to_appointments_table` - Relacionamento com agendamentos recorrentes
23. `add_appointment_mode_to_appointments` - Campo `appointment_mode` (presencial/online) em agendamentos e recorrências
24. `create_online_appointment_instructions_table` - Tabela de instruções para consultas online
25. `add_default_appointment_mode_setting` - Configuração padrão de modo de atendimento
26. `add_customization_fields_to_doctors_table` - Campos de personalização do médico (labels, signature, registration)
27. `add_apple_calendar_fields_to_appointments_table` - Campo `apple_event_id` em agendamentos
28. `create_apple_calendar_tokens_table` - Tabela de tokens CalDAV do Apple Calendar

**Nota sobre campos do Google Calendar:**
- O campo `google_event_id` já está incluído na migração `create_appointments_table`
- O campo `google_recurring_event_ids` já está incluído na migração `create_recurring_appointments_table`

**Nota sobre campos do Apple Calendar:**
- O campo `apple_event_id` foi adicionado via migração `add_apple_calendar_fields_to_appointments_table`
- A tabela `apple_calendar_tokens` foi criada para armazenar credenciais CalDAV do Apple Calendar

**Nota sobre agendamentos online:**
- O campo `appointment_mode` foi adicionado via migração `add_appointment_mode_to_appointments`
- A tabela `online_appointment_instructions` foi criada para armazenar instruções de consultas online
- A configuração `appointments.default_appointment_mode` é criada automaticamente via migração

**Nota sobre sistema de roles:**
- O campo `role` foi adicionado via migração `add_role_to_users_table`
- Valores possíveis: `admin`, `doctor`, `user`
- O sistema aplica filtros automáticos baseados no role em todas as listagens
- Controllers usam o trait `HasDoctorFilter` para aplicar filtros automaticamente

**Nota sobre personalização de médicos:**
- Os campos de personalização (`label_singular`, `label_plural`, `signature`, `registration_label`, `registration_value`) foram adicionados via migração `add_customization_fields_to_doctors_table`
- Permitem adaptar a terminologia e campos do sistema para diferentes tipos de profissionais de saúde

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
5. **Sistema de Roles**: Controle de acesso baseado em papéis (admin, doctor, user) com filtros automáticos
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
- [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md) - Guia completo de criação de formulários
- [docs/GUIA_TESTE_PUBLICO.md](docs/GUIA_TESTE_PUBLICO.md) - Guia de teste da área pública
- [docs/INSTRUCOES_MIGRATION.md](docs/INSTRUCOES_MIGRATION.md) - Instruções para migrações manuais

---

**Última atualização:** 2025-12-03

**Nota:** Esta documentação foi completamente revisada e atualizada para refletir todas as funcionalidades atuais, incluindo:
- Portal do Paciente completo
- Integração Google Calendar com sincronização automática
- Integração Apple Calendar (iCloud) com protocolo CalDAV (seção completa adicionada)
- Agendamentos recorrentes
- Permissões de médicos para usuários
- Sistema de notificações
- **Agendamentos Online** com instruções e envio de links de reunião
- **Módulo de Consultas Online** (`online_appointments`)
- **Atendimento Médico** (sessão de atendimento do dia)
- **Relatórios completos** (agendamentos, pacientes, médicos, formulários, etc.)
- **Configuração de modo de atendimento** (presencial/online/escolha do usuário)
- **Sistema de Roles** (admin, doctor, user) com controle de acesso baseado em papéis
- **Filtros Automáticos** baseados em roles aplicados em todas as listagens
- **Campos de Personalização** no Doctor (labels, signature, registration)
- **Configurações de Profissionais** (rótulos globais personalizados)
- Lista completa e atualizada de módulos disponíveis
- Rotas completas de relatórios com exportação (Excel, PDF, CSV)
- Rotas de integração Apple Calendar documentadas
- Rotas de configurações do médico (doctor-settings) documentadas
- Model AppleCalendarToken documentado
- Controller AppleCalendarController documentado

---

## 📝 Correções e Atualizações

### Módulos de Acesso
- Corrigida lista de módulos para corresponder ao código (`Module.php`)
- Removidos módulos que não existem no código (`responses`, `settings`, `notifications`, `appointment-types`, `business-hours`)
- Ajustados nomes dos módulos para corresponder às chaves reais (`calendar`, `business_hours`)

### Controllers
- Adicionado `ProfileController` na tabela de controllers do tenant

### Rotas do Portal do Paciente
- Corrigida URL de acesso: `/t/{tenant}/paciente/login` (não `/t/{tenant}/portal/login`)
- Corrigida URL após login: `/paciente/dashboard` (não `/t/{tenant}/portal/dashboard`)

### Migrações
- Adicionadas migrações faltantes: `add_role_to_users_table`, `add_avatar_to_users_table`
- Nota sobre campos do Google Calendar já incluídos nas migrações principais

### Gerenciamento de Login do Paciente
- Atualizada seção com todas as rotas disponíveis para gerenciar login do paciente

### Agendamentos Online (Nova Funcionalidade)
- Adicionado suporte completo para agendamentos online
- Novo controller `OnlineAppointmentController` para gerenciar consultas virtuais
- Novo model `OnlineAppointmentInstruction` para armazenar instruções de consultas online
- Campo `appointment_mode` adicionado em `Appointments` e `RecurringAppointments`
- Configuração `appointments.default_appointment_mode` para definir comportamento padrão
- Rotas adicionadas para gerenciar agendamentos online
- Envio de instruções por email e WhatsApp para pacientes
- Novo módulo `online_appointments` adicionado ao sistema de permissões
- Migrações adicionadas para suportar a funcionalidade

### Formulários Públicos
- Adicionado controller `PublicFormController` para formulários públicos
- Pacientes podem responder formulários sem precisar estar logados
- Suporte para vincular respostas a agendamentos específicos

### Atendimento Médico (Nova Funcionalidade)
- Adicionado módulo completo de Atendimento Médico para sessões de atendimento do dia
- Novo controller `MedicalAppointmentController` para gerenciar sessões de atendimento
- Visualização de agendamentos do dia com filtros baseados em roles
- Gerenciamento de status de atendimento (agendado, chegou, em atendimento, concluído, cancelado)
- Integração com formulários para visualizar respostas durante o atendimento
- Navegação automática entre agendamentos após conclusão
- Novo módulo `medical_appointments` adicionado ao sistema de permissões
- Rotas adicionadas para acessar e gerenciar atendimentos do dia

### Relatórios (Nova Funcionalidade)
- Adicionado módulo completo de Relatórios
- Novos controllers de relatórios: `Reports/ReportController`, `Reports/AppointmentReportController`, `Reports/PatientReportController`, `Reports/DoctorReportController`, `Reports/RecurringReportController`, `Reports/FormReportController`, `Reports/PortalReportController`, `Reports/NotificationReportController`
- Relatórios disponíveis: Agendamentos, Pacientes, Médicos, Recorrências, Formulários, Portal do Paciente, Notificações
- Exportação em múltiplos formatos: Excel, PDF, CSV
- Filtros avançados em todos os relatórios
- Novo módulo `reports` adicionado ao sistema de permissões
- Rotas adicionadas para acessar e exportar relatórios

### Configurações de Profissionais
- Adicionada configuração de rótulos globais para profissionais
- Personalização de labels (singular, plural) e label de registro
- Configuração disponível em `/tenant/settings/professionals`
- Permite adaptar terminologia do sistema para diferentes tipos de clínicas

