# Database

Tabela:

- `whatsapp_official_templates`

Campos principais:

- `id` (uuid)
- `key`
- `meta_template_name`
- `provider` (fixo `whatsapp_business`)
- `category`
- `language`
- `header_text`, `body_text`, `footer_text`
- `buttons` (json), `variables` (json)
- `sample_variables` (json): exemplos obrigatorios das variaveis do `body_text` (placeholders `{{n}}`)
- `version`
- `status` (`draft`, `pending`, `approved`, `rejected`, `archived`)
- `meta_template_id`, `meta_waba_id`
- `meta_response` (json)
- `last_synced_at`
- `created_by`, `updated_by`
- timestamps

Constraints/indices:

- unique: `provider + key + version`
- unique: `provider + meta_template_name + language + version`
- indices para `provider/status`, `provider/key/status`, `meta_template_name`, `last_synced_at`

Seeder inicial:

- `Database\Seeders\WhatsAppOfficialTemplatesSeeder`
- Baseline SaaS da Platform:
  - `invoice.created`
  - `invoice.upcoming_due`
  - `invoice.overdue`
  - `tenant.suspended_due_to_overdue`
  - `security.2fa_code`
  - `tenant.welcome`
  - `subscription.created`
  - `subscription.recovery_started`
  - `credentials.resent`
- O seeder nao cadastra `appointment.*` ou `waitlist.*`.
- Templates clinicos permanecem no dominio Tenant (`config/notification_templates.php` + tabela tenant `notification_templates`).
