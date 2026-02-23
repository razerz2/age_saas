# Calendars — Overview

O módulo **Calendars** gerencia as agendas dos médicos no Tenant.

- Cada médico pode ter **apenas um calendário**.
- Calendários são usados pelo módulo de agendamentos (`appointments`) e pelo módulo de atendimentos médicos.
- O acesso às agendas é restrito por **role** e associação do usuário ao médico.

> Todas as informações abaixo são extraídas de `CalendarController` e `routes/tenant.php`.
