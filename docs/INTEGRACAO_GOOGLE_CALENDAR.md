# Integração Google Calendar

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Requisitos e Configuração](#requisitos-e-configuração)
3. [Instruções de Uso](#instruções-de-uso)
4. [Funcionalidades](#funcionalidades)
5. [Estrutura Técnica](#estrutura-técnica)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

A integração com Google Calendar permite sincronizar automaticamente os agendamentos do sistema com o calendário do Google de cada médico. Quando um agendamento é criado, editado ou cancelado no sistema, o evento correspondente é automaticamente sincronizado no Google Calendar do médico.

### Características Principais

- ✅ **Sincronização Automática**: Agendamentos são sincronizados automaticamente em tempo real
- ✅ **Por Médico**: Cada médico pode conectar sua própria conta do Google Calendar
- ✅ **Suporte a Recorrências**: Agendamentos recorrentes são criados como eventos recorrentes no Google Calendar
- ✅ **Renovação Automática de Tokens**: Tokens de acesso são renovados automaticamente quando expiram
- ✅ **Integração com Observers**: Sincronização automática através de Laravel Observers

---

## ⚙️ Requisitos e Configuração

### 1. Configuração no Google Cloud Console

#### Passo 1: Criar Projeto no Google Cloud

1. Acesse o [Google Cloud Console](https://console.cloud.google.com/)
2. Crie um novo projeto ou selecione um existente
3. Ative a **Google Calendar API** para o projeto

#### Passo 2: Configurar OAuth 2.0

1. Vá em **APIs & Services** > **Credentials**
2. Clique em **Create Credentials** > **OAuth client ID**
3. Se necessário, configure a tela de consentimento OAuth
4. Escolha o tipo de aplicação: **Web application**
5. Configure as URLs de redirecionamento autorizadas:
   ```
   https://seudominio.com/google/callback
   ```
   > **Importante**: Esta URL deve ser exatamente igual à configurada no sistema. Não inclua o subdomínio do tenant.

#### Passo 3: Obter Credenciais

1. Copie o **Client ID** e **Client Secret**
2. Adicione no arquivo `.env` do sistema:

```env
GOOGLE_CLIENT_ID=seu_client_id_aqui
GOOGLE_CLIENT_SECRET=seu_client_secret_aqui
```

### 2. Configuração no Sistema

#### Passo 1: Verificar Configuração

As credenciais são lidas do arquivo `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
],
```

#### Passo 2: Configurar APP_URL

Certifique-se de que a variável `APP_URL` no `.env` está correta:

```env
APP_URL=https://seudominio.com
```

> **Importante**: Esta URL é usada para gerar o callback do OAuth. Se estiver usando ngrok ou outro túnel, atualize esta variável.

#### Passo 3: Cadastrar Integração (Opcional)

Para habilitar a sincronização automática globalmente, cadastre a integração na tabela `integrations`:

```sql
INSERT INTO integrations (id, key, is_enabled, config, created_at, updated_at)
VALUES (
    gen_random_uuid(),
    'google_calendar',
    true,
    '{"client_id": "seu_client_id", "client_secret": "seu_client_secret"}'::jsonb,
    NOW(),
    NOW()
);
```

> **Nota**: Esta configuração é opcional. A sincronização funciona mesmo sem este registro, desde que as credenciais estejam no `.env`.

---

## 📖 Instruções de Uso

### Para Administradores

#### Habilitar Sincronização Global

1. Acesse **Configurações** > **Integrações**
2. Localize a seção **Google Calendar**
3. Ative o switch **"Habilitar Sincronização com Google Calendar"**
4. (Opcional) Ative **"Sincronização Automática"** para sincronização em tempo real

### Para Médicos

#### Conectar Conta do Google Calendar

1. Acesse **Integrações** > **Google Calendar**
2. Localize seu nome na lista de médicos
3. Clique em **"Conectar"**
4. Você será redirecionado para o Google para autorizar o acesso
5. Faça login na sua conta do Google
6. Autorize o acesso ao Google Calendar
7. Você será redirecionado de volta ao sistema com a mensagem de sucesso

#### Verificar Status da Conexão

1. Acesse **Integrações** > **Google Calendar**
2. O status da conexão será exibido:
   - ✅ **Conectado**: Integração ativa
   - ⚠️ **Expirado**: Token expirado (será renovado automaticamente)
   - ❌ **Não Conectado**: Nenhuma integração configurada

#### Desconectar Conta

1. Acesse **Integrações** > **Google Calendar**
2. Clique em **"Desconectar"** ao lado do seu nome
3. Confirme a ação
4. Os eventos já criados no Google Calendar **não serão removidos automaticamente**

---

## 🚀 Funcionalidades

### 1. Sincronização Automática de Agendamentos

#### Criação de Agendamento

Quando um agendamento é criado:

- ✅ Um evento é criado automaticamente no Google Calendar do médico
- ✅ O evento contém informações completas do agendamento:
  - Nome do paciente
  - Especialidade e tipo de consulta
  - Data e horário
  - Informações de contato do paciente
  - Observações
  - ID do agendamento (para rastreamento)

#### Edição de Agendamento

Quando um agendamento é editado:

- ✅ O evento no Google Calendar é atualizado automaticamente
- ✅ **Estratégia**: O sistema deleta o evento antigo e cria um novo (mais confiável)
- ✅ Mudanças em horário, paciente, notas, etc. são refletidas no Google Calendar

#### Cancelamento de Agendamento

Quando um agendamento é cancelado:

- ✅ O evento é removido automaticamente do Google Calendar
- ✅ O status do agendamento é atualizado para "canceled"

#### Exclusão de Agendamento

Quando um agendamento é excluído:

- ✅ O evento é removido automaticamente do Google Calendar

### 2. Suporte a Agendamentos Recorrentes

#### Criação de Recorrência

Quando uma recorrência é criada:

- ✅ Um evento recorrente é criado no Google Calendar usando RRULE
- ✅ Suporta múltiplas regras (ex: segunda e quarta-feira)
- ✅ Cada regra gera um evento recorrente separado
- ✅ Para recorrências sem data fim, usa data fim padrão de 1 ano (renovável)

#### Edição de Recorrência

Quando uma recorrência é editada:

- ✅ Os eventos recorrentes são atualizados no Google Calendar
- ✅ **Estratégia**: Deleta eventos antigos e cria novos

#### Cancelamento de Recorrência

Quando uma recorrência é cancelada:

- ✅ Os eventos recorrentes são atualizados para terminar hoje
- ✅ Eventos passados são mantidos como histórico
- ✅ Eventos futuros são removidos

#### Exclusão de Recorrência

Quando uma recorrência é excluída:

- ✅ Todos os eventos recorrentes são removidos do Google Calendar

### 3. Renovação Automática de Tokens

- ✅ Tokens de acesso são renovados automaticamente quando expiram
- ✅ Usa o `refresh_token` para obter novo `access_token`
- ✅ Transparente para o usuário (não precisa reconectar)

### 4. Listagem de Eventos (API)

O sistema fornece uma API para listar eventos do Google Calendar:

```
GET /workspace/{slug}/integrations/google/api/{doctor}/events?start=2025-01-01&end=2025-01-31
```

**Resposta:**
```json
[
  {
    "id": "event_id_123",
    "title": "João Silva - Cardiologia - Consulta",
    "start": "2025-01-15T10:00:00-03:00",
    "end": "2025-01-15T11:00:00-03:00",
    "description": "Detalhes do evento..."
  }
]
```

### 5. Proteção contra Loops Infinitos

- ✅ O sistema usa `withoutEvents()` para evitar loops infinitos
- ✅ Mudanças apenas em `google_event_id` não disparam nova sincronização
- ✅ Agendamentos de recorrência não são sincronizados individualmente

---

## 🔧 Estrutura Técnica

### Arquivos Principais

#### Controllers

- **`app/Http/Controllers/Tenant/Integrations/GoogleCalendarController.php`**
  - Gerencia conexão/desconexão
  - Callback OAuth
  - API de eventos

#### Services

- **`app/Services/Tenant/GoogleCalendarService.php`**
  - Lógica de sincronização
  - Criação/atualização/exclusão de eventos
  - Suporte a recorrências
  - Renovação de tokens

#### Models

- **`app/Models/Tenant/GoogleCalendarToken.php`**
  - Armazena tokens OAuth por médico
  - Relacionamento com `Doctor`
  - Métodos para verificar expiração

#### Observers

- **`app/Observers/AppointmentObserver.php`**
  - Dispara sincronização automática
  - Escuta eventos: `created`, `updated`, `deleted`

### Estrutura de Dados

#### Tabela: `google_calendar_tokens`

```sql
CREATE TABLE google_calendar_tokens (
    id UUID PRIMARY KEY,
    doctor_id UUID NOT NULL UNIQUE,
    access_token JSONB NOT NULL,
    refresh_token TEXT,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);
```

#### Campo: `appointments.google_event_id`

Armazena o ID do evento criado no Google Calendar para rastreamento.

#### Campo: `recurring_appointments.google_recurring_event_ids`

Armazena JSON com os IDs dos eventos recorrentes criados no Google Calendar:

```json
{
  "rule_id_1": "google_event_id_1",
  "rule_id_2": "google_event_id_2"
}
```

### Fluxo de Sincronização

```
1. Agendamento Criado/Editado/Deletado
   ↓
2. AppointmentObserver detecta mudança
   ↓
3. GoogleCalendarService.syncEvent()
   ↓
4. Verifica se médico tem token Google
   ↓
5. Se sim, sincroniza com Google Calendar
   ↓
6. Salva google_event_id no agendamento
```

### Rotas

#### Rotas do Tenant

```php
// Listar integrações
GET /workspace/{slug}/integrations/google

// Conectar
GET /workspace/{slug}/integrations/google/{doctor}/connect

// Desconectar
DELETE /workspace/{slug}/integrations/google/{doctor}/disconnect

// Status
GET /workspace/{slug}/integrations/google/{doctor}/status

// API: Eventos
GET /workspace/{slug}/integrations/google/api/{doctor}/events
```

#### Rota Global (OAuth Callback)

```php
// Callback OAuth (não usa prefixo /tenant)
GET /google/callback
```

---

## 🐛 Troubleshooting

### Problema: Erro "Credenciais do Google não configuradas"

**Causa**: Variáveis `GOOGLE_CLIENT_ID` ou `GOOGLE_CLIENT_SECRET` não estão no `.env`.

**Solução**:
1. Verifique se as variáveis estão no arquivo `.env`
2. Execute `php artisan config:clear` para limpar cache
3. Reinicie o servidor

### Problema: Erro "redirect_uri_mismatch"

**Causa**: A URL de callback configurada no Google Cloud Console não corresponde à gerada pelo sistema.

**Solução**:
1. Verifique a variável `APP_URL` no `.env`
2. A URL de callback gerada é: `{APP_URL}/google/callback`
3. Certifique-se de que esta URL está cadastrada no Google Cloud Console
4. Exemplo: Se `APP_URL=https://meusite.com`, cadastre `https://meusite.com/google/callback`

### Problema: Token expirado não é renovado

**Causa**: O `refresh_token` não foi salvo ou foi revogado.

**Solução**:
1. Desconecte e reconecte a integração
2. Certifique-se de que o OAuth está configurado com `access_type=offline` e `prompt=consent`

### Problema: Eventos duplicados no Google Calendar

**Causa**: Múltiplas chamadas de sincronização ou evento já existente.

**Solução**:
- O sistema já trata isso automaticamente:
  - Verifica se `google_event_id` existe antes de criar
  - Deleta evento antigo antes de criar novo na edição

### Problema: Sincronização não funciona

**Verificações**:
1. ✅ Médico tem token Google configurado?
2. ✅ Token não está expirado? (renovação automática)
3. ✅ Integração está habilitada nas configurações?
4. ✅ Verifique os logs: `storage/logs/laravel.log`

### Problema: Recorrências sem data fim criam eventos infinitos

**Solução**:
- O sistema já trata isso:
  - Recorrências sem data fim usam data fim padrão de 1 ano
  - Podem ser renovadas manualmente ou automaticamente

### Logs e Debug

Para debugar problemas, verifique os logs:

```bash
tail -f storage/logs/laravel.log | grep -i "google"
```

O sistema registra:
- ✅ Tentativas de conexão
- ✅ Sincronizações bem-sucedidas
- ❌ Erros de sincronização
- 🔄 Renovações de token

---

## 📝 Notas Importantes

1. **URL de Callback**: A URL de callback é global (não inclui subdomínio do tenant). O sistema usa o parâmetro `state` do OAuth para identificar o tenant e médico.

2. **Sincronização Automática**: A sincronização automática é controlada por Laravel Observers. Não é necessário chamar manualmente os métodos de sincronização.

3. **Agendamentos de Recorrência**: Agendamentos individuais gerados por recorrências **não são sincronizados individualmente**. Apenas a recorrência em si é sincronizada como evento recorrente.

4. **Segurança**: Tokens são armazenados criptografados no banco de dados. Apenas o médico pode acessar sua própria integração.

5. **Performance**: A sincronização é assíncrona quando possível. Erros não bloqueiam a criação/edição de agendamentos.

---

## 🔗 Referências

- [Google Calendar API Documentation](https://developers.google.com/calendar/api)
- [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Laravel Observers](https://laravel.com/docs/eloquent#observers)

