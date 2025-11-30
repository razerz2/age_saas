# Limpeza e Consolidação de Migrations - Tenant

## ✅ Migrations Incorporadas nas Originais

### 1. **doctor_id obrigatório em appointment_types**
- **Migration removida**: `2025_11_30_010051_make_doctor_id_required_in_appointment_types_table.php`
- **Incorporada em**: `2025_09_28_155007_create_appointment_types_table.php`
- **Alteração**: `doctor_id` agora é obrigatório (não nullable) desde a criação da tabela

### 2. **google_recurring_event_ids em recurring_appointments**
- **Migration removida**: `2025_01_27_000001_add_google_recurring_event_id_to_recurring_appointments_table.php`
- **Incorporada em**: `2025_12_01_000001_create_recurring_appointments_table.php`
- **Alteração**: Coluna `google_recurring_event_ids` adicionada diretamente na criação da tabela

### 3. **google_event_id em appointments**
- **Migration removida**: `2025_11_29_053939_add_google_event_id_to_appointments_table.php`
- **Incorporada em**: `2025_09_28_155008_create_appointments_table.php`
- **Alteração**: Coluna `google_event_id` adicionada diretamente na criação da tabela

## ⚠️ Migrations Mantidas (não podem ser incorporadas)

### 1. **recurring_appointment_id em appointments**
- **Migration mantida**: `2025_12_01_000003_add_recurring_appointment_id_to_appointments_table.php`
- **Motivo**: A tabela `recurring_appointments` é criada DEPOIS da tabela `appointments`
  - `appointments` é criada em: `2025_09_28_155008`
  - `recurring_appointments` é criada em: `2025_12_01_000001`
- **Razão**: Não é possível criar uma foreign key para uma tabela que ainda não existe

## 📋 Estado Final das Migrations

### Migrations de Criação (CREATE)
- ✅ `2025_01_01_000001_create_notifications_table.php`
- ✅ `2025_01_01_000002_create_tenant_settings_table.php`
- ✅ `2025_09_28_155001_create_users_table.php`
- ✅ `2025_09_28_155002_create_doctors_table.php`
- ✅ `2025_09_28_155003_create_medical_specialties_table.php`
- ✅ `2025_09_28_155004_create_doctor_specialty_table.php`
- ✅ `2025_09_28_155005_create_patients_table.php`
- ✅ `2025_09_28_155006_create_calendars_and_business_hours_tables.php`
- ✅ `2025_09_28_155007_create_appointment_types_table.php` (doctor_id obrigatório incorporado)
- ✅ `2025_09_28_155008_create_appointments_table.php` (google_event_id incorporado)
- ✅ `2025_09_28_155009_create_forms_tables.php`
- ✅ `2025_09_28_155010_create_form_responses_tables.php`
- ✅ `2025_09_28_155011_create_integrations_tables.php`
- ✅ `2025_09_28_155012_create_user_doctor_permissions_table.php`
- ✅ `2025_11_28_215928_create_patient_logins_table.php`
- ✅ `2025_11_29_053817_create_google_calendar_tokens_table.php`
- ✅ `2025_12_01_000001_create_recurring_appointments_table.php` (google_recurring_event_ids incorporado)
- ✅ `2025_12_01_000002_create_recurring_appointment_rules_table.php`

### Migrations de Alteração (ALTER) - Mantidas
- ⚠️ `2025_12_01_000003_add_recurring_appointment_id_to_appointments_table.php` (necessária pela ordem de criação)

## 📝 Resumo

**Total de migrations removidas**: 3
**Total de migrations mantidas**: 19 (18 CREATE + 1 ALTER)

Todas as alterações que podiam ser incorporadas nas migrations originais foram consolidadas, mantendo apenas a migration de alteração que é necessária pela ordem de criação das tabelas.

