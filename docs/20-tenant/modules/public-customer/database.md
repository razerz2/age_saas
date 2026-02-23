# Public Customer — Database

## Models

- `App/Models/Tenant/Patient.php`
- `App/Models/Tenant/Appointment.php`
- `App/Models/Tenant/Form.php`
- `App/Models/Tenant/FormResponse.php`

(Models inferidos a partir das rotas e responsabilidade dos controllers públicos; recomenda-se abrir os controllers para confirmar exatamente quais são usados.)

## Tabelas / Migrations

- Tabelas principais envolvidas:
  - `patients`
  - `appointments`
  - `forms`
  - `form_responses`

- Migrations específicas da área pública:
  - (não identificado no código — provável em `database/migrations/tenant/*patients*`, `*appointments*`, `*forms*`, `*form_responses*`).

## Relações relevantes

- Pacientes podem ser criados/atualizados via fluxo público antes de possuir login.
- Agendamentos criados via `/customer/{slug}/agendamento/*` são armazenados na mesma tabela de `appointments` do Tenant.
- Formulários públicos reutilizam o mesmo modelo de `Form` / `FormResponse` usado na área autenticada.
