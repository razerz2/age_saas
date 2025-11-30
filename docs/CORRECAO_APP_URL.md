# ✅ Correção do APP_URL para Google Calendar

## 🔴 Problema Identificado

O log mostrou que o Laravel está gerando:
```
http://127.0.0.1:8000/google/callback
```

Mas o Google espera:
```
https://5946f73d7978.ngrok-free.app/google/callback
```

## ✅ Solução

### Passo 1: Editar o arquivo `.env`

Abra o arquivo `.env` na raiz do projeto e altere a linha `APP_URL`:

**ANTES:**
```env
APP_URL=http://127.0.0.1:8000
```

**DEPOIS:**
```env
APP_URL=https://5946f73d7978.ngrok-free.app
```

⚠️ **IMPORTANTE:**
- ✅ Use `https://` (não `http://`)
- ✅ Não coloque barra final (`/`)
- ✅ Use o domínio completo do ngrok

### Passo 2: Caches já foram limpos ✅

Os caches foram limpos automaticamente. Se precisar limpar novamente:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Passo 3: Testar novamente

1. Acesse a tela de integrações do Google Calendar
2. Clique em "Conectar com Google"
3. Verifique o log (`storage/logs/laravel.log`)
4. Agora deve aparecer: `sao_iguais: true` ✅

### Passo 4: Verificar o resultado esperado

Após alterar o `.env`, o log deve mostrar:

```
redirect_uri_gerado: "https://5946f73d7978.ngrok-free.app/google/callback"
sao_iguais: true
```

## 📝 Checklist

- [ ] Editei o `.env` e alterei `APP_URL`
- [ ] Confirmei que está usando `https://` e não `http://`
- [ ] Confirmei que não há barra final
- [ ] Testei novamente a conexão
- [ ] O log agora mostra `sao_iguais: true`

## ⚠️ Se o ngrok mudar

Se você reiniciar o ngrok e ele gerar uma nova URL (ex: `abc123.ngrok-free.app`), você precisará:

1. Atualizar `APP_URL` no `.env` para a nova URL
2. Atualizar o redirect URI no Google Cloud Console
3. Limpar os caches novamente

