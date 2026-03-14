# Frontend

Padrao visual: Freedash (mesmo padrao dos modulos da Platform).

Comportamentos principais:

- preview de `body_text` no formulario;
- exibe orientacao de que templates aprovados devem ser versionados;
- botoes de acao: editar draft, duplicar versao, enviar Meta, sincronizar status, arquivar.

Campos do formulario:

- `key`
- `meta_template_name`
- `provider` fixo `whatsapp_business`
- `category` (`UTILITY` ou `SECURITY`)
- `language` (`pt_BR`)
- `header_text` (opcional)
- `body_text`
- `footer_text` (opcional)
- `variables` (JSON)
- `sample_variables` (JSON): exemplos obrigatorios por placeholder (`1`, `2`, `3`, ...)
- `buttons` (JSON opcional)

No formulario, a key exibe lista curta dos eventos SaaS recomendados para evitar uso de keys de dominio Tenant.

Exibicao no detalhe (Show):

- secao "Exemplos de Variaveis" mostrando o JSON persistido em `sample_variables`;
- quando ausente/incompleto para o `body_text`, a acao de enviar para a Meta e bloqueada com erro de validacao.

## Teste manual (Show)

Na tela de detalhe (Show) existe o botao `Testar template` que abre uma modal para envio manual do template selecionado.

### Teste manual de template

Campos da modal:

- numero de destino (formato internacional com DDI);
- inputs dinamicos das variaveis cadastradas no template (com opcao "Preencher com dados ficticios").

### Fluxo de envio de teste

- usuario abre a modal pelo botao `Testar template`;
- informa numero de destino;
- preenche variaveis manualmente ou usando dados ficticios;
- ao enviar, o backend monta o payload com base no schema remoto aprovado (quando disponivel) e dispara o envio via provider oficial.

### Diferenca entre schema local e schema remoto

- a tela exibe o cadastro local (`variables`, `sample_variables`, `body_text`, `buttons`);
- quando o template remoto aprovado diverge do cadastro local, o teste manual segue o schema remoto salvo em `meta_response` (fonte de verdade do envio).

### Tratamento de parametros (BODY/BUTTONS)

- o teste manual so pode ser executado quando `status=APPROVED`;
- para templates `AUTHENTICATION`/`SECURITY`, o sistema usa o schema remoto aprovado (via `meta_response`) para decidir:
  - quantidade de parametros do `BODY`;
  - existencia e parametros obrigatorios em `BUTTONS` (index/sub_type);
- o numero de parametros enviados respeita exatamente o que a Meta exige; quando houver divergencia entre cadastro local e remoto, o remoto prevalece no envio de teste.

### Regras de bloqueio

- templates `DRAFT`, `PENDING`, `REJECTED` e `ARCHIVED` exibem aviso e o envio fica bloqueado na interface;
- o backend tambem bloqueia qualquer envio quando o template nao esta em `APPROVED`.
