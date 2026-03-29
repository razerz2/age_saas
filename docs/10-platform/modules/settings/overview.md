# Overview

Configuracoes globais da Platform, incluindo provedores de WhatsApp oficiais e nao oficiais,
catalogo global para tenants e testes operacionais.

## Platform > Configuracoes > WhatsApp

A Platform suporta os seguintes providers no seletor "Provedor WhatsApp":

- `whatsapp_business` (Meta)
- `zapi`
- `waha`
- `evolution`

Para Evolution API, os campos da Platform sao:

- `EVOLUTION_BASE_URL`
- `EVOLUTION_API_KEY`
- `EVOLUTION_INSTANCE`

Regra importante:

- `EVOLUTION_INSTANCE` e a instancia padrao da propria Platform.
- Ela pode ser usada em operacoes da Platform que exigem instancia (ex.: teste de envio).
- Ela NAO define a instancia de tenant em modo global.

## Tenant global (coexistencia WAHA + Evolution)

Quando o tenant usa `whatsapp_driver=global`, a selecao de provider global vem do catalogo habilitado pela Platform:

- `waha`
- `evolution`

Regras de dominio:

- So providers globais habilitados na Platform aparecem para o tenant.
- Meta oficial nao entra nesse catalogo global de tenant.
- Nao existe fallback silencioso entre `waha` e `evolution`.
- Provider invalido ou desabilitado e bloqueado explicitamente na validacao.

## Instancia da Platform x instancia por tenant

Separacao obrigatoria:

- Platform: usa `EVOLUTION_INSTANCE` apenas para operacoes administrativas da Platform.
- Tenant global + Evolution: instancia resolvida server-side por tenant (slug/subdomain operacional), sem campo manual no tenant.
- Vinculo por tenant/provider persistido em `tenant_whatsapp_global_instances` (provider `evolution` ou `waha`).

## Guia Evolution no tenant

A aba `Evolution` no tenant existe e opera a instancia global da propria clinica.

Ela aparece somente quando o contexto atual e valido, incluindo:

- `whatsapp_driver=global`
- provider global efetivo `evolution`
- vinculo/contexto de instancia Evolution disponivel para a tenant autenticada

Operacoes disponiveis:

- Conectar
- Restart
- Logout
- Atualizar status
- Atualizar QR

Seguranca:

- O frontend nao e fonte de verdade para `instance_name`.
- Rotas nao operam instancia informada pelo usuario.
- A instancia alvo e sempre resolvida no backend a partir da tenant autenticada.

## Troubleshooting rapido

- `Instance "default" not found`
  - Causa comum: teste antigo dependente de instancia especifica.
  - Estado atual: o teste de conexao Evolution da Platform nao depende de instancia.
- `Cannot GET /api/...`
  - Causa comum: instalacao Evolution sem prefixo `/api`.
  - Acao: configurar `EVOLUTION_BASE_URL` sem `/api`.
- Provider nao aparece no tenant
  - Verificar `WHATSAPP_GLOBAL_ENABLED_PROVIDERS` na Platform.
  - Verificar se `evolution` esta habilitado no catalogo global.
- Aba Evolution nao aparece
  - Verificar `whatsapp_driver=global`.
  - Verificar provider global efetivo `evolution`.
  - Verificar vinculo em `tenant_whatsapp_global_instances`.
  - Verificar configuracao global Evolution valida na Platform.
