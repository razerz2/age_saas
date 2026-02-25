# Overview

## O que é

O Editor de Notificações (Settings > Editor) permite personalizar templates de notificação por tenant, por canal e por tipo (key), sem alterar o default do sistema.

Conceitos:

- **Default (sistema)**: vem de `config/notification_templates.php` e é imutável.
- **Override (tenant)**: fica no banco (tabela `notification_templates`) por `(tenant_id, channel, key)`.
- **Restaurar padrão**: remove o override; o sistema volta a usar o default automaticamente.

## Canais

- `email`: possui `subject` (quando o template default define subject) e `content`.
- `whatsapp`: possui apenas `content` (subject é ignorado).

## Keys (tipos) suportadas

As keys atuais do catálogo (config) usadas no fluxo de agendamentos/waitlist são:

- `appointment.pending_confirmation`
- `appointment.confirmed`
- `appointment.canceled`
- `appointment.expired`
- `waitlist.joined`
- `waitlist.offered`

## Variáveis (placeholders)

Os templates usam placeholders no formato `{{a.b}}` (dot notation). Exemplo:

- `{{patient.name}}`
- `{{appointment.date}}`
- `{{links.appointment_confirm}}`

As variáveis efetivamente disponíveis dependem do contexto gerado por:

- `app/Services/Tenant/NotificationContextBuilder.php`

Se uma variável não existir no contexto, o placeholder é mantido como texto (não falha o envio).

## Auditoria

Toda tentativa de envio real (success/error) é registrada no banco do tenant:

- `notification_deliveries` (ver `database.md`)

Por padrão, a auditoria não armazena o corpo completo da mensagem (LGPD); registra hashes e tamanho.

