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
- `notifications` - Notificações do tenant

O middleware `module.access:{modulo}` verifica o acesso antes de permitir a rota.

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
/tenant/notifications                # Notificações do tenant
/tenant/settings                     # Configurações do tenant
/tenant/settings/general             # Atualizar configurações gerais
/tenant/settings/appointments        # Atualizar configurações de agendamentos
/tenant/settings/calendar            # Atualizar configurações de calendário
/tenant/settings/notifications       # Atualizar configurações de notificações
/tenant/settings/integrations        # Atualizar configurações de integrações
/tenant/agendamentos/recorrentes     # Agendamentos recorrentes

# APIs para agendamentos
/tenant/api/doctors/{doctorId}/calendars
/tenant/api/doctors/{doctorId}/appointment-types
/tenant/api/doctors/{doctorId}/specialties
/tenant/api/doctors/{doctorId}/available-slots

# APIs para agendamentos recorrentes
/tenant/api/doctors/{doctorId}/business-hours
/tenant/api/doctors/{doctorId}/available-slots-recurring

# Rotas especiais
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
/tenant/forms/{form_id}/responses/create # Criar resposta de formulário
/tenant/forms/{form_id}/responses     # Salvar resposta de formulário
/tenant/responses/{id}/answer         # Adicionar resposta individual
/tenant/answers/{id}                   # Atualizar resposta individual
/tenant/integrations/google            # Lista médicos e status de integração Google
/tenant/integrations/google/{doctor}/connect # Conectar conta Google do médico
/tenant/integrations/google/{doctor}/disconnect # Desconectar conta Google
/tenant/integrations/google/{doctor}/status # Status da integração (JSON)
/tenant/integrations/google/api/{doctor}/events # Eventos do Google Calendar (JSON)
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
POST /paciente/agendamentos                  # Processar criação
GET  /paciente/agendamentos/{id}/editar       # Editar agendamento
PUT  /paciente/agendamentos/{id}              # Atualizar agendamento
POST /paciente/agendamentos/{id}/cancelar    # Cancelar agendamento
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
| `CalendarSyncStateController` | Estado de sincronização de calendário | `/tenant/calendar-sync` |
| `SettingsController` | Configurações do tenant | `/tenant/settings` |
| `RecurringAppointmentController` | Agendamentos recorrentes | `/tenant/agendamentos/recorrentes` |
| `UserDoctorPermissionController` | Permissões de médicos para usuários | `/tenant/users/{id}/doctor-permissions` |
| `NotificationController` | Notificações do tenant | `/tenant/notifications` |
| `PublicPatientController` | Identificação de paciente (área pública) | `/t/{tenant}/agendamento/identificar` |
| `PublicPatientRegisterController` | Cadastro de paciente (área pública) | `/t/{tenant}/agendamento/cadastro` |
| `PublicAppointmentController` | Criação de agendamento (área pública) | `/t/{tenant}/agendamento/criar` |
| `PatientPortal/AuthController` | Autenticação do portal do paciente | `/t/{tenant}/paciente/login` |
| `PatientPortal/DashboardController` | Dashboard do portal do paciente | `/paciente/dashboard` |
| `PatientPortal/AppointmentController` | Agendamentos do portal do paciente | `/paciente/agendamentos` |
| `PatientPortal/NotificationController` | Notificações do portal do paciente | `/paciente/notificacoes` |
| `PatientPortal/ProfileController` | Perfil do portal do paciente | `/paciente/perfil` |

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
- `Doctor` possui relacionamento com `GoogleCalendarToken` para integração com Google Calendar

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

**Ver Guia Completo:** [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md)

### 7. Respostas de Formulários

**Visualizar Respostas:**
1. Acesse `/tenant/responses`
2. Visualize todas as respostas coletadas
3. Filtre por formulário, paciente, data, etc.
4. Clique em "Ver" para visualizar resposta completa

### 8. Agendamentos Recorrentes

**Criar Agendamento Recorrente:**
1. Acesse `/tenant/agendamentos/recorrentes`
2. Clique em "Criar Agendamento Recorrente"
3. Preencha:
   - Paciente
   - Médico
   - Tipo de consulta
   - Data de início
   - Tipo de término (data final ou número de sessões)
   - Regras de recorrência (diária, semanal, mensal, etc.)
4. O sistema gerará automaticamente os agendamentos conforme as regras

**Gerenciar Agendamentos Recorrentes:**
- Visualize todos os agendamentos recorrentes ativos
- Edite regras de recorrência
- Cancele agendamentos recorrentes
- Visualize agendamentos gerados a partir da recorrência

### 9. Permissões de Médicos para Usuários

**Gerenciar Permissões:**
1. Acesse `/tenant/users/{id}/doctor-permissions`
2. Selecione quais médicos o usuário pode gerenciar
3. Salve as permissões
4. O usuário terá acesso apenas aos médicos permitidos

### 10. Integrações

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

### 11. Notificações do Tenant

**Visualizar Notificações:**
1. Acesse `/tenant/notifications`
2. Visualize todas as notificações do sistema
3. Marque como lidas individualmente ou todas de uma vez
4. Filtre por tipo ou status

**API de Notificações:**
- `GET /tenant/notifications/json` - Retorna notificações em JSON
- `POST /tenant/notifications/{id}/read` - Marcar notificação como lida
- `POST /tenant/notifications/mark-all-read` - Marcar todas como lidas

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
- `last_login_at` - Data do último login
- `is_active` - Status ativo/inativo

**Gerenciar Login do Paciente:**
1. Acesse `/tenant/patients/{id}/login`
2. Crie credenciais de login para o paciente
3. Envie credenciais por email ou WhatsApp
4. Ative/desative o acesso do paciente
5. Remova credenciais se necessário

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
5. O paciente poderá acessar o portal em `/t/{tenant}/paciente/login`

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
19. `add_google_event_id_to_appointments_table` - Campo `google_event_id` em agendamentos
20. `add_google_recurring_event_id_to_recurring_appointments_table` - Campo `google_recurring_event_ids` em agendamentos recorrentes
21. `add_recurring_appointment_id_to_appointments_table` - Relacionamento com agendamentos recorrentes

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
- [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md) - Guia completo de criação de formulários
- [docs/GUIA_TESTE_PUBLICO.md](docs/GUIA_TESTE_PUBLICO.md) - Guia de teste da área pública
- [docs/INSTRUCOES_MIGRATION.md](docs/INSTRUCOES_MIGRATION.md) - Instruções para migrações manuais

---

**Última atualização:** 2025-01-27

