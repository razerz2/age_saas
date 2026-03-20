# Database

- `subscriptions` (platform)
  - FK: `tenant_id` -> `tenants`
  - FK: `plan_id` -> `plans`
  - Status: `pending`, `active`, `past_due`, `canceled`, `trialing`, `recovery_pending`
  - Vigencia: `starts_at` / `ends_at`
  - Trial comercial: `is_trial` (bool), `trial_ends_at` (datetime)

- `plan_change_requests` (platform)
  - Solicita mudanca de plano para um tenant (quando aplicavel)
