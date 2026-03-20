# Frontend

- Blade TailAdmin

## Visibilidade operacional

A Platform destaca a situacao comercial em:

- listagem de tenants (coluna comercial)
- detalhe da tenant (resumo comercial e alerta de bloqueio)
- edicao da tenant (resumo comercial antes do formulario)
- dashboard (status comercial no quadro de tenants)

## Fluxo guiado apos criacao

Quando a tenant e criada sem elegibilidade comercial:

- a tela de detalhe mostra banner de pendencia comercial
- a UI explica que o ambiente foi provisionado, mas o acesso segue bloqueado
- existe acao rapida `Regularizar agora`/`Criar Assinatura` apontando para `Platform.subscriptions.create` com `tenant_id` preselecionado
