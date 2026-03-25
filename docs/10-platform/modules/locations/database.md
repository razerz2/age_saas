# Database

## Tabelas
- `paises` (legado tecnico para FKs antigas; sem cadastro funcional na UI)
- `estados`
- `cidades`

## Evolucao de schema
- Migration incremental:
  - `estados.ibge_id` (nullable + indice)
  - `cidades.ibge_id` (nullable + indice)
  - arquivo: `database/migrations/2026_03_19_100000_add_ibge_ids_to_estados_e_cidades_tables.php`

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

## Contagem oficial esperada (Brasil)
- `estados`: `27`
- `cidades`: `5570`

## Validacao rapida de integridade
- Conferir contagens:
  - `SELECT COUNT(*) FROM estados;`
  - `SELECT COUNT(*) FROM cidades;`
- Conferir pendencias de codigo oficial:
  - `SELECT COUNT(*) FROM estados WHERE ibge_id IS NULL;`
  - `SELECT COUNT(*) FROM cidades WHERE ibge_id IS NULL;`
- Conferir duplicidades de `ibge_id`:
  - `SELECT ibge_id, COUNT(*) FROM estados WHERE ibge_id IS NOT NULL GROUP BY ibge_id HAVING COUNT(*) > 1;`
  - `SELECT ibge_id, COUNT(*) FROM cidades WHERE ibge_id IS NOT NULL GROUP BY ibge_id HAVING COUNT(*) > 1;`
