# Integração Apple Calendar (iCloud)

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Requisitos e Configuração](#requisitos-e-configuração)
3. [Instruções de Uso](#instruções-de-uso)
4. [Funcionalidades](#funcionalidades)
5. [Estrutura Técnica](#estrutura-técnica)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

A integração com Apple Calendar (iCloud) permite sincronizar automaticamente os agendamentos do sistema com o calendário iCloud de cada médico usando o protocolo CalDAV. Quando um agendamento é criado, editado ou cancelado no sistema, o evento correspondente é automaticamente sincronizado no Apple Calendar do médico.

### Características Principais

- ✅ **Sincronização Automática**: Agendamentos são sincronizados automaticamente em tempo real
- ✅ **Por Médico**: Cada médico pode conectar sua própria conta do iCloud
- ✅ **Protocolo CalDAV**: Usa o protocolo padrão CalDAV para comunicação com iCloud
- ✅ **Formato iCal**: Eventos são criados no formato iCalendar (.ics)
- ✅ **Integração com Observers**: Sincronização automática através de Laravel Observers

---

## ⚙️ Requisitos e Configuração

### 1. Requisitos do Sistema

#### Dependências PHP

A integração usa a biblioteca **SabreDAV** para comunicação CalDAV:

```bash
composer require sabre/dav
```

> **Nota**: Esta dependência já deve estar instalada no projeto.

#### Extensões PHP Necessárias

- ✅ `curl` (para requisições HTTP)
- ✅ `xml` (para processamento de XML CalDAV)
- ✅ `openssl` (para conexões HTTPS)

### 2. Configuração no iCloud

#### Passo 1: Obter Credenciais do iCloud

Para conectar com o iCloud, você precisa:

1. **E-mail do iCloud**: Seu endereço de e-mail do iCloud (ex: `usuario@icloud.com`)
2. **Senha do iCloud**: Sua senha do iCloud OU uma **Senha de App Específica**

> **Recomendação**: Use uma **Senha de App Específica** para maior segurança.

#### Passo 2: Criar Senha de App Específica (Recomendado)

1. Acesse [appleid.apple.com](https://appleid.apple.com)
2. Faça login com sua conta Apple
3. Vá em **Segurança** > **Senhas de App**
4. Clique em **Gerar Senha de App**
5. Dê um nome (ex: "Sistema de Agendamento")
6. Copie a senha gerada (ela só aparece uma vez)

> **Importante**: Use esta senha ao invés da senha normal do iCloud.

#### Passo 3: URL do Servidor CalDAV

A URL padrão do servidor CalDAV do iCloud é:

```
https://caldav.icloud.com
```

> **Nota**: Esta URL é configurada automaticamente pelo sistema, mas pode ser personalizada.

### 3. Configuração no Sistema

#### Passo 1: Executar Migrations

Execute as migrations necessárias para criar as tabelas:

```bash
php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084550_add_apple_calendar_fields_to_appointments_table.php
php artisan migrate --database=tenant --path=database/migrations/tenant/2025_12_03_084556_create_apple_calendar_tokens_table.php
```

**OU** execute o script SQL diretamente:

```bash
psql -d nome_do_banco_tenant < database/migrations/tenant/apple_calendar_migration.sql
```

#### Passo 2: Verificar Tabelas

Certifique-se de que as seguintes tabelas foram criadas:

- ✅ `apple_calendar_tokens` (tokens de conexão)
- ✅ Campo `apple_event_id` na tabela `appointments`

---

## 📖 Instruções de Uso

### Para Administradores

#### Verificar Instalação

1. Acesse **Integrações** > **Apple Calendar**
2. Se a tabela não existir, você verá uma mensagem de aviso
3. Execute as migrations conforme instruções acima

### Para Médicos

#### Conectar Conta do Apple Calendar (iCloud)

1. Acesse **Integrações** > **Apple Calendar**
2. Localize seu nome na lista de médicos
3. Clique em **"Conectar"**
4. Preencha o formulário:
   - **E-mail**: Seu endereço de e-mail do iCloud
   - **Senha**: Sua senha do iCloud OU Senha de App Específica
   - **URL do Servidor**: (Opcional) Deixe em branco para usar o padrão `https://caldav.icloud.com`
   - **URL do Calendário**: (Opcional) Deixe em branco para descobrir automaticamente
5. Clique em **"Conectar"**
6. O sistema tentará descobrir os calendários disponíveis automaticamente
7. Se bem-sucedido, você verá a mensagem de sucesso

#### Verificar Status da Conexão

1. Acesse **Integrações** > **Apple Calendar**
2. O status da conexão será exibido:
   - ✅ **Conectado**: Integração ativa (mostra data da última conexão)
   - ❌ **Não Conectado**: Nenhuma integração configurada

#### Desconectar Conta

1. Acesse **Integrações** > **Apple Calendar**
2. Clique em **"Desconectar"** ao lado do seu nome
3. Confirme a ação
4. Os eventos já criados no Apple Calendar **não serão removidos automaticamente**

---

## 🚀 Funcionalidades

### 1. Sincronização Automática de Agendamentos

#### Criação de Agendamento

Quando um agendamento é criado:

- ✅ Um evento é criado automaticamente no Apple Calendar do médico
- ✅ O evento é criado no formato iCalendar (.ics)
- ✅ O evento contém informações completas do agendamento:
  - Nome do paciente
  - Especialidade e tipo de consulta
  - Data e horário
  - Informações de contato do paciente
  - Observações
  - ID do agendamento (para rastreamento)

#### Edição de Agendamento

Quando um agendamento é editado:

- ✅ O evento no Apple Calendar é atualizado automaticamente
- ✅ **Estratégia**: O sistema deleta o evento antigo e cria um novo (mais confiável)
- ✅ Mudanças em horário, paciente, notas, etc. são refletidas no Apple Calendar

#### Cancelamento de Agendamento

Quando um agendamento é cancelado:

- ✅ O evento é removido automaticamente do Apple Calendar
- ✅ O status do agendamento é atualizado para "canceled"

#### Exclusão de Agendamento

Quando um agendamento é excluído:

- ✅ O evento é removido automaticamente do Apple Calendar

### 2. Descoberta Automática de Calendários

- ✅ O sistema pode descobrir automaticamente os calendários disponíveis no iCloud
- ✅ Usa o método `PROPFIND` do protocolo CalDAV
- ✅ Se não especificar a URL do calendário, o primeiro calendário encontrado será usado

### 3. Listagem de Eventos (API)

O sistema fornece uma API para listar eventos do Apple Calendar:

```
GET /workspace/{slug}/integrations/apple/api/{doctor}/events?start=2025-01-01&end=2025-01-31
```

**Resposta:**
```json
[
  {
    "id": "event_uid_123",
    "title": "João Silva - Cardiologia - Consulta",
    "start": "2025-01-15T10:00:00-03:00",
    "end": "2025-01-15T11:00:00-03:00",
    "description": "Detalhes do evento..."
  }
]
```

> **Nota**: A implementação de listagem de eventos está em desenvolvimento. Atualmente retorna array vazio.

### 4. Proteção contra Loops Infinitos

- ✅ O sistema usa `withoutEvents()` para evitar loops infinitos
- ✅ Mudanças apenas em `apple_event_id` não disparam nova sincronização
- ✅ Agendamentos de recorrência não são sincronizados individualmente

---

## 🔧 Estrutura Técnica

### Arquivos Principais

#### Controllers

- **`app/Http/Controllers/Tenant/Integrations/AppleCalendarController.php`**
  - Gerencia conexão/desconexão
  - Formulário de conexão
  - API de eventos

#### Services

- **`app/Services/Tenant/AppleCalendarService.php`**
  - Lógica de sincronização
  - Criação/atualização/exclusão de eventos
  - Comunicação CalDAV
  - Descoberta de calendários

#### Models

- **`app/Models/Tenant/AppleCalendarToken.php`**
  - Armazena credenciais CalDAV por médico
  - Relacionamento com `Doctor`
  - Senha é armazenada criptografada

#### Observers

- **`app/Observers/AppointmentObserver.php`**
  - Dispara sincronização automática
  - Escuta eventos: `created`, `updated`, `deleted`

### Estrutura de Dados

#### Tabela: `apple_calendar_tokens`

```sql
CREATE TABLE apple_calendar_tokens (
    id UUID PRIMARY KEY,
    doctor_id UUID NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL,
    password TEXT NOT NULL,  -- Criptografado
    server_url VARCHAR(255) NOT NULL DEFAULT 'https://caldav.icloud.com',
    calendar_url VARCHAR(255) NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);
```

#### Campo: `appointments.apple_event_id`

Armazena o UID do evento criado no Apple Calendar (formato: `{appointment_id}@{app_url}`).

### Fluxo de Sincronização

```
1. Agendamento Criado/Editado/Deletado
   ↓
2. AppointmentObserver detecta mudança
   ↓
3. AppleCalendarService.syncEvent()
   ↓
4. Verifica se médico tem token Apple
   ↓
5. Se sim, sincroniza com Apple Calendar via CalDAV
   ↓
6. Salva apple_event_id no agendamento
```

### Protocolo CalDAV

A integração usa o protocolo CalDAV para comunicação com o iCloud:

#### Métodos HTTP Utilizados

- **`PUT`**: Criar/atualizar evento
- **`DELETE`**: Remover evento
- **`PROPFIND`**: Descobrir calendários
- **`REPORT`**: Listar eventos (em desenvolvimento)

#### Formato iCalendar

Os eventos são criados no formato iCalendar (.ics):

```ical
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sistema de Agendamento//NONSGML v1.0//PT
BEGIN:VEVENT
UID:appointment_id@domain.com
SUMMARY:João Silva - Cardiologia - Consulta
DESCRIPTION:Detalhes do agendamento...
DTSTART:20250115T100000
DTEND:20250115T110000
DTSTAMP:20250101T120000
END:VEVENT
END:VCALENDAR
```

### Rotas

#### Rotas do Tenant

```php
// Listar integrações
GET /workspace/{slug}/integrations/apple

// Mostrar formulário de conexão
GET /workspace/{slug}/integrations/apple/{doctor}/connect

// Conectar
POST /workspace/{slug}/integrations/apple/{doctor}/connect

// Desconectar
DELETE /workspace/{slug}/integrations/apple/{doctor}/disconnect

// Status
GET /workspace/{slug}/integrations/apple/{doctor}/status

// API: Eventos
GET /workspace/{slug}/integrations/apple/api/{doctor}/events
```

---

## 🐛 Troubleshooting

### Problema: Erro "Tabela apple_calendar_tokens não existe"

**Causa**: As migrations não foram executadas.

**Solução**:
1. Execute as migrations conforme instruções na seção [Configuração](#configuração-no-sistema)
2. Verifique se as tabelas foram criadas:
   ```sql
   SELECT * FROM information_schema.tables 
   WHERE table_name = 'apple_calendar_tokens';
   ```

### Problema: Erro de Autenticação ao Conectar

**Causas Possíveis**:

1. **Senha Incorreta**: Verifique se está usando a senha correta do iCloud ou Senha de App Específica
2. **Autenticação de Dois Fatores**: Se tiver 2FA ativado, você **deve** usar uma Senha de App Específica
3. **Conta Bloqueada**: Muitas tentativas falhas podem bloquear temporariamente a conta

**Solução**:
1. Use uma **Senha de App Específica** (recomendado)
2. Verifique se o e-mail está correto
3. Tente novamente após alguns minutos se a conta foi bloqueada

### Problema: Erro "Não foi possível descobrir calendários"

**Causa**: O sistema não conseguiu descobrir os calendários automaticamente.

**Solução**:
1. Tente especificar manualmente a **URL do Calendário** no formulário de conexão
2. A URL geralmente segue o padrão: `/calendars/{username}/{calendar-id}/`
3. Você pode descobrir a URL usando um cliente CalDAV como o DAVx⁵ ou verificando nas configurações do iCloud

### Problema: Eventos não aparecem no Apple Calendar

**Verificações**:
1. ✅ Médico tem token Apple configurado?
2. ✅ Credenciais estão corretas?
3. ✅ URL do calendário está correta?
4. ✅ Verifique os logs: `storage/logs/laravel.log`

**Solução**:
1. Desconecte e reconecte a integração
2. Verifique se o calendário selecionado está visível no app Apple Calendar
3. Verifique os logs para erros específicos

### Problema: Eventos duplicados no Apple Calendar

**Causa**: Múltiplas chamadas de sincronização ou evento já existente.

**Solução**:
- O sistema já trata isso automaticamente:
  - Verifica se `apple_event_id` existe antes de criar
  - Deleta evento antigo antes de criar novo na edição

### Problema: Sincronização não funciona

**Verificações**:
1. ✅ Médico tem token Apple configurado?
2. ✅ Credenciais estão corretas e não expiraram?
3. ✅ URL do servidor está correta? (padrão: `https://caldav.icloud.com`)
4. ✅ Verifique os logs: `storage/logs/laravel.log`

### Problema: Erro de Conexão com Servidor CalDAV

**Causas Possíveis**:
1. URL do servidor incorreta
2. Problemas de rede/firewall
3. Servidor iCloud temporariamente indisponível

**Solução**:
1. Verifique se a URL do servidor está correta: `https://caldav.icloud.com`
2. Teste a conectividade:
   ```bash
   curl -I https://caldav.icloud.com
   ```
3. Verifique se não há firewall bloqueando conexões HTTPS

### Logs e Debug

Para debugar problemas, verifique os logs:

```bash
tail -f storage/logs/laravel.log | grep -i "apple"
```

O sistema registra:
- ✅ Tentativas de conexão
- ✅ Sincronizações bem-sucedidas
- ❌ Erros de sincronização
- 🔍 Descoberta de calendários

---

## 📝 Notas Importantes

1. **Senha de App Específica**: É altamente recomendado usar uma Senha de App Específica ao invés da senha normal do iCloud. Isso oferece maior segurança e evita problemas com autenticação de dois fatores.

2. **Criptografia de Senha**: As senhas são armazenadas criptografadas no banco de dados usando `encrypt()` do Laravel.

3. **URL do Calendário**: Se não especificar a URL do calendário, o sistema tentará descobrir automaticamente. Se falhar, você pode especificar manualmente.

4. **Sincronização Automática**: A sincronização automática é controlada por Laravel Observers. Não é necessário chamar manualmente os métodos de sincronização.

5. **Agendamentos de Recorrência**: Atualmente, agendamentos recorrentes não são suportados na integração Apple Calendar. Apenas agendamentos individuais são sincronizados.

6. **Formato de Evento**: Os eventos são criados no formato iCalendar padrão, compatível com qualquer cliente CalDAV.

7. **Performance**: A sincronização é síncrona. Erros não bloqueiam a criação/edição de agendamentos, mas podem aparecer nos logs.

---

## 🔗 Referências

- [CalDAV Protocol (RFC 4791)](https://tools.ietf.org/html/rfc4791)
- [iCalendar Format (RFC 5545)](https://tools.ietf.org/html/rfc5545)
- [SabreDAV Documentation](https://sabre.io/dav/)
- [Apple iCloud CalDAV Setup](https://support.apple.com/en-us/HT202304)
- [Laravel Observers](https://laravel.com/docs/eloquent#observers)

---

## 🚧 Funcionalidades Futuras

- [ ] Suporte completo a agendamentos recorrentes
- [ ] Listagem completa de eventos do Apple Calendar
- [ ] Sincronização bidirecional (eventos criados no Apple Calendar aparecem no sistema)
- [ ] Suporte a múltiplos calendários por médico
- [ ] Interface para seleção de calendário

