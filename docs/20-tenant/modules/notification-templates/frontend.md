# Frontend

## 1) Layout do Editor

Arquivo:

- `resources/views/tenant/settings/tabs/editor.blade.php`

Estrutura:

- 2 colunas (desktop):
  - esquerda: formulário do editor (canal/tipo, assunto, conteúdo, botões, alertas, preview)
  - direita: card “Variáveis disponíveis” (sticky no desktop)
- Mobile: 1 coluna (variáveis ficam abaixo do formulário).

## 2) Filtros (canal e tipo)

- Canal e Tipo são selects full width (um por linha).
- Trocar canal/tipo faz `GET` para Settings preservando `tab=editor`.

## 3) Status do template

- Badge “Padrão” quando não existe override.
- Badge “Personalizado” quando existe override para `(tenant_id, channel, key)`.

## 4) Campos editáveis

- `email`:
  - Campo “Assunto” aparece apenas quando o template default define `subject`.
  - Campo “Conteúdo” sempre existe.
- `whatsapp`:
  - Apenas “Conteúdo”.

## 5) Botões e ações

Ordem atual dos botões (mesma linha):

1. Restaurar padrão (remove override)
2. Pré-visualizar (gera preview renderizado sem salvar)
3. Salvar (cria/atualiza override)

## 6) Preview renderizado

- Usa os valores do formulário (subject/content) sem salvar.
- Se houver um Appointment recente no tenant, usa dados reais via `NotificationContextBuilder`.
- Se não houver Appointment, usa contexto mock básico e mostra aviso.
- Para keys `waitlist.*`, aplica fallback de preview para preencher campos de waitlist.

## 7) Emojis (inserção no cursor)

- Paleta simples de emojis no Editor.
- Clique insere no campo atualmente focado (Assunto/Conteúdo) na posição do cursor.

## 8) Placeholders desconhecidos

- Preview: mostra aviso não bloqueante com placeholders não resolvidos no contexto.
- Save: após salvar, mostra aviso com placeholders desconhecidos detectados no último save.

