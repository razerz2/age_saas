# Overview

Modulo da Platform para manter templates de email do escopo **Platform** (SaaS).

Separacao de dominio:

- **Platform Email Templates**: eventos SaaS (ex.: `invoice.*`, `subscription.*`, `tenant.*`, `security.*`).
- **Tenant Email Templates**: baseline email para eventos clinicos (ver `docs/10-platform/modules/tenant-email-templates/`).
- **WhatsApp Oficial**: templates aprovados na Meta (ver `docs/10-platform/modules/whatsapp-official-templates/`).
- **WhatsApp Nao Oficial**: catalogos internos e baseline (ver `docs/10-platform/modules/whatsapp-unofficial-templates/` e `docs/10-platform/modules/tenant-default-notification-templates/`).

Catalogo controlado (intencional):

- cada `key` representa um evento;
- nao existe criacao manual via UI;
- o baseline e gerado via seeder e nao duplica registros.

Placeholders:

- formato: `{{placeholder}}` (suporta dot notation quando aplicavel, ex.: `{{patient.name}}`);
- no teste de envio, placeholders sao preenchidos com valores dummy/fallback quando ausentes.

Teste de envio (UI):

- acao `Testar envio` na tela Show;
- modal com `Email de destino`;
- usa o `subject` + `body` atuais do registro e faz fallback dummy para placeholders nao resolvidos;
- nao aplica layout estrutural (layout e um modulo separado).

Fluxo de uso correto:

1. editar o template (ajustar `subject`/`body`, mantendo a `key` alinhada ao evento);
2. usar `Testar envio` para validar renderizacao (placeholders) e entrega;
3. para WhatsApp:
   - usar **Oficial** quando houver integracao com Meta e necessidade de governanca por template aprovado;
   - usar **Nao Oficial** para envio de texto via providers nao oficiais (WAHA/Z-API) com catalogos internos/baseline.

