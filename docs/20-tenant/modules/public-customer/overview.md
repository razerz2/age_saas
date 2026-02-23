# Public Customer — Overview

O módulo **Public Customer** agrupa as rotas públicas sob `/customer/{slug}` usadas para identificação/cadastro de pacientes, criação de agendamentos e resposta de formulários.

- Permite que pacientes se identifiquem, cadastrem e criem agendamentos sem autenticação tradicional.
- Exponde APIs públicas para consumo em frontends de agendamento.
- Permite responder formulários públicos associados ao Tenant.

> Informações extraídas de `routes/tenant.php` e dos controllers públicos (`PublicPatientController`, `PublicPatientRegisterController`, `PublicAppointmentController`, `PublicFormController`).
