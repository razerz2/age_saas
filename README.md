# 🏥 Sistema de Agendamento SaaS

Sistema SaaS (Software as a Service) de agendamento médico construído com Laravel 10, utilizando arquitetura multitenancy com banco de dados separado por tenant. Cada clínica possui seu próprio banco de dados PostgreSQL isolado, garantindo total separação de dados.

## 📋 Índice

- [Características](#-características)
- [Tecnologias](#-tecnologias)
- [Requisitos](#-requisitos)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Uso](#-uso)
- [Arquitetura Multitenant](#-arquitetura-multitenant)
- [Integrações](#-integrações)
- [Desenvolvimento](#-desenvolvimento)
- [Documentação Adicional](#-documentação-adicional)

## ✨ Características

### Área Platform (Administrativa)
- ✅ Gerenciamento de tenants (clínicas)
- ✅ Gestão de planos de assinatura
- ✅ Controle de assinaturas e renovações
- ✅ Gerenciamento de faturas
- ✅ Sistema de notificações
- ✅ Catálogo de especialidades médicas
- ✅ Gestão de usuários administrativos
- ✅ Configurações do sistema
- ✅ Integração com gateway de pagamento (Asaas)
- ✅ Envio de mensagens WhatsApp
- ✅ Monitor de kiosk

### Área Tenant (Clínicas)
- ✅ Dashboard com estatísticas
- ✅ Gerenciamento de usuários
- ✅ Cadastro de médicos e especialidades
- ✅ Cadastro de pacientes
- ✅ Calendários de agendamento
- ✅ Horários comerciais
- ✅ Tipos de consulta
- ✅ Agendamentos
- ✅ Formulários personalizados
- ✅ Respostas de formulários
- ✅ Formulários públicos (pacientes respondem sem login)
- ✅ Envio automático de links de formulários por email/WhatsApp
- ✅ Integrações (Google Calendar, etc.)
- ✅ Sincronização de calendário
- ✅ Notificações configuráveis (email e WhatsApp por tenant)

## 🛠 Tecnologias

- **Backend**: PHP 8.1+, Laravel 10
- **Banco de Dados**: PostgreSQL
- **Multitenancy**: Spatie Laravel Multitenancy 3.2
- **Autenticação**: Laravel Breeze + Laravel Sanctum
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js
- **Build Tools**: Vite
- **Testes**: Pest PHP
- **Integrações**: 
  - Asaas (Gateway de pagamento)
  - WhatsApp Business API (Meta)

## 📦 Requisitos

- PHP >= 8.1
- Composer
- Node.js >= 18 e npm
- PostgreSQL >= 12
- Extensões PHP:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - PDO_PGSQL
  - Tokenizer
  - XML

## 🚀 Instalação

### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd agendamento-saas
```

### 2. Instale as dependências PHP

```bash
composer install
```

### 3. Instale as dependências Node.js

```bash
npm install
```

### 4. Configure o ambiente

Copie o arquivo `.env.example` para `.env` (se existir) ou crie um novo:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

### 5. Configure o banco de dados

Edite o arquivo `.env` e configure as variáveis de banco de dados (veja seção [Configuração](#-configuração)).

### 6. Execute as migrações

```bash
php artisan migrate
```

### 7. Execute os seeders (opcional)

```bash
php artisan db:seed
```

### 8. Compile os assets

Para desenvolvimento:

```bash
npm run dev
```

Para produção:

```bash
npm run build
```

### 9. Inicie o servidor

```bash
php artisan serve
```

## ⚙️ Configuração

### Variáveis de Ambiente

Crie ou edite o arquivo `.env` com as seguintes configurações:

#### Aplicação

```env
APP_NAME="Agendamento SaaS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost
```

#### Banco de Dados (Landlord - Central)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=agendamento_landlord
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

#### Banco de Dados (Tenants)

```env
DB_TENANT_HOST=127.0.0.1
DB_TENANT_PORT=5432
```

**Nota**: O nome do banco, usuário e senha de cada tenant são gerados automaticamente durante a criação do tenant.

#### Integração Asaas (Gateway de Pagamento)

```env
ASAAS_API_URL=https://sandbox.asaas.com/api/v3/
ASAAS_API_KEY=sua_chave_api
ASAAS_WEBHOOK_SECRET=seu_secret_webhook
ASAAS_ENV=sandbox
```

#### Integração WhatsApp (Meta)

```env
WHATSAPP_API_URL=https://graph.facebook.com/v18.0
WHATSAPP_TOKEN=seu_token
WHATSAPP_PHONE_ID=seu_phone_id
META_ACCESS_TOKEN=seu_token_meta
META_PHONE_NUMBER_ID=seu_phone_number_id
```

#### Email

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

#### Multitenancy

```env
APP_DOMAIN=app.exemplo.com
```

### Configuração de Webhook Asaas

Para receber notificações do Asaas, configure o webhook apontando para:

```
POST https://seu-dominio.com/webhook/asaas
```

O webhook valida o token usando o middleware `verify.asaas.token`. Gere um token seguro:

```bash
php artisan asaas:webhook-token
```

## 📁 Estrutura do Projeto

```
agendamento-saas/
├── app/
│   ├── Console/Commands/          # Comandos Artisan
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Platform/          # Controllers da área administrativa
│   │   │   ├── Tenant/            # Controllers da área do tenant
│   │   │   └── Webhook/           # Controllers de webhooks
│   │   ├── Middleware/            # Middlewares customizados
│   │   └── Requests/              # Form Requests (validação)
│   ├── Models/
│   │   ├── Platform/              # Models do banco central
│   │   └── Tenant/                # Models do banco do tenant
│   ├── Services/                  # Serviços de negócio
│   │   ├── AsaasService.php       # Integração Asaas
│   │   ├── TenantProvisioner.php  # Criação/remoção de tenants
│   │   └── WhatsAppService.php    # Integração WhatsApp
│   └── TenantFinder/               # Identificação de tenant
├── config/
│   ├── multitenancy.php           # Configuração multitenancy
│   └── database.php               # Conexões de banco
├── database/
│   ├── migrations/                # Migrações do banco central
│   │   └── tenant/                # Migrações dos tenants
│   └── seeders/                   # Seeders
├── routes/
│   ├── web.php                    # Rotas da Platform
│   ├── tenant.php                 # Rotas dos Tenants
│   └── api.php                    # Rotas da API
└── resources/views/                # Views Blade
```

Para mais detalhes sobre a arquitetura, consulte:
- [PLATFORM.md](PLATFORM.md) - Documentação da área Platform
- [TENANT.md](TENANT.md) - Documentação da área Tenant
- [ARQUITETURA.md](ARQUITETURA.md) - Documentação técnica da arquitetura

## 🎯 Uso

### Acessando a Platform

1. Acesse: `http://localhost/Platform/dashboard`
2. Faça login com um usuário administrativo
3. Gerencie tenants, planos, assinaturas e faturas

### Criando um Tenant

1. Na área Platform, acesse **Tenants** → **Criar**
2. Preencha os dados da clínica
3. O sistema criará automaticamente:
   - Banco de dados PostgreSQL
   - Usuário do banco
   - Estrutura de tabelas (migrations)
   - Usuário admin padrão

### Acessando um Tenant

1. Acesse: `http://localhost/t/{subdomain}/login`
2. Faça login com as credenciais do tenant
3. Após o login, você será redirecionado para `/tenant/dashboard`

### Primeiro Acesso

Após criar um tenant, use as credenciais padrão:
- **Email**: admin@{subdomain}
- **Senha**: Verifique o seeder `TenantAdminSeeder`

## 🏢 Arquitetura Multitenant

O sistema utiliza **multitenancy com banco de dados separado** (database-per-tenant):

- **Banco Central (Landlord)**: PostgreSQL com dados da plataforma
- **Bancos dos Tenants**: Cada tenant possui seu próprio banco PostgreSQL isolado

### Fluxo de Detecção do Tenant

1. Request chega em `/t/{tenant}/login`
2. `PathTenantFinder` detecta o tenant pelo path
3. `SwitchTenantTask` configura a conexão dinâmica
4. Middleware persiste o tenant na sessão
5. Request continua com tenant ativo

### Autenticação Dual

- **Guard `web`**: Usuários da platform (`App\Models\Platform\User`)
- **Guard `tenant`**: Usuários dos tenants (`App\Models\Tenant\User`)

Para mais detalhes técnicos, consulte:
- [PLATFORM.md](PLATFORM.md) - Documentação da área Platform
- [TENANT.md](TENANT.md) - Documentação da área Tenant
- [ARQUITETURA.md](ARQUITETURA.md) - Documentação técnica da arquitetura

## 🔌 Integrações

### Asaas (Gateway de Pagamento)

O sistema integra com o Asaas para:
- Criação de clientes
- Gerenciamento de assinaturas
- Geração de faturas
- Recebimento de webhooks de pagamento

### WhatsApp Business API

Integração com Meta para envio de:
- Notificações de agendamento
- Lembretes
- Notificações de faturas

### Google Calendar

Sincronização automática de agendamentos com Google Calendar por médico.

**Configuração:**

1. Configure as credenciais OAuth no Google Cloud Console
2. Adicione no arquivo `.env`:
   ```
   GOOGLE_CLIENT_ID=seu_client_id
   GOOGLE_CLIENT_SECRET=seu_client_secret
   ```
3. Configure a URI de redirecionamento no Google Cloud Console como: `{APP_URL}/google/callback`
   - Exemplo local: `http://localhost:8000/google/callback`
   - Exemplo produção: `https://seudominio.com/google/callback`
4. Cada médico pode conectar sua própria conta Google Calendar
5. Os agendamentos são sincronizados automaticamente

Para mais detalhes, consulte a documentação em [TENANT.md](TENANT.md#10-integrações).

## 💻 Desenvolvimento

### Comandos Úteis

```bash
# Executar testes
php artisan test

# Formatar código
./vendor/bin/pest --testdox

# Limpar cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Executar migrations
php artisan migrate

# Criar um tenant manualmente (via tinker)
php artisan tinker
>>> $tenant = App\Models\Platform\Tenant::create([...]);
>>> App\Services\TenantProvisioner::createDatabase($tenant);
```

### Estrutura de Testes

Os testes estão em `tests/` usando Pest PHP:

```bash
php artisan test
```

### Code Style

O projeto utiliza Laravel Pint para formatação:

```bash
./vendor/bin/pint
```

## 📚 Documentação Adicional

### Documentação por Área

- [PLATFORM.md](PLATFORM.md) - Documentação completa da área Platform (Administrativa)
- [TENANT.md](TENANT.md) - Documentação completa da área Tenant (Clínicas)

### Documentação Técnica

- [ARQUITETURA.md](ARQUITETURA.md) - Documentação técnica detalhada da arquitetura
- [docs/ENV.md](docs/ENV.md) - Guia completo de variáveis de ambiente

### Guias Específicos

- [docs/GUIA_CRIAR_FORMULARIO.md](docs/GUIA_CRIAR_FORMULARIO.md) - Guia passo a passo para criar formulários
- [docs/GUIA_TESTE_PUBLICO.md](docs/GUIA_TESTE_PUBLICO.md) - Guia de teste da área pública de agendamento
- [docs/INSTRUCOES_MIGRATION.md](docs/INSTRUCOES_MIGRATION.md) - Instruções para migrações manuais

### Documentação Externa

- [Laravel Documentation](https://laravel.com/docs/10.x)
- [Spatie Multitenancy](https://spatie.be/docs/laravel-multitenancy)

## 🔐 Segurança

- Isolamento de dados por tenant (banco separado)
- Autenticação separada para platform e tenant
- Validação de tenant em cada request
- Controle de acesso por módulos
- Webhook seguro com validação de token

## 📝 Licença

Este projeto é proprietário. Todos os direitos reservados.

## 👥 Contribuindo

1. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
2. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
3. Push para a branch (`git push origin feature/nova-feature`)
4. Abra um Pull Request

## 🐛 Suporte

Para suporte, entre em contato com a equipe de desenvolvimento.

---

**Desenvolvido com ❤️ usando Laravel**
