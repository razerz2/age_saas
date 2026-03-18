# Troubleshooting

## Template aprovado na Meta, mas UI diz "nao encontrado"

Sintoma:

- no Show/Modal aparece mensagem indicando que o template ainda nao foi localizado na Meta para nome/idioma.

Causa comum:

- divergencia entre `meta_template_name` local e o nome canonico remoto aprovado na Meta (ex.: local com prefixo `tenant_`).

Solucao:

- use `Sincronizar status` no Show para atualizar `meta_response` e corrigir nome canonico quando aplicavel;
- confirme no detalhe que o snapshot remoto corresponde ao idioma (`pt_BR`) esperado.

## Erro Meta 132001 (template nao existe na traducao)

Sintoma:

- erro HTTP 404 com:
  - `(#132001) Template name does not exist in the translation`

Causa:

- o nome/idioma consultado nao corresponde a um template existente na WABA configurada.

Solucao:

- sincronize e valide o nome canonico remoto (`meta_template_name`) + idioma;
- se o template ainda nao existir remotamente, submeta primeiro (`Enviar para Meta`) e sincronize ate ficar `APPROVED`.

## Erro Meta 132000 (quantidade de parametros divergente)

Sintoma:

- erro HTTP 400 com:
  - `(#132000) Number of parameters does not match the expected number of params`

Causa:

- schema local (`variables`) diverge do schema remoto aprovado (placeholders efetivos do `BODY`).

Solucao:

- sincronize o template para atualizar `meta_response`;
- no teste manual, siga os campos efetivos exibidos (schema remoto);
- quando houver divergencia, a UI exibira aviso: `Divergencia detectada: local=X e remoto=Y`.

## Template encontrado, mas mensagem chega com conteudo trocado

Sintoma:

- o envio ocorre, mas os valores aparecem em placeholders errados (ex.: data no lugar de link).

Causa:

- divergencia entre schema local e remoto somada a alinhamento ingenuo por posicao.

Solucao:

- o modulo aplica mapeamento semantico por `key/slot` para keys do baseline tenant (ex.: `appointment.confirmed`):
  - `{{1}}` -> `patient_name`
  - `{{2}}` -> `appointment_date`
  - `{{3}}` -> `appointment_details_link`
- se o remoto mudar, sincronize para refletir o `BODY` remoto e revalide o teste manual.
