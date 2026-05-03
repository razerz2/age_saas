# ðŸ”§ Guia de VariÃ¡veis de Ambiente

Este documento lista todas as variÃ¡veis de ambiente necessÃ¡rias para o funcionamento do sistema.

## ðŸ“‹ Ãndice

- [AplicaÃ§Ã£o](#-aplicaÃ§Ã£o)
- [Banco de Dados](#-banco-de-dados)
- [IntegraÃ§Ãµes](#-integraÃ§Ãµes)
- [Email](#-email)
- [Multitenancy](#-multitenancy)
- [Cache e SessÃ£o](#-cache-e-sessÃ£o)
- [Queue](#-queue)

## ðŸš€ AplicaÃ§Ã£o

```env
APP_NAME="Agendamento SaaS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/Sao_Paulo
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `APP_NAME` | Nome da aplicaÃ§Ã£o | NÃ£o | Laravel |
| `APP_ENV` | Ambiente (local, staging, production) | Sim | production |
| `APP_KEY` | Chave de criptografia | Sim | - |
| `APP_DEBUG` | Modo debug | NÃ£o | false |
| `APP_URL` | URL base da aplicaÃ§Ã£o | Sim | http://localhost |
| `APP_TIMEZONE` | Fuso horÃ¡rio | NÃ£o | UTC |

## ðŸ—„ï¸ Banco de Dados

### Banco Central (Landlord)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agendamento_landlord
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `DB_CONNECTION` | Tipo de conexÃ£o (pgsql, mysql, sqlite) | Sim | mysql |
| `DB_HOST` | Host do banco de dados | Sim | 127.0.0.1 |
| `DB_PORT` | Porta do banco de dados | Sim | 3306 (MySQL) / 5432 (PostgreSQL) |
| `DB_DATABASE` | Nome do banco de dados | Sim | - |
| `DB_USERNAME` | UsuÃ¡rio do banco | Sim | - |
| `DB_PASSWORD` | Senha do banco | Sim | - |

### Banco dos Tenants

```env
DB_TENANT_HOST=127.0.0.1
DB_TENANT_PORT=5432
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `DB_TENANT_HOST` | Host para bancos dos tenants | Sim | 127.0.0.1 |
| `DB_TENANT_PORT` | Porta para bancos dos tenants | Sim | 5432 |

**Nota**: O nome do banco, usuÃ¡rio e senha de cada tenant sÃ£o gerados automaticamente durante a criaÃ§Ã£o do tenant.

## ðŸ”Œ IntegraÃ§Ãµes

### Asaas (Gateway de Pagamento)

```env
ASAAS_API_URL=https://api-sandbox.asaas.com/v3
ASAAS_API_KEY=sua_chave_api
ASAAS_WEBHOOK_SECRET=seu_secret_webhook
ASAAS_ENV=sandbox
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `ASAAS_API_URL` | URL da API do Asaas | Sim | https://api-sandbox.asaas.com/v3 |
| `ASAAS_API_KEY` | Chave de API do Asaas | Sim | - |
| `ASAAS_WEBHOOK_SECRET` | Secret para validar webhooks | Sim | - |
| `ASAAS_ENV` | Ambiente (sandbox, production) | NÃ£o | sandbox |

**Como obter:**
1. Acesse [Asaas](https://www.asaas.com/)
2. Crie uma conta
3. Acesse ConfiguraÃ§Ãµes â†’ API
4. Copie a chave de API
5. Configure o webhook conforme o contexto:
   - **Platform**: `https://seu-dominio.com/webhook/asaas`
   - **Finance (Tenant)**: `https://seu-dominio.com/t/{slug}/webhooks/asaas`

### WhatsApp (Meta / Z-API)

```env
# Escolha o provedor: 'whatsapp_business' (Meta) ou 'zapi'
WHATSAPP_PROVIDER=whatsapp_business

# OpÃ§Ã£o 1: WhatsApp Business API (Meta)
WHATSAPP_BUSINESS_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_BUSINESS_TOKEN=seu_token_meta
WHATSAPP_BUSINESS_PHONE_ID=seu_phone_id_meta

# ConfiguraÃ§Ãµes legadas (mantidas para compatibilidade)
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_TOKEN=seu_token_legacy
WHATSAPP_PHONE_ID=seu_phone_id_legacy

# OpÃ§Ã£o 2: Z-API
ZAPI_API_URL=https://api.z-api.io
ZAPI_TOKEN=seu_token_zapi
ZAPI_INSTANCE_ID=seu_instance_id
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `WHATSAPP_PROVIDER` | Provedor (whatsapp_business, zapi) | Sim | whatsapp_business |
| `WHATSAPP_BUSINESS_API_URL` | Base URL da API Meta | NÃ£o | https://graph.facebook.com/v18.0 |
| `WHATSAPP_BUSINESS_TOKEN` | Token de acesso (Meta) | Sim (se Meta) | - |
| `WHATSAPP_BUSINESS_PHONE_ID` | Phone Number ID (Meta) | Sim (se Meta) | - |
| `ZAPI_API_URL` | Base URL da API Z-API | NÃ£o | https://api.z-api.io |
| `ZAPI_TOKEN` | Token (Z-API) | Sim (se Z-API) | - |
| `ZAPI_INSTANCE_ID` | Instance ID (Z-API) | Sim (se Z-API) | - |
| `WHATSAPP_API_URL` | Legado: base URL Meta | NÃ£o | https://graph.facebook.com/v18.0 |
| `WHATSAPP_TOKEN` | Legado: token Meta | NÃ£o | - |
| `WHATSAPP_PHONE_ID` | Legado: phone id Meta | NÃ£o | - |

**Como obter:**
1. Acesse [Meta for Developers](https://developers.facebook.com/)
2. Crie um app do tipo "Business"
3. Configure WhatsApp Business API
4. Obtenha o token de acesso e phone number ID
5. Alternativamente, para Z-API, obtenha `ZAPI_TOKEN` e `ZAPI_INSTANCE_ID` no painel do provedor

### Google Calendar

```env
GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `GOOGLE_CLIENT_ID` | Client ID do Google OAuth 2.0 | Sim | - |
| `GOOGLE_CLIENT_SECRET` | Client Secret do Google OAuth 2.0 | Sim | - |

**Como obter:**
1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um projeto ou selecione um existente
3. Ative a API do Google Calendar
4. Crie credenciais OAuth 2.0 (tipo: Aplicativo Web)
5. Configure a URI de redirecionamento como: `{APP_URL}/google/callback`
   - Exemplo local: `http://localhost:8000/google/callback`
   - Exemplo produÃ§Ã£o: `https://seudominio.com/google/callback`
6. Copie o Client ID e Client Secret para o arquivo `.env`

**Nota:** O sistema usa automaticamente a rota `route('google.callback')` que resolve para `/google/callback` baseado no `APP_URL`. Certifique-se de que a URI configurada no Google Cloud Console corresponda exatamente Ã  URL completa (incluindo domÃ­nio e porta). A URI deve ser **sem barra final** e **sem parÃ¢metros**.

## ðŸ“§ Email

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@exemplo.com
MAIL_FROM_NAME="${APP_NAME}"
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `MAIL_MAILER` | Driver de email (smtp, mailgun, ses, postmark) | Sim | smtp |
| `MAIL_HOST` | Host do servidor SMTP | Sim | - |
| `MAIL_PORT` | Porta do servidor SMTP | Sim | 587 |
| `MAIL_USERNAME` | UsuÃ¡rio SMTP | Sim | - |
| `MAIL_PASSWORD` | Senha SMTP | Sim | - |
| `MAIL_ENCRYPTION` | Criptografia (tls, ssl) | NÃ£o | tls |
| `MAIL_FROM_ADDRESS` | Email remetente | Sim | - |
| `MAIL_FROM_NAME` | Nome do remetente | NÃ£o | ${APP_NAME} |

### Mailgun (Opcional)

```env
MAILGUN_DOMAIN=seu_dominio.mailgun.org
MAILGUN_SECRET=sua_chave_secreta
MAILGUN_ENDPOINT=api.mailgun.net
```

### AWS SES (Opcional)

```env
AWS_ACCESS_KEY_ID=sua_chave
AWS_SECRET_ACCESS_KEY=sua_chave_secreta
AWS_DEFAULT_REGION=us-east-1
```

## ðŸ¢ Multitenancy

```env
APP_DOMAIN=app.exemplo.com
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `APP_DOMAIN` | DomÃ­nio central da plataforma | NÃ£o | app.agepro.com |

**Nota**: Esta variÃ¡vel define o domÃ­nio que serÃ¡ usado para a Ã¡rea administrativa (Platform). Tenants acessam via `/t/{subdomain}/login`.

## ðŸ’¾ Cache e SessÃ£o

```env
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `CACHE_DRIVER` | Driver de cache (file, redis, memcached) | NÃ£o | file |
| `SESSION_DRIVER` | Driver de sessÃ£o (file, redis, database) | NÃ£o | file |
| `SESSION_LIFETIME` | Tempo de vida da sessÃ£o (minutos) | NÃ£o | 120 |

### Redis (Opcional)

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## ðŸ“¬ Queue

```env
QUEUE_CONNECTION=sync
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `QUEUE_CONNECTION` | Driver de fila (sync, database, redis, sqs) | NÃ£o | sync |

**Para produÃ§Ã£o**, recomenda-se usar `database` ou `redis`:

```env
QUEUE_CONNECTION=database
```

## ðŸ” AutenticaÃ§Ã£o

```env
BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
```

| VariÃ¡vel | DescriÃ§Ã£o | ObrigatÃ³rio | PadrÃ£o |
|----------|-----------|-------------|--------|
| `BROADCAST_DRIVER` | Driver de broadcast (log, pusher, redis) | NÃ£o | log |
| `FILESYSTEM_DISK` | Disco padrÃ£o para arquivos (local, s3) | NÃ£o | local |

### AWS S3 (Opcional)

```env
AWS_ACCESS_KEY_ID=sua_chave
AWS_SECRET_ACCESS_KEY=sua_chave_secreta
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=seu_bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## ðŸ“ Exemplo Completo de .env

```env
APP_NAME="Agendamento SaaS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/Sao_Paulo

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agendamento_landlord
DB_USERNAME=postgres
DB_PASSWORD=sua_senha

DB_TENANT_HOST=127.0.0.1
DB_TENANT_PORT=5432

ASAAS_API_URL=https://api-sandbox.asaas.com/v3
ASAAS_API_KEY=sua_chave_api
ASAAS_WEBHOOK_SECRET=seu_secret_webhook
ASAAS_ENV=sandbox

WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_TOKEN=seu_token
WHATSAPP_PHONE_ID=seu_phone_id
META_ACCESS_TOKEN=seu_token_meta
META_PHONE_NUMBER_ID=seu_phone_number_id

GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@exemplo.com
MAIL_FROM_NAME="${APP_NAME}"

APP_DOMAIN=app.exemplo.com

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120

QUEUE_CONNECTION=sync

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
```

## âš ï¸ SeguranÃ§a

1. **Nunca commite o arquivo `.env`** no repositÃ³rio
2. Use valores diferentes para desenvolvimento e produÃ§Ã£o
3. Mantenha as chaves de API seguras
4. Use senhas fortes para o banco de dados
5. Em produÃ§Ã£o, defina `APP_DEBUG=false`

## ðŸ”„ ConfiguraÃ§Ãµes DinÃ¢micas

Algumas configuraÃ§Ãµes podem ser alteradas via interface administrativa (Platform â†’ Settings), sendo armazenadas na tabela `system_settings`:

- `timezone`
- `country_id` (legado tecnico, fixo em Brasil)
- `language`
- `ASAAS_API_URL`
- `ASAAS_API_KEY`
- `ASAAS_ENV`
- `META_ACCESS_TOKEN`
- `META_PHONE_NUMBER_ID`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

Essas configuraÃ§Ãµes tÃªm prioridade sobre as variÃ¡veis de ambiente quando definidas.

---

**Ãšltima atualizaÃ§Ã£o:** 2025-12-03

**Nota:** Esta documentaÃ§Ã£o foi revisada e atualizada para incluir:
- VariÃ¡veis de ambiente do Google Calendar (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET)
- Todas as variÃ¡veis necessÃ¡rias para o funcionamento completo do sistema





