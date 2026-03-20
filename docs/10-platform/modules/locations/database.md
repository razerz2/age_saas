# Database

## Tabelas
- `paises` (legado tecnico para FKs antigas; sem cadastro funcional na UI)
- `estados`
- `cidades`

## Evolucao de schema
- Migration incremental:
  - `estados.ibge_id` (nullable + indice)
  - `cidades.ibge_id` (nullable + indice)

## Por que `ibge_id` ainda e nullable
- Existem bases legadas com registros historicos sem correspondencia oficial imediata.
- Forcar `NOT NULL` agora pode quebrar migracoes e fluxos em ambientes ja populados.
- O saneamento e incremental via sincronizacao oficial e logs de inconsistencias.

## Integridade e saneamento
- O sync IBGE gera indicadores operacionais:
  - `states_without_ibge`
  - `cities_without_match`
  - `states_with_duplicate_ibge`
  - `cities_with_duplicate_ibge`
- Os detalhes sao registrados em log para revisao segura antes de endurecer constraints.

## Compatibilidade preservada
- Chaves legadas continuam ativas:
  - `estado_id`
  - `cidade_id`
- Nao houve rename/recriacao destrutiva das tabelas.

## Seed oficial local
- Arquivo fonte: `database/data/ibge_localidades.json`
- Seeder: `OfficialIbgeLocationsSeeder`
