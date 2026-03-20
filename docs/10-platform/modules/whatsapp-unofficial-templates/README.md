# Modulo: WhatsApp Unofficial Templates

Modulo da Platform para templates internos de mensagens WhatsApp nao oficial.

Escopo:

- templates internos nao oficiais da Platform;
- sem aprovacao/cadastro na Meta;
- usado como catalogo para envio interno via providers nao oficiais.
- catalogo controlado: nao existe criacao manual via UI (sem rotas `create/store` e policy `create=false`);
- baseline populado via seeder (idempotente); apenas edicao/ativacao e permitida.

Baseline inicial (confirmado por eventos reais da Platform):

- `invoice.created`
- `invoice.upcoming_due`
- `invoice.overdue`
- `tenant.suspended_due_to_overdue`
- `tenant.welcome`
- `subscription.created`
- `subscription.recovery_started`
- `credentials.resent`
- `security.2fa_code`

Resolucao (dominio nao oficial):

- tenant primeiro (`notification_templates`);
- fallback Platform (`whatsapp_unofficial_templates`) somente quando explicitamente solicitado.

Runtime integrado:

- caminho central conectado em `NotificationDispatcher` (resolucao + renderizacao);
- envio final em `WhatsAppSender` e traducao por provider em `WhatsAppService`;
- providers nao oficiais suportados nesta etapa: WAHA e Z-API.

Teste manual e preview:

- disponivel na tela `show` do template interno (`Testar mensagem`);
- preview renderizado com a mesma engine de runtime (`TemplateRenderer`);
- envio manual agnostico a provider (WAHA/Z-API), com validacao de variaveis obrigatorias e telefone;
- suporte a preenchimento com dados ficticios por variavel.

Limites atuais:

- campanhas e hardcodes legados fora do caminho central ainda nao foram migrados nesta etapa.

Arquivos: README.md, overview.md, routes.md, views.md, backend.md, frontend.md, database.md, permissions.md, troubleshooting.md
