# 🔍 Diagnóstico e Correção: redirect_uri_mismatch (Google Calendar)

## 📋 Checklist de Verificação

### 1️⃣ Verificar o redirect gerado pelo Laravel

**Passo 1:** Acesse a tela de integrações do Google Calendar no tenant  
**Passo 2:** Clique em "Conectar com Google"  
**Passo 3:** Verifique os logs do Laravel (`storage/logs/laravel.log`)

Procure por esta mensagem:
```
🔍 DIAGNÓSTICO REDIRECT URI - Google Calendar OAuth
```

**Ou descomente temporariamente a linha no controller:**
```php
// Em app/Http/Controllers/Tenant/Integrations/GoogleCalendarController.php
// Linha ~65, descomente:
dd(['redirect_uri' => $redirectUri, 'app_url' => config('app.url')]);
```

### 2️⃣ Verificar APP_URL no .env

O redirect URI gerado pelo Laravel usa `route()`, que depende do `APP_URL` no `.env`.

**Verifique se está:**
```env
APP_URL=https://5946f73d7978.ngrok-free.app
```

**⚠️ NÃO use:**
- ❌ `APP_URL=http://127.0.0.1:8000`
- ❌ `APP_URL=http://localhost:8000`
- ❌ `APP_URL=http://5946f73d7978.ngrok-free.app` (sem https)

### 3️⃣ Limpar cache após alterar APP_URL

Depois de alterar o `.env`, execute:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4️⃣ Verificar Google Cloud Console

O redirect URI cadastrado deve ser **EXATAMENTE**:
```
https://5946f73d7978.ngrok-free.app/google/callback
```

**Verifique:**
- ✅ Protocolo: `https://` (não `http://`)
- ✅ Sem barra final: `/google/callback` (não `/google/callback/`)
- ✅ Subdomínio correto do ngrok

### 5️⃣ Verificar Rota Global

A rota `/google/callback` deve estar em `routes/web.php` (não em `routes/tenant.php`):

```php
// routes/web.php
Route::get('/google/callback', [GoogleCalendarController::class, 'callback'])
    ->name('google.callback');
```

✅ Confirmado? A rota está correta!

### 6️⃣ Resumo do Problema

O erro `redirect_uri_mismatch` ocorre quando:

| Laravel gera | Google espera | Status |
|-------------|---------------|--------|
| `http://127.0.0.1:8000/google/callback` | `https://5946...ngrok-free.app/google/callback` | ❌ ERRO |
| `https://5946...ngrok-free.app/google/callback` | `https://5946...ngrok-free.app/google/callback` | ✅ OK |

### 7️⃣ Solução Completa

1. **Atualize o `.env`:**
   ```env
   APP_URL=https://5946f73d7978.ngrok-free.app
   ```

2. **Limpe os caches:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

3. **Verifique o redirect gerado:**
   - Veja os logs ou descomente o `dd()` temporariamente
   - Confirme que é exatamente: `https://5946f73d7978.ngrok-free.app/google/callback`

4. **Teste novamente:**
   - Acesse a tela de integrações
   - Clique em "Conectar com Google"
   - Verifique se funciona sem erro

### 8️⃣ Se ainda não funcionar

Verifique também:

- ✅ O ngrok está rodando e acessível?
- ✅ O Google Cloud Console tem o redirect correto?
- ✅ Não há espaço extra no `APP_URL` no `.env`?
- ✅ O cache foi limpo após alterar `.env`?

## 🎯 Resultado Esperado

Após corrigir o `APP_URL`, o Laravel deve gerar:
```
https://5946f73d7978.ngrok-free.app/google/callback
```

E esse valor deve ser **IDÊNTICO** ao cadastrado no Google Cloud Console.

