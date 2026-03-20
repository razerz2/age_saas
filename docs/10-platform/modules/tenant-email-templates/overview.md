# Overview

Modulo da Platform para manter templates de email do escopo **Tenant** (baseline clinico).

Separacao Platform vs Tenant:

- **Platform Email Templates**: eventos SaaS/Platform (ver `docs/10-platform/modules/platform-email-templates/`).
- **Tenant Email Templates**: eventos clinicos/Tenant (este modulo).

Catalogo controlado (intencional):

- cada `key` representa um evento clinico (ex.: `appointment.*`, `waitlist.*`);
- nao existe criacao manual via UI;
- o baseline e gerado via seeder e nao duplica registros.

Origem do baseline:

- as keys ativas sao derivadas de `tenant_default_notification_templates` (canal `whatsapp`) para manter consistencia de eventos;
- a geracao ocorre no seeder `NotificationTemplatesSeeder`.

Teste de envio (UI):

- acao `Testar envio` na tela Show;
- modal com `Email de destino`;
- usa o `subject` + `body` atuais do registro e faz fallback dummy para placeholders nao resolvidos;
- nao aplica layout estrutural (layout e um modulo separado).

