# Solução para Erro 419 (PAGE EXPIRED)

## Causas Comuns

O erro 419 geralmente ocorre quando:
- A sessão expirou enquanto você estava na página
- Cookies não estão sendo enviados corretamente
- O token CSRF expirou ou não foi gerado corretamente
- Há problemas com cache do navegador

## Soluções Rápidas

### 1. Limpar Cache e Sessões
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

> **Nota:** o Laravel não vem com `php artisan session:clear` por padrão. A limpeza depende do `SESSION_DRIVER` (ex.: `file`, `database`, `redis`).

### 2. Limpar Sessões Manualmente
```bash
# Limpar arquivos de sessão (se usar driver 'file')
rm -rf storage/framework/sessions/*
```

**Windows (PowerShell), se `SESSION_DRIVER=file`:**
```powershell
Remove-Item -Force -Recurse "storage\framework\sessions\*" -ErrorAction SilentlyContinue
```

**Se `SESSION_DRIVER=database`:**
- Trunque a tabela `sessions` (após garantir que não impactará usuários em produção).

### 3. Verificar Configuração de Sessão
No arquivo `.env`, verifique:
```env
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false  # Para desenvolvimento local
```

### 4. No Navegador
- Limpar cookies do site
- Fazer hard refresh (Ctrl+Shift+R ou Cmd+Shift+R)
- Tentar em modo anônimo/privado
- Verificar se cookies não estão bloqueados

### 5. Verificar Permissões
```bash
# Garantir que o diretório de sessões tem permissão de escrita
chmod -R 775 storage/framework/sessions
chown -R www-data:www-data storage/framework/sessions  # Linux
```

> Em **Windows**, permissões (`chmod/chown`) não se aplicam da mesma forma; valide as permissões NTFS do diretório `storage/`.

## Para Desenvolvimento Local

Se estiver usando `127.0.0.1:8000`, certifique-se de que:

1. **APP_URL está correto no .env:**
```env
APP_URL=http://127.0.0.1:8000
```

2. **SESSION_DOMAIN está vazio ou null:**
```env
SESSION_DOMAIN=
```

3. **SESSION_SECURE_COOKIE está false:**
```env
SESSION_SECURE_COOKIE=false
```

## Verificação no Código

O formulário de login já possui `@csrf`, então o problema não é falta do token.

Verifique se:
- O middleware `VerifyCsrfToken` está ativo
- A sessão está sendo iniciada corretamente
- Não há conflitos entre múltiplas sessões de tenant

## Solução Definitiva

Se o problema persistir, tente:

1. **Aumentar o tempo de sessão temporariamente:**
```env
SESSION_LIFETIME=480  # 8 horas
```

2. **Usar driver de sessão diferente:**
```env
SESSION_DRIVER=database
```
E criar a tabela de sessões:
```bash
php artisan session:table
php artisan migrate
```

3. **Verificar logs:**
```bash
tail -f storage/logs/laravel.log
```

**Windows (PowerShell):**
```powershell
Get-Content "storage\logs\laravel.log" -Wait
```

