# Integrations — Backend

Fontes: `app/Http/Controllers/Tenant/IntegrationController.php`.

## Controller

- `App\Http\Controllers\Tenant\IntegrationController`
  - Métodos públicos:
    - `index()`
    - `create()`
    - `store(StoreIntegrationRequest $request)`
    - `show($slug, $id)`
    - `edit($slug, $id)`
    - `update(UpdateIntegrationRequest $request, $slug, $id)`
    - `destroy($slug, $id)`

## Requests

- `App\Http\Requests\Tenant\Integrations\StoreIntegrationRequest`
- `App\Http\Requests\Tenant\Integrations\UpdateIntegrationRequest`

## Models

- `App\Models\Tenant\Integrations`

## Fluxo geral (factual)

- `index`:
  - Lista integrações ordenadas por `key` e paginadas (20 por página).

- `create`:
  - Retorna view de criação sem lógica adicional.

- `store`:
  - Valida dados via `StoreIntegrationRequest`.
  - Gera `id` com `Str::uuid()`.
  - Cria registro em `Integrations`.

- `show` / `edit`:
  - Carregam `Integrations` por id e enviam para as views de detalhes/edição.

- `update`:
  - Atualiza integração com dados validados de `UpdateIntegrationRequest`.

- `destroy`:
  - Remove a integração e redireciona com mensagem de sucesso.
