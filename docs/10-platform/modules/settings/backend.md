# Backend

## Componentes principais

- Controller: `SystemSettingsController`
- Persistencia: `SystemSetting` (`sysconfig`)
- Catalogo global de providers nao oficiais: `TenantGlobalProviderCatalogService`
- Resolucao de runtime: `ProviderConfigResolver`

## Platform WhatsApp: providers e configuracao

`SystemSettingsController@updateIntegrations` aceita e persiste:

- `WHATSAPP_PROVIDER` com opcoes: `whatsapp_business`, `zapi`, `waha`, `evolution`
- `EVOLUTION_BASE_URL`
- `EVOLUTION_API_KEY`
- `EVOLUTION_INSTANCE`
- `WHATSAPP_GLOBAL_ENABLED_PROVIDERS` (catalogo global consumido por tenants)

Separacao de responsabilidade:

- `WHATSAPP_PROVIDER`: provider usado pela propria Platform.
- `WHATSAPP_GLOBAL_ENABLED_PROVIDERS`: catalogo de providers nao oficiais permitidos para tenants em modo global.

## Teste de conexao Evolution (Platform)

`SystemSettingsController@testConnection` para `service=evolution`:

- aplica config runtime da requisicao (`EVOLUTION_BASE_URL`, `EVOLUTION_API_KEY`, opcionalmente `EVOLUTION_INSTANCE`)
- usa `EvolutionClient::testConnection()`
- endpoint de saude: `GET /instance/fetchInstances`
- nao depende de `instanceName`
- nao depende de `default`

O teste valida:

- URL da API
- API key
- disponibilidade/alcance da Evolution API

## Instancia da Platform x instancia por tenant

### Platform

- `EVOLUTION_INSTANCE` e a instancia padrao da Platform.
- usada em operacoes da Platform que precisam de instancia (ex.: teste de envio Evolution).

### Tenant global + Evolution

- nao usa `EVOLUTION_INSTANCE` da Platform como fonte da instancia da tenant.
- usa resolucao server-side via `ProviderConfigResolver::resolveEvolutionConfig()` + `TenantEvolutionGlobalInstanceService::resolveRuntimeInstance()`.
- instancia operacional da tenant deriva do slug/subdomain.

## Provisionamento e vinculo por tenant

Em `Tenant\SettingsController@updateNotifications`, quando:

- `whatsapp_driver=global`
- `whatsapp_global_provider=evolution`

o backend chama `TenantEvolutionGlobalInstanceService::ensureProvisionedForCurrentTenant()`.

Comportamento:

- tenta reutilizar instancia existente da tenant
- cria instancia se nao existir
- persiste vinculo em `tenant_whatsapp_global_instances` com `provider=evolution`
- persiste `status` e `last_error`
- trata idempotencia e corrida com constraints unicas

## Runtime de providers nao oficiais

`ProviderConfigResolver::applyUnofficialRuntimeConfigs()` aplica em paralelo:

- WAHA (`services.whatsapp.waha.*`)
- Evolution (`services.whatsapp.evolution.*`)

Para `driver=global`, o provider global efetivo do tenant decide qual instancia/sessao sera resolvida para runtime.
Nao existe fallback silencioso entre WAHA e Evolution quando um provider global foi selecionado.

## Seguranca multi-tenant (Evolution)

Operacoes da aba Evolution no tenant passam por `TenantEvolutionGlobalOperationsService`, que:

- valida tenant autenticada atual
- exige `driver=global` e provider global efetivo `evolution`
- busca o vinculo da propria tenant em `tenant_whatsapp_global_instances`
- resolve e corrige `instance_name` para o slug/subdomain da tenant
- usa apenas instancia resolvida no backend (sem confiar em `instance_name` de frontend)

## Rotas de operacao Evolution (tenant)

Controlador: `Tenant\EvolutionGlobalInstanceController`

- `GET settings/evolution/status`
- `GET settings/evolution/qr-code`
- `POST settings/evolution/start`
- `POST settings/evolution/restart`
- `POST settings/evolution/logout`

As rotas nao recebem `instance_name` como parametro de operacao.
