# Modulo: Templates de Email Platform

Catalogo de templates de email do escopo **Platform** (eventos SaaS), com edicao e teste de envio.

Regras (catalogo controlado):

- nao e permitido criar templates manualmente (sem rotas `create/store` e policy `create=false`);
- templates sao populados via seeder (idempotente);
- apenas edicao e permitida (conteudo do template).

Conceitos:

- template = `subject` + `body`;
- layout e separado (ver modulo `docs/10-platform/modules/notification-templates/`).

Origem do baseline (seeding):

- fonte: `whatsapp_unofficial_templates` (Platform)
- seeder: `database/seeders/NotificationTemplatesSeeder.php`
- execucao: `php artisan db:seed`

Arquivos: README.md, overview.md, routes.md, views.md, backend.md, frontend.md, database.md, permissions.md, troubleshooting.md

