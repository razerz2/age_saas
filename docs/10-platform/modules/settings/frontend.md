# Frontend

## Platform > Aba WhatsApp

A tela de configuracoes da Platform exibe seletor de provider com:

- WhatsApp Business (Meta)
- Z-API
- WAHA
- Evolution API

O bloco de Evolution exibe:

- `EVOLUTION_BASE_URL`
- `EVOLUTION_API_KEY`
- `EVOLUTION_INSTANCE` (instancia padrao da Platform)

Mensagem funcional da UI:

- `EVOLUTION_INSTANCE` e usada para operacoes da Platform (como teste de envio).
- `EVOLUTION_INSTANCE` nao define instancia de tenant global.

## Testes na UI da Platform

### Testar Conexao Evolution

- usa endpoint backend de teste de conexao Evolution
- backend valida conectividade via `GET /instance/fetchInstances`
- independe de instancia especifica (incluindo `default`)

### Testar Envio Evolution

- usa `EVOLUTION_BASE_URL` + `EVOLUTION_API_KEY` + `EVOLUTION_INSTANCE` informados na tela
- objetivo: validar operacao de envio na instancia padrao da Platform

## Catalogo global para tenants (na mesma aba WhatsApp)

A Platform define os providers globais habilitados para tenants via `WHATSAPP_GLOBAL_ENABLED_PROVIDERS`.
No estado atual:

- WAHA e Evolution podem coexistir como opcoes globais
- Meta oficial permanece fora do catalogo global de tenant

## Tenant Settings (comportamento refletido na UI tenant)

Quando tenant seleciona `Usar servico global do sistema`:

- a lista de provider global mostra apenas providers habilitados na Platform
- tenant pode escolher `waha` e/ou `evolution` conforme catalogo habilitado
- selecao invalida/desabilitada e bloqueada no backend

Abas operacionais:

- aba `WAHA`: aparece apenas no contexto global efetivo WAHA valido
- aba `Evolution`: aparece apenas no contexto global efetivo Evolution valido

Na aba Evolution, a instancia e exibida somente leitura, com acoes:

- Conectar
- Restart
- Logout
- Atualizar status
- Atualizar QR

## Troubleshooting de UI (operacional)

- Erro `Instance "default" not found` no teste de conexao
  - comportamento atual correto: o teste de conexao nao depende de instancia
- Erro `Cannot GET /api/...`
  - ajustar `EVOLUTION_BASE_URL` para a raiz correta da instalacao Evolution (sem `/api`, quando a instalacao nao usa esse prefixo)
- Evolution nao aparece para tenant em provider global
  - validar habilitacao da Platform em `WHATSAPP_GLOBAL_ENABLED_PROVIDERS`
- Aba Evolution nao aparece no tenant
  - validar driver global
  - validar provider global `evolution`
  - validar vinculo de instancia da tenant
  - validar configuracao global Evolution na Platform
