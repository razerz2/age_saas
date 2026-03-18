# Troubleshooting

## Modulo bloqueado por provider

Sintoma:

- mensagem de bloqueio ao acessar telas.

Causa:

- `WHATSAPP_PROVIDER` diferente de `whatsapp_business`.

Acao:

- configurar provider oficial na Platform (`WHATSAPP_PROVIDER=whatsapp_business`).

## Erro de configuracao Meta (token/WABA)

Sintoma:

- falha ao enviar/sincronizar template com mensagem tecnica de configuracao.

Causa:

- token ausente ou `WABA ID` ausente.

Acao:

- revisar campos em configuracoes da Platform:
  - `META_ACCESS_TOKEN`
  - `META_WABA_ID`

## Falha HTTP da API Meta

Sintoma:

- erro ao enviar/sincronizar com status HTTP.

Causa:

- erro de requisicao para Meta (payload, permissao de app, quota etc.).

Acao:

- verificar `meta_response` no detalhe do template e logs tecnicos (`meta_template_*`).

## Template em draft/pending/rejected nao envia

Sintoma:

- fluxo Platform tenta enviar WhatsApp oficial, mas nada e entregue.

Causa:

- runtime oficial usa apenas template com `status=approved` para a key solicitada.

Acao:

- confirmar status na tela de templates oficiais;
- submeter para Meta e sincronizar status ate ficar `approved`;
- validar logs `platform_whatsapp_official_send_skipped` (reason: `template_not_approved`).

## Teste manual bloqueado no Show

Sintoma:

- botao `Testar template` existe, mas o envio nao ocorre (ou retorna erro de bloqueio).

Causa:

- template nao esta em `APPROVED`;
- provider ativo diferente de `whatsapp_business`;
- numero de destino ausente/invalido.

Acao:

- sincronizar status com a Meta para garantir `approved` e `meta_response` atualizado;
- confirmar configuracao do provider oficial;
- revisar logs `platform_whatsapp_official_manual_test` (result: `blocked`) e motivo.

## Rejeicao no teste manual por parametros (BODY/BUTTONS)

Sintoma:

- Meta retorna erro indicando falta/excesso de parametros, ou falta de parametro em botao dinamico (ex.: URL).

Causa:

- schema remoto aprovado exige parametros em `BODY` e/ou `BUTTONS` e o teste foi enviado sem valor suficiente;
- divergencia entre cadastro local (variaveis) e template remoto aprovado.

Acao:

- garantir que `meta_response` esta atualizado (acao "Sincronizar Status");
- preencher no teste manual a variavel semantica `code` quando se tratar de template de autenticacao/OTP;
- se houver botao dinamico, o payload exige componente `button` com `index/sub_type` e parametro(s) no formato esperado pela Meta;
- validar logs de envio do provider (`WhatsApp Meta authentication template send attempt`) e o retorno detalhado em caso de `422` na UI.

## Erro Meta 132000 (parametros nao conferem)

Sintoma:

- `(#132000) Number of parameters does not match the expected number of params`.

Causa:

- a quantidade de parametros enviada no payload nao corresponde ao numero esperado pelo template aprovado na Meta (normalmente no `BODY`).

Solucao:

- sincronizar o template (`Sincronizar Status`) para atualizar `meta_response`;
- revisar o schema remoto aprovado no Show (quantidade de placeholders do `BODY`);
- reenviar o teste manual garantindo que os valores informados cobrem a quantidade esperada.

## Erro Meta 132001 (template nao encontrado)

Sintoma:

- erro HTTP 404 com:
  - `(#132001) Template name does not exist in the translation`

Causa:

- o nome/idioma consultado nao corresponde a um template existente na WABA configurada;
- em ambientes com legado, pode haver divergencia entre `meta_template_name` local e o nome canonico remoto.

Solucao:

- sincronizar o template (`Sincronizar Status`) para atualizar `meta_response` e confirmar nome/idioma remoto;
- se o template ainda nao existir remotamente, submeter (`Enviar para Meta`) e sincronizar ate ficar `APPROVED`.

## Erro Meta 131008 (botao dinamico exige parametro)

Sintoma:

- `(#131008) Required parameter is missing` com detalhe indicando `buttons: Button at index X ... requires a parameter`.

Causa:

- o template remoto aprovado possui `BUTTONS` dinamicos (ex.: URL com placeholder) e o payload foi enviado sem componente `button` com o parametro exigido.

Solucao:

- sincronizar o template (`Sincronizar Status`) para atualizar `meta_response`;
- revisar no Show o resumo do schema remoto de `BUTTONS` (index/sub_type e quantidade de parametros);
- reenviar o teste manual informando valores suficientes (ex.: `code` para templates de autenticacao/OTP).

## 2FA por WhatsApp indisponivel no usuario Platform

Sintoma:

- ao selecionar 2FA via WhatsApp, o sistema bloqueia com mensagem de telefone inapto;
- no challenge de login, pode ocorrer fallback para email.

Causa:

- usuario Platform sem telefone valido para envio oficial (E.164 normalizado).

Acao:

- revisar cadastro do usuario Platform para garantir telefone valido;
- enquanto indisponivel, usar 2FA por email/TOTP;
- validar logs `platform_2fa_whatsapp_unavailable_on_settings` e `platform_2fa_whatsapp_unavailable_on_challenge`.

## Rejeicao: variaveis do modelo sem texto de amostra

Sintoma:

- ao enviar/criar template, a Meta retorna erro indicando que variaveis do modelo estao sem texto de amostra (examples).

Causa:

- `body_text` possui placeholders (`{{1}}`, `{{2}}`, ...) e nao ha exemplos cadastrados para todos eles.

Acao:

- no Create/Edit do template, preencher `sample_variables` com um exemplo para cada placeholder presente no `body_text`;
- reenviar o template para a Meta.

## Comando legado: limpeza de chaves clinicas

Quando usar:

- somente em instalacoes antigas onde registros clinicos foram cadastrados por engano no catalogo oficial e precisam ser arquivados/removidos.

Importante:

- na arquitetura atual, `appointment.*`/`waitlist.*` podem fazer parte do baseline oficial tenant e sao geridos no modulo `whatsapp-official-tenant-templates`.
- use o comando abaixo apenas em instalacoes antigas onde essas chaves existiam por engano e devem ser removidas/arquivadas.

Acao:

- executar primeiro em simulacao (dry-run):
  - `php artisan whatsapp-official-templates:clean-clinical`
- aplicar arquivamento controlado:
  - `php artisan whatsapp-official-templates:clean-clinical --apply --mode=archive`
- opcionalmente remover registros:
  - `php artisan whatsapp-official-templates:clean-clinical --apply --mode=delete`
