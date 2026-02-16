# Configuração de E-mail - SMTP AllSync

## Variáveis de Ambiente Necessárias

Adicione ou verifique estas variáveis no seu arquivo `.env` (linhas 37-44):

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.allsync.com.br
MAIL_PORT=587
MAIL_USERNAME=seu_usuario@allsync.com.br
MAIL_PASSWORD=sua_senha_aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@allsync.com.br
MAIL_FROM_NAME="AllSync"
```

> **Segurança:** não coloque credenciais reais (usuário/senha) na documentação. Use placeholders e mantenha os valores apenas no `.env`/secret manager.

## Variáveis Opcionais para SSL

Para desabilitar a verificação de certificado SSL (quando o certificado não corresponde ao hostname):

```env
MAIL_VERIFY_PEER=false
MAIL_VERIFY_PEER_NAME=false
MAIL_TIMEOUT=30
```

## Explicação das Variáveis

- **MAIL_MAILER**: Tipo de mailer (smtp, sendmail, mailgun, etc.)
- **MAIL_HOST**: Servidor SMTP (ex: mail.allsync.com.br)
- **MAIL_PORT**: Porta SMTP (587 para TLS, 465 para SSL)
- **MAIL_USERNAME**: Usuário para autenticação SMTP
- **MAIL_PASSWORD**: Senha para autenticação SMTP
- **MAIL_ENCRYPTION**: Tipo de criptografia (tls ou ssl)
- **MAIL_FROM_ADDRESS**: Endereço de e-mail do remetente
- **MAIL_FROM_NAME**: Nome do remetente
- **MAIL_VERIFY_PEER**: Verificar certificado SSL (false para ignorar erros de certificado)
- **MAIL_VERIFY_PEER_NAME**: Verificar nome do certificado (false para ignorar incompatibilidade de CN)
- **MAIL_TIMEOUT**: Timeout da conexão em segundos (padrão: 30)

## Problema de Certificado SSL Resolvido

O sistema foi configurado para ignorar erros de certificado SSL quando `MAIL_VERIFY_PEER=false` e `MAIL_VERIFY_PEER_NAME=false`.

Isso é útil quando:
- O certificado SSL não corresponde ao hostname (ex: certificado para `*.hostgator.com.br` mas conectando em `mail.allsync.com.br`)
- O certificado é autoassinado
- Há problemas de validação de certificado

## ⚠️ Problemas Comuns

### 1. "authentication failed" (Erro 535)

**Erro:** `535 5.7.8 Error: authentication failed`

**Causa:** O servidor SMTP está rejeitando as credenciais de autenticação.

**Soluções:**

1. **Remova aspas da senha no `.env`**:
   ```env
   # ❌ ERRADO
   MAIL_PASSWORD="SUA_SENHA_AQUI"
   
   # ✅ CORRETO
   MAIL_PASSWORD=SUA_SENHA_AQUI
   ```

2. **Verifique se a senha está correta**: Confirme a senha no painel do seu provedor de e-mail (Titan Email, Hostinger, etc.)

3. **Caracteres especiais**: Se a senha contém caracteres especiais como `@`, `#`, `$`, etc., certifique-se de que não há espaços extras ou caracteres invisíveis.

4. **Verifique o usuário**: O `MAIL_USERNAME` deve ser o endereço de e-mail completo usado para autenticação.

### 2. "Sender address rejected: not logged in"

**Erro:** `553 5.7.1 <notification@seu-dominio.com>: Sender address rejected: not logged in`

**Causa:** O servidor SMTP está rejeitando o endereço do remetente porque ele não corresponde ao usuário autenticado.

**Solução:** O `MAIL_FROM_ADDRESS` deve ser **igual** ao `MAIL_USERNAME`, ou ser um alias válido configurado no servidor SMTP.

### Exemplo Correto:

```env
MAIL_USERNAME=usuario@seu-dominio.com
MAIL_FROM_ADDRESS=usuario@seu-dominio.com
```

### Ou se você tem um alias configurado:

```env
MAIL_USERNAME=usuario@seu-dominio.com
MAIL_FROM_ADDRESS=notification@seu-dominio.com  # Deve ser um alias válido do usuário
```

**Importante:** Se você quiser usar `notification@seu-dominio.com` como remetente, você precisa:
1. Fazer login com uma conta que tenha esse endereço, OU
2. Configurar um alias no servidor SMTP que permita que `usuario@allsync.com.br` envie como `notification@allsync.com.br`

## Testando a Configuração

Após configurar o `.env`, execute:

```bash
php artisan config:clear
```

Depois teste o envio de e-mail através do painel administrativo ou usando:

```php
Mail::raw('Teste', function ($m) {
    $m->to('seuemail@gmail.com')->subject('Teste SMTP');
});
```

## Notas Importantes

1. **Segurança**: Em produção, é recomendado usar certificados SSL válidos. As opções `MAIL_VERIFY_PEER=false` devem ser usadas apenas quando necessário.

2. **Autenticação**: Certifique-se de que `MAIL_USERNAME` e `MAIL_PASSWORD` estão corretos. O erro "SMTP AUTH is required" indica que as credenciais estão faltando ou incorretas.

3. **Remetente vs Usuário**: O `MAIL_FROM_ADDRESS` deve corresponder ao `MAIL_USERNAME` ou ser um alias válido. Muitos servidores SMTP rejeitam e-mails quando o remetente não corresponde ao usuário autenticado.

4. **Porta**: 
   - Porta 587 geralmente usa TLS (MAIL_ENCRYPTION=tls)
   - Porta 465 geralmente usa SSL (MAIL_ENCRYPTION=ssl)

5. **Timeout**: Se houver muitos erros de "Too many concurrent SMTP connections", aumente o `MAIL_TIMEOUT` ou aguarde alguns segundos entre tentativas.

