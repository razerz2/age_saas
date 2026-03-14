# Views

- `resources/views/platform/whatsapp_official_templates/index.blade.php`
- `resources/views/platform/whatsapp_official_templates/show.blade.php`
- `resources/views/platform/whatsapp_official_templates/create.blade.php`
- `resources/views/platform/whatsapp_official_templates/edit.blade.php`
- `resources/views/platform/whatsapp_official_templates/_form.blade.php`

Telas:

- Index com listagem, filtro e acoes;
- Show com detalhes, status Meta, resposta tecnica resumida e historico de versoes;
- Create/Edit com formulario padrao, preview e orientacao de versionamento.

Detalhe (Show) - teste manual:

### Teste manual de template

- botao `Testar template` abre modal de envio manual;
- a modal solicita:
  - numero de destino;
  - variaveis do template (inputs dinamicos, com opcao de preencher com dados ficticios);
- acao bloqueada quando `status` diferente de `approved`.

### Diferenca entre schema local e schema remoto

- para templates `AUTHENTICATION`/`SECURITY`, a tela exibe um resumo do schema remoto salvo em `meta_response`:
  - quantidade de parametros exigidos no `BODY`;
  - quantidade de parametros exigidos em `BUTTONS` e detalhes por botao (`index`/`sub_type`);
- o teste manual usa o schema remoto aprovado como fonte de verdade para o payload, mesmo que o cadastro local tenha variaveis diferentes.
