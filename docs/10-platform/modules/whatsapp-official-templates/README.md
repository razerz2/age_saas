# Modulo: Templates WhatsApp Oficial

Gestao de templates oficiais do WhatsApp Business (Meta Cloud API).

Inclui suporte a exemplos obrigatorios de variaveis (`sample_variables`) exigidos pela Meta ao criar/submeter `message_templates`.
Inclui teste manual do envio de templates oficiais diretamente na tela Show, respeitando o schema remoto aprovado salvo em `meta_response`.

Funcionalidades principais:

- criacao e edicao de templates (restrita a drafts);
- submissao para a Meta e sincronizacao de status;
- versionamento seguro (nao edita template aprovado diretamente);
- teste manual de envio pela UI (Show) via endpoint `test-send`.

Importante: este modulo e exclusivo da Platform e nao deve ser usado para templates operacionais de clinica (`appointment.*`, `waitlist.*`).

Eventos SaaS seedados no baseline atual:

- `invoice.created`
- `invoice.upcoming_due`
- `invoice.overdue`
- `tenant.suspended_due_to_overdue`
- `security.2fa_code`
- `tenant.welcome`
- `subscription.created`
- `subscription.recovery_started`
- `credentials.resent`

Arquivos: README.md, overview.md, routes.md, views.md, backend.md, frontend.md, database.md, permissions.md, troubleshooting.md
