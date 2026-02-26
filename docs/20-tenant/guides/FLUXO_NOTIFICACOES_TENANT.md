# 🔔 Fluxo de Notificações de Agendamentos - Processamento Tenant por Tenant

## 📋 Visão Geral

O comando `appointments:notify-upcoming` processa **cada tenant separadamente**, garantindo que:
- Cada tenant tenha seu próprio banco de dados isolado
- Cada tenant use suas próprias configurações
- Cada tenant tenha seus próprios pacientes e agendamentos
- As notificações sejam enviadas usando as credenciais de email/WhatsApp de cada tenant

---

## 🔄 Fluxo de Execução

```
┌─────────────────────────────────────────────────────────────┐
│ 1. COMANDO INICIA                                           │
│    Conexão: banco 'platform'                                │
│    Tabelas: tenants, plans, subscriptions, etc.             │
└─────────────────────────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. BUSCA TODOS OS TENANTS ATIVOS                           │
│    Query: Tenant::where('status', 'active')->get()         │
│    Resultado: [Tenant A, Tenant B, Tenant C, ...]          │
└─────────────────────────────────────────────────────────────┘
                    │
                    ▼
        ┌───────────────────┐
        │  LOOP POR TENANT  │
        └───────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
┌──────────────────┐   ┌──────────────────┐
│ TENANT A         │   │ TENANT B         │
└──────────────────┘   └──────────────────┘
```

---

## 🔧 Processamento de Cada Tenant

### **ETAPA 1: Inicialização do Tenant**

```php
tenancy()->initialize($tenant);
```

**O que acontece:**

1. **Ativa o tenant atual**
   - `Tenant::current()` agora retorna este tenant
   - Todas as queries passam a usar o contexto deste tenant

2. **Troca a conexão de banco de dados:**
   ```
   ANTES: banco 'platform'
   ├── tenants (lista de todos os tenants)
   ├── plans
   ├── subscriptions
   └── system_settings
   
   DEPOIS: banco do tenant (ex: 'tenant_clinica_abc')
   ├── appointments (agendamentos deste tenant)
   ├── patients (pacientes deste tenant)
   ├── doctors (médicos deste tenant)
   ├── tenant_settings (configurações deste tenant)
   └── ... (todas as tabelas do tenant)
   ```

3. **Configura a conexão 'tenant':**
   ```php
   Config::set('database.connections.tenant.host', $tenant->db_host);
   Config::set('database.connections.tenant.database', $tenant->db_name);
   Config::set('database.connections.tenant.username', $tenant->db_username);
   Config::set('database.connections.tenant.password', $tenant->db_password);
   ```

---

### **ETAPA 2: Busca Agendamentos do Tenant**

```php
$appointments = Appointment::with(['patient', 'calendar.doctor.user', 'specialty'])
    ->where('status', 'scheduled')
    ->whereBetween('starts_at', [...])
    ->get();
```

**O que acontece:**

- A query é executada no **banco DO TENANT**
- Busca apenas agendamentos **deste tenant específico**
- Cada tenant tem sua própria tabela `appointments` isolada

**Exemplo:**
- Tenant A: busca em `tenant_clinica_abc.appointments`
- Tenant B: busca em `tenant_clinica_xyz.appointments`

---

### **ETAPA 3: Verifica Configurações do Tenant**

```php
$reminderHours = TenantSetting::get('appointments.reminder_hours', 24);
$emailEnabled = TenantSetting::isEnabled('notifications.send_email_to_patients');
$whatsappEnabled = TenantSetting::isEnabled('notifications.send_whatsapp_to_patients');
```

**O que acontece:**

- `TenantSetting::get()` busca no **banco DO TENANT**
- Cada tenant tem suas próprias configurações
- Configurações são isoladas por tenant

**Exemplo:**
- Tenant A: `reminder_hours = 24` (envia 24h antes)
- Tenant B: `reminder_hours = 48` (envia 48h antes)

---

### **ETAPA 4: Envia Notificações**

```php
// Email usando configurações do tenant
$emailService = app(MailTenantService::class);
$emailService->send($patient->email, $subject, $body);

// WhatsApp usando configurações do tenant
$whatsappService = app(WhatsappTenantService::class);
$whatsappService->send($patient->phone, $message);
```

**O que acontece:**

- `MailTenantService` usa as configurações de email **deste tenant**
- `WhatsappTenantService` usa as configurações de WhatsApp **deste tenant**
- Cada tenant pode ter provedores diferentes (Gmail, SendGrid, Z-API, Meta, etc.)

