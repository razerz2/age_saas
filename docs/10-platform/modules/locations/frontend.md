# Frontend

## Comportamento atual
- Formularios de pacientes, configuracoes da tenant e pre-cadastro da landing:
  - carregam estados/cidades via endpoints internos do sistema.
  - consultam CEP via `GET /api/zipcode/{cep}` (backend).
  - preenchem `state_id`/`city_id` com IDs internos quando ha match por IBGE.

## Regras
- Frontend nao chama ViaCEP diretamente.
- Mascara de CEP permanece ativa.
- Fallback manual permanece disponivel quando CEP nao e encontrado ou nao mapeia cidade interna.
