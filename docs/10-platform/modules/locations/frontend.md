# Frontend

## Comportamento atual
- Formularios com auto preenchimento de CEP:
  - landing / pre-cadastro
  - pacientes
  - configuracoes da tenant
  - tenants create/edit (Platform)
- Todos esses fluxos:
  - carregam estados/cidades via endpoints internos do sistema.
  - consultam CEP via `GET /api/zipcode/{cep}` (backend).
  - preenchem `state_id`/`city_id` com IDs internos quando ha match por IBGE.

## Tenant create/edit (Platform)
- As telas:
  - `resources/views/platform/tenants/create.blade.php`
  - `resources/views/platform/tenants/edit.blade.php`
- Usam helper reutilizavel:
  - `resources/views/platform/tenants/partials/address-lookup-script.blade.php`
- O helper:
  - aplica mascara/normalizacao de CEP
  - consulta no `input` (quando atinge 8 digitos) e no `blur`
  - seleciona estado/cidade por IDs internos retornados pelo endpoint de CEP

## Regras
- Frontend nao chama ViaCEP diretamente.
- Mascara de CEP permanece ativa.
- Fallback manual permanece disponivel quando CEP nao e encontrado ou nao mapeia cidade interna.
