# Frontend

Menu final:

- `WhatsApp Oficial`
  - `Templates Oficiais Platform`
  - `Templates Oficiais Tenant`
- `WhatsApp Nao Oficial`
  - `Templates Internos Platform`
  - `Templates Padrao Tenant`

Telas do modulo oficial tenant (baseline clinico oficial):

- `index`: lista templates oficiais do dominio Tenant (somente keys clinicas suportadas)
- `create`: cria template oficial tenant no catalogo global (rascunho/draft)
- `edit`: ajusta template local (antes de aprovacao Meta)
- `show`: detalhe do template + acoes operacionais (Meta)

UX da tela:

- deixa explicito que e baseline oficial tenant (nao e baseline nao oficial);
- deixa explicito que depende de aprovacao da Meta;
- deixa explicito que o teste manual usa o schema remoto aprovado.

Show: acoes principais (Meta):

- `Enviar para Meta` (submissao/criacao remota)
- `Sincronizar status` (atualiza status e snapshot remoto)
- `Testar template` (modal)

Modal de teste manual (pontos importantes):

- exibe aviso quando o schema local diverge do remoto (ex.: local=6 placeholders e remoto=3);
- os campos exibidos para preenchimento seguem o schema remoto aprovado;
- quando necessario, aplica alinhamento semantico por `key/slot` para evitar trocar significado dos placeholders.
