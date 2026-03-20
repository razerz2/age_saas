# Backend

- SystemSettingsController
- Model: SystemSetting

## Comandos agendados relevantes

- `subscriptions:notify-trial-reminders`
  - envia lembretes de fim de trial comercial (7 dias, 3 dias, hoje e expirado)
  - horario padrao: `09:00`
  - configuravel na aba de comandos agendados da Platform (`commands.<command>.enabled/time`)
