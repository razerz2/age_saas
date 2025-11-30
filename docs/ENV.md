# 🔧 Guia de Variáveis de Ambiente

Este documento lista todas as variáveis de ambiente necessárias para o funcionamento do sistema.

## 📋 Índice

- [Aplicação](#-aplicação)
- [Banco de Dados](#-banco-de-dados)
- [Integrações](#-integrações)
- [Email](#-email)
- [Multitenancy](#-multitenancy)
- [Cache e Sessão](#-cache-e-sessão)
- [Queue](#-queue)

## 🚀 Aplicação

```env
APP_NAME="Agendamento SaaS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost
APP_TIMEZONE=America/Sao_Paulo
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `APP_NAME` | Nome da aplicação | Não | Laravel |
| `APP_ENV` | Ambiente (local, staging, production) | Sim | production |
| `APP_KEY` | Chave de criptografia | Sim | - |
| `APP_DEBUG` | Modo debug | Não | false |
| `APP_URL` | URL base da aplicação | Sim | http://localhost |
| `APP_TIMEZONE` | Fuso horário | Não | UTC |

## 🗄️ Banco de Dados

### Banco Central (Landlord)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agendamento_landlord
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `DB_CONNECTION` | Tipo de conexão (pgsql, mysql, sqlite) | Sim | mysql |
| `DB_HOST` | Host do banco de dados | Sim | 127.0.0.1 |
| `DB_PORT` | Porta do banco de dados | Sim | 3306 (MySQL) / 5432 (PostgreSQL) |
| `DB_DATABASE` | Nome do banco de dados | Sim | - |
| `DB_USERNAME` | Usuário do banco | Sim | - |
| `DB_PASSWORD` | Senha do banco | Sim | - |

### Banco dos Tenants

```env
DB_TENANT_HOST=127.0.0.1
DB_TENANT_PORT=5432
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `DB_TENANT_HOST` | Host para bancos dos tenants | Sim | 127.0.0.1 |
| `DB_TENANT_PORT` | Porta para bancos dos tenants | Sim | 5432 |

**Nota**: O nome do banco, usuário e senha de cada tenant são gerados automaticamente durante a criação do tenant.

## 🔌 Integrações

### Asaas (Gateway de Pagamento)

```env
ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
ASAAS_API_KEY=sua_chave_api
ASAAS_WEBHOOK_SECRET=seu_secret_webhook
ASAAS_ENV=sandbox
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `ASAAS_API_URL` | URL da API do Asaas | Sim | https://sandbox.asaas.com/api/v3/ |
| `ASAAS_API_KEY` | Chave de API do Asaas | Sim | - |
| `ASAAS_WEBHOOK_SECRET` | Secret para validar webhooks | Sim | - |
| `ASAAS_ENV` | Ambiente (sandbox, production) | Não | sandbox |

**Como obter:**
1. Acesse [Asaas](https://www.asaas.com/)
2. Crie uma conta
3. Acesse Configurações → API
4. Copie a chave de API
5. Configure o webhook apontando para: `https://seu-dominio.com/webhook/asaas`

### WhatsApp (Meta)

```env
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_TOKEN=seu_token
WHATSAPP_PHONE_ID=seu_phone_id
META_ACCESS_TOKEN=seu_token_meta
META_PHONE_NUMBER_ID=seu_phone_number_id
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `WHATSAPP_API_URL` | URL da API do WhatsApp | Não | https://graph.facebook.com/v18.0 |
| `WHATSAPP_TOKEN` | Token de acesso | Sim | - |
| `WHATSAPP_PHONE_ID` | ID do número de telefone | Sim | - |
| `META_ACCESS_TOKEN` | Token de acesso Meta | Sim | - |
| `META_PHONE_NUMBER_ID` | ID do número de telefone Meta | Sim | - |

**Como obter:**
1. Acesse [Meta for Developers](https://developers.facebook.com/)
2. Crie um app do tipo "Business"
3. Configure WhatsApp Business API
4. Obtenha o token de acesso e phone number ID

### Google Calendar

```env
GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret
```

| Variável | Descrição | Obrigatório | Padrão |
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
   - Exemplo produção: `https://seudominio.com/google/callback`
6. Copie o Client ID e Client Secret para o arquivo `.env`

**Nota:** O sistema usa automaticamente a rota `route('google.callback')` que resolve para `/google/callback` baseado no `APP_URL`. Certifique-se de que a URI configurada no Google Cloud Console corresponda exatamente à URL completa (incluindo domínio e porta). A URI deve ser **sem barra final** e **sem parâmetros**.

## 📧 Email

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

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `MAIL_MAILER` | Driver de email (smtp, mailgun, ses, postmark) | Sim | smtp |
| `MAIL_HOST` | Host do servidor SMTP | Sim | - |
| `MAIL_PORT` | Porta do servidor SMTP | Sim | 587 |
| `MAIL_USERNAME` | Usuário SMTP | Sim | - |
| `MAIL_PASSWORD` | Senha SMTP | Sim | - |
| `MAIL_ENCRYPTION` | Criptografia (tls, ssl) | Não | tls |
| `MAIL_FROM_ADDRESS` | Email remetente | Sim | - |
| `MAIL_FROM_NAME` | Nome do remetente | Não | ${APP_NAME} |

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

## 🏢 Multitenancy

```env
APP_DOMAIN=app.exemplo.com
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `APP_DOMAIN` | Domínio central da plataforma | Não | app.agepro.com |

**Nota**: Esta variável define o domínio que será usado para a área administrativa (Platform). Tenants acessam via `/t/{subdomain}/login`.

## 💾 Cache e Sessão

```env
CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `CACHE_DRIVER` | Driver de cache (file, redis, memcached) | Não | file |
| `SESSION_DRIVER` | Driver de sessão (file, redis, database) | Não | file |
| `SESSION_LIFETIME` | Tempo de vida da sessão (minutos) | Não | 120 |

### Redis (Opcional)

```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📬 Queue

```env
QUEUE_CONNECTION=sync
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `QUEUE_CONNECTION` | Driver de fila (sync, database, redis, sqs) | Não | sync |

**Para produção**, recomenda-se usar `database` ou `redis`:

```env
QUEUE_CONNECTION=database
```

## 🔐 Autenticação

```env
BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
```

| Variável | Descrição | Obrigatório | Padrão |
|----------|-----------|-------------|--------|
| `BROADCAST_DRIVER` | Driver de broadcast (log, pusher, redis) | Não | log |
| `FILESYSTEM_DISK` | Disco padrão para arquivos (local, s3) | Não | local |

### AWS S3 (Opcional)

```env
AWS_ACCESS_KEY_ID=sua_chave
AWS_SECRET_ACCESS_KEY=sua_chave_secreta
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=seu_bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## 📝 Exemplo Completo de .env

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

ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
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

## ⚠️ Segurança

1. **Nunca commite o arquivo `.env`** no repositório
2. Use valores diferentes para desenvolvimento e produção
3. Mantenha as chaves de API seguras
4. Use senhas fortes para o banco de dados
5. Em produção, defina `APP_DEBUG=false`

## 🔄 Configurações Dinâmicas

Algumas configurações podem ser alteradas via interface administrativa (Platform → Settings), sendo armazenadas na tabela `system_settings`:

- `timezone`
- `country_id`
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

Essas configurações têm prioridade sobre as variáveis de ambiente quando definidas.

---

**Última atualização:** 2025-01-27

**Nota:** Esta documentação foi revisada e atualizada para incluir:
- Variáveis de ambiente do Google Calendar (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET)





