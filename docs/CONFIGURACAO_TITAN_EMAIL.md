# Configuração SMTP - Titan Email (Hostinger/Gator)

## Informações do Suporte

Conforme informações do suporte do Gator/Hostinger/Titan Email:

### Servidor de Saída (SMTP)
- **Servidor SMTP**: `smtp.titan.email`
- **Porta**: `465` ou `587`
- **Segurança**: 
  - Porta `465`: SSL
  - Porta `587`: TLS
- **Autenticação**: Sim (use seu endereço de e-mail completo e senha)

### Servidor de Entrada (IMAP) - Para leitura de e-mails
- **Servidor IMAP**: `imap.titan.email`
- **Porta**: `993`
- **Segurança**: SSL

## Configuração no `.env`

### Opção 1: Porta 587 com TLS (Recomendado)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=587
MAIL_USERNAME=notification@allsync.com.br
MAIL_PASSWORD=all@0612
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notification@allsync.com.br
MAIL_FROM_NAME="AllSync Notifications"
```

### Opção 2: Porta 465 com SSL (Alternativa)

Se a porta 587 não funcionar, tente a porta 465 com SSL:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.titan.email
MAIL_PORT=465
MAIL_USERNAME=notification@allsync.com.br
MAIL_PASSWORD=all@0612
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notification@allsync.com.br
MAIL_FROM_NAME="AllSync Notifications"
```

## ⚠️ Pontos Importantes

### 1. Senha SEM Aspas

**❌ ERRADO:**
```env
MAIL_PASSWORD="all@0612"
```

**✅ CORRETO:**
```env
MAIL_PASSWORD=all@0612
```

### 2. Usuário e Remetente Devem Ser Iguais

O `MAIL_USERNAME` e `MAIL_FROM_ADDRESS` devem ser iguais:

```env
MAIL_USERNAME=notification@allsync.com.br
MAIL_FROM_ADDRESS=notification@allsync.com.br
```

### 3. Criptografia e Porta

- **Porta 587**: Use `MAIL_ENCRYPTION=tls`
- **Porta 465**: Use `MAIL_ENCRYPTION=ssl`

Recomendamos começar com a porta 587 e TLS. Se não funcionar, tente a porta 465 com SSL.

### 4. Endereço de E-mail Completo

O `MAIL_USERNAME` deve ser o endereço de e-mail completo usado para autenticação no servidor SMTP.

## Verificação da Configuração

Após configurar o `.env`, execute:

```bash
php artisan config:clear
```

## Teste de Envio

Teste o envio usando o tinker:

```php
Mail::raw('Teste de envio', function ($m) {
    $m->to('seuemail@gmail.com')->subject('Teste SMTP Titan Email');
});
```

## Problemas Comuns

### Erro 535: "authentication failed"

**Causas possíveis:**
1. Senha incorreta
2. Senha com aspas no `.env`
3. Usuário não corresponde ao endereço de e-mail configurado
4. Senha contém caracteres especiais que precisam ser tratados

**Soluções:**
1. Verifique se a senha está correta no painel do Titan Email
2. Remova aspas da senha no `.env`
3. Certifique-se de que `MAIL_USERNAME` é o endereço completo de e-mail
4. Se necessário, redefina a senha no painel do Titan Email

### Erro 553: "Sender address rejected"

**Causa:** O remetente não corresponde ao usuário autenticado.

**Solução:** Faça com que `MAIL_FROM_ADDRESS` seja igual a `MAIL_USERNAME`.

## Configurações Adicionais (Opcionais)

Se houver problemas com certificado SSL:

```env
MAIL_VERIFY_PEER=false
MAIL_VERIFY_PEER_NAME=false
MAIL_TIMEOUT=30
```

## Notas

- O sistema já está configurado para ignorar erros de certificado SSL quando necessário
- A configuração SSL é aplicada automaticamente via `AppServiceProvider`
- O Transport SMTP é criado usando o método padrão do Laravel para garantir compatibilidade