**Exemplo:**
- Tenant A: usa Gmail (SMTP próprio)
- Tenant B: usa SendGrid (API própria)
- Tenant C: usa configuração global da plataforma

---

### **ETAPA 5: Finalização do Tenant**

```php
tenancy()->end();
```

**O que acontece:**

1. **Desativa o tenant atual**
   - `Tenant::current()` retorna `null`

2. **Volta a conexão para 'platform'**
   ```
   DEPOIS: banco 'platform'
   ├── tenants
   ├── plans
   └── ...
   ```

3. **Limpa todas as configurações do tenant**
   - Garante que não haja "vazamento" de contexto

---

## 📊 Exemplo Prático

### **Cenário: 3 Tenants Ativos**

```
┌─────────────────────────────────────────────────────────────┐
│ COMANDO: appointments:notify-upcoming                      │
└─────────────────────────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│ 1. Busca tenants no banco 'platform'                       │
│    Resultado: [Clinica ABC, Clinica XYZ, Clinica 123]      │
└─────────────────────────────────────────────────────────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
┌──────────────────┐   ┌──────────────────┐
│ CLINICA ABC      │   │ CLINICA XYZ      │
│                  │   │                  │
│ 1. initialize()  │   │ 1. initialize()  │
│    → banco:      │   │    → banco:      │
│    tenant_abc    │   │    tenant_xyz    │
│                  │   │                  │
│ 2. Busca:        │   │ 2. Busca:        │
│    5 agendamentos│   │    3 agendamentos│
│                  │   │                  │
│ 3. Configurações:│   │ 3. Configurações:│
│    reminder: 24h │   │    reminder: 48h │
│    email: Gmail  │   │    email: SendGrid│
│                  │   │                  │
│ 4. Envia:        │   │ 4. Envia:        │
│    5 emails      │   │    3 emails      │
│    5 WhatsApps   │   │    3 WhatsApps   │
│                  │   │                  │
│ 5. end()         │   │ 5. end()         │
│    → banco:      │   │    → banco:      │
│    platform     │   │    platform     │
└──────────────────┘   └──────────────────┘
```

---

## 🔐 Isolamento e Segurança

### **Isolamento de Dados**

- ✅ Cada tenant tem seu próprio banco de dados
- ✅ Agendamentos de um tenant não são visíveis para outros
- ✅ Pacientes de um tenant não são visíveis para outros
- ✅ Configurações de um tenant não afetam outros

### **Isolamento de Configurações**

- ✅ Cada tenant pode ter suas próprias credenciais de email
- ✅ Cada tenant pode ter suas próprias credenciais de WhatsApp
- ✅ Cada tenant pode configurar horas de lembrete diferentes
- ✅ Cada tenant pode habilitar/desabilitar notificações independentemente

---

## ⚠️ Pontos Importantes

### **1. Sempre usar `finally` para limpar contexto**

```php
try {
    tenancy()->initialize($tenant);
    // ... processamento ...
} finally {
    tenancy()->end(); // SEMPRE executa, mesmo em caso de erro
}
```

**Por quê?**
- Garante que o contexto seja limpo mesmo em caso de erro
- Evita "vazamento" de contexto entre tenants
- Previne que queries de um tenant sejam executadas no contexto de outro

### **2. Contexto é por thread/processo**

- Cada execução do comando tem seu próprio contexto
- Múltiplas execuções simultâneas não interferem entre si
- Cada processo mantém seu próprio estado de tenant

### **3. Models com `connection => 'tenant'`**

- Models como `Appointment`, `Patient`, `TenantSetting` usam `connection => 'tenant'`
- Quando o tenant é inicializado, essas queries vão para o banco do tenant
- Models da plataforma (como `Tenant`, `Plan`) sempre usam `connection => 'platform'`

---

## 📝 Resumo

1. **Busca tenants** no banco `platform`
2. **Para cada tenant:**
   - Inicializa contexto (troca para banco do tenant)
   - Busca agendamentos (no banco do tenant)
   - Verifica configurações (no banco do tenant)
   - Envia notificações (usando credenciais do tenant)
   - Finaliza contexto (volta para banco platform)
3. **Mostra resumo** de todos os tenants processados

**Resultado:** Cada tenant é processado de forma completamente isolada, garantindo privacidade e segurança dos dados.

