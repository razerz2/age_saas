# 🧪 Guia de Teste - Redirecionamento quando Sessão Expira

Este guia explica como testar se o redirecionamento para o login da tenant está funcionando corretamente quando a sessão expira.

## 📋 Pré-requisitos

1. Ter uma tenant criada e funcionando
2. Estar logado na tenant (ex: `/workspace/{slug}/dashboard`)
3. Conhecer o slug da tenant (ex: `minha-clinica`)

## 🧪 Métodos de Teste

### Método 1: Limpar Cookies/Sessão Manualmente (Mais Simples)

**Passos:**

1. **Faça login na tenant:**
   - Acesse: `http://127.0.0.1:8000/t/{tenant-slug}/login`
   - Faça login normalmente

2. **Acesse uma rota protegida:**
   - Exemplo: `http://127.0.0.1:8000/workspace/{tenant-slug}/dashboard`
   - Confirme que está logado

3. **Limpe os cookies da sessão:**
   - **Chrome/Edge:** 
     - Pressione `F12` para abrir DevTools
     - Vá na aba `Application` → `Cookies` → `http://127.0.0.1:8000`
     - Delete o cookie `laravel_session` (ou o nome configurado em `SESSION_COOKIE`)
   - **Firefox:**
     - Pressione `F12` para abrir DevTools
     - Vá na aba `Armazenamento` → `Cookies` → `http://127.0.0.1:8000`
     - Delete o cookie da sessão

4. **Tente acessar uma rota protegida novamente:**
   - Exemplo: `http://127.0.0.1:8000/workspace/{tenant-slug}/dashboard`
   - Ou clique em qualquer link do menu (ex: `/workspace/{tenant-slug}/appointments`)

5. **Verifique o redirecionamento:**
   - ✅ **Esperado:** Deve redirecionar para `/t/{tenant-slug}/login`
   - ❌ **Errado:** Redirecionar para `/login` (login da plataforma)

---

### Método 2: Usar Console do Navegador (JavaScript)

**Passos:**

1. **Faça login na tenant normalmente**

2. **Abra o Console do navegador** (`F12` → aba `Console`)

3. **Execute este código para limpar a sessão:**
   ```javascript
   // Limpa todos os cookies da sessão
   document.cookie.split(";").forEach(function(c) { 
       document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
   });
   ```

4. **Tente acessar uma rota protegida:**
   - Digite na barra de endereço: `http://127.0.0.1:8000/workspace/{tenant-slug}/dashboard`
   - Ou recarregue a página atual

5. **Verifique o redirecionamento:**
   - ✅ Deve ir para `/t/{tenant-slug}/login`

---

### Método 3: Reduzir Temporariamente o Tempo de Sessão

**Passos:**

1. **Edite o arquivo `.env`:**
   ```env
   SESSION_LIFETIME=1
   ```
   (Isso define a sessão para expirar em 1 minuto)

2. **Limpe o cache de configuração:**
   ```bash
   php artisan config:clear
   ```

3. **Faça login na tenant**

4. **Aguarde 2 minutos** (mais que o tempo de expiração)

5. **Tente acessar uma rota protegida**

6. **Verifique o redirecionamento**

7. **⚠️ IMPORTANTE:** Após o teste, volte o valor original:
   ```env
   SESSION_LIFETIME=120
   ```
   E execute: `php artisan config:clear`

---

### Método 4: Limpar Sessões Manualmente (recomendado)

**Passos:**

1. **Faça login na tenant**

2. **Limpe as sessões conforme o `SESSION_DRIVER`:**
   - **Se `SESSION_DRIVER=file` (Linux/macOS):**
     ```bash
     rm -rf storage/framework/sessions/*
     ```
   - **Se `SESSION_DRIVER=file` (Windows PowerShell):**
     ```powershell
     Remove-Item -Force -Recurse "storage\framework\sessions\*" -ErrorAction SilentlyContinue
     ```
   - **Se `SESSION_DRIVER=database`:** trunque a tabela `sessions` (com cuidado em produção).

3. **Tente acessar uma rota protegida**

4. **Verifique o redirecionamento**

---

### Método 5: Verificar Logs

**Passos:**

1. **Monitore os logs em tempo real:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
   **Windows (PowerShell):**
   ```powershell
   Get-Content "storage\logs\laravel.log" -Wait
   ```

2. **Faça login na tenant**

3. **Limpe a sessão** (usando um dos métodos acima)

4. **Tente acessar uma rota protegida**

5. **Verifique nos logs:**
   - Procure por mensagens do middleware `Authenticate`
   - Deve aparecer tentativas de obter o slug do tenant
   - Se aparecer erro "Não foi possível encontrar slug", significa que precisa melhorar a detecção

---

## ✅ Resultado Esperado

Quando a sessão expira e você tenta acessar uma rota autenticada do tenant (`/workspace/{slug}/*`):

1. **URL de redirecionamento:** `http://127.0.0.1:8000/t/{tenant-slug}/login`
2. **NÃO deve redirecionar para:** `http://127.0.0.1:8000/login`

## 🔍 Verificações Adicionais

### Verificar se o slug está sendo detectado:

Adicione temporariamente este código no método `redirectTo` do `Authenticate` para debug:

```php
\Log::info("🔍 Tentando obter tenant slug", [
    'route_tenant' => $request->route('tenant'),
    'session_slug' => session('tenant_slug'),
    'current_tenant' => Tenant::current()?->subdomain,
    'user_tenant_id' => Auth::guard('tenant')->user()?->tenant_id ?? null,
]);
```

### Testar diferentes cenários:

1. ✅ Sessão expirada mas `tenant_slug` ainda na sessão
2. ✅ Sessão completamente limpa (sem cookies)
3. ✅ Usuário ainda "logado" mas sessão expirada (token inválido)
4. ✅ Acessar diretamente `/workspace/{slug}/dashboard` sem estar logado

---

## 🐛 Troubleshooting

### Problema: Ainda redireciona para `/login`

**Possíveis causas:**
1. Cache de configuração não limpo → Execute `php artisan config:clear`
2. Cache de rotas → Execute `php artisan route:clear`
3. Middleware não está sendo aplicado → Verifique `routes/tenant.php`

### Problema: Erro 403 ao invés de redirecionar

**Causa:** Não conseguiu encontrar o slug do tenant

**Solução:** Verifique os logs para ver qual método de detecção falhou e melhore a lógica no `getTenantSlug()`

---

## 📝 Notas

- O teste mais confiável é o **Método 1** (limpar cookies manualmente)
- Sempre teste em uma tenant real, não apenas em desenvolvimento
- Após os testes, certifique-se de restaurar configurações originais

