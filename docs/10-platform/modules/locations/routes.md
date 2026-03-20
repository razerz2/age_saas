# Routes

## Publicas (landing/formularios)
- `GET /api/location/estados` -> `api.public.estados`
- `GET /api/location/cidades/{estado}` -> `api.public.cidades`
- `GET /api/zipcode/{zipcode}` -> `api.zipcode`

## Internas da Platform
- `GET /Platform/api/estados` -> `Platform.api.estados`
- `GET /Platform/api/cidades/{estado}` -> `Platform.api.cidades`
- `resource /Platform/estados`
- `resource /Platform/cidades`

## Importante
- Nao existe rota funcional de gestao de paises na interface administrativa.
- O escopo geografico e exclusivamente Brasil.
