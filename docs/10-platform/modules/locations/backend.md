# Backend

## Sincronizacao oficial IBGE
- Command: `php artisan locations:sync-ibge`
- Classe de API: `App\Services\Platform\IbgeLocationApiService`
- Classe de sync: `App\Services\Platform\IbgeLocationSyncService`
- Escopo: Brasil apenas (pais fixo interno, sem parametro de pais nos endpoints).
- Opcoes do command:
  - `--from-cache`
  - `--write-cache`
  - `--cache-path=database/data/ibge_localidades.json`

Fluxo:
1. Busca estados e municipios na API oficial do IBGE (ou arquivo local).
2. Estados: match por `ibge_id` e fallback por `UF`.
3. Cidades: match por `ibge_id`, fallback por `(estado_id + nome normalizado)`.
4. Preserva IDs internos existentes sempre que possivel.
5. Nao remove registros legados em massa; inconsistencias sao logadas.

## Seed local oficial
- Seeder: `Database\Seeders\OfficialIbgeLocationsSeeder`
- Origem do seed: `database/data/ibge_localidades.json`

## Endpoint interno de CEP
- Controller: `App\Http\Controllers\Platform\ZipcodeController`
- Service: `App\Services\Platform\ZipcodeLookupService`
- Rota: `GET /api/zipcode/{zipcode}`

Fluxo:
1. Valida CEP.
2. Consulta ViaCEP no backend.
3. Resolve cidade/estado por `cidades.ibge_id`.
4. Retorna IDs internos para selecao de estado/cidade no front.
5. Em inconsistencias, retorna fallback textual + warnings sem quebrar o form.
6. Usa cache interno por CEP para reduzir chamadas externas.

## Contrato de resposta do CEP
- Campos principais:
  - `zipcode`, `street`, `neighborhood`, `complement`
  - `state.id`, `state.uf`, `state.name`, `state.ibge_id`
  - `city.id`, `city.name`, `city.ibge_id`
- Fallback controlado:
  - `fallback.state_uf`
  - `fallback.city_name`
  - `fallback.city_ibge_id`
  - `warnings[]`

## Codigos de erro do CEP
- `422 invalid_zipcode`: CEP fora do formato esperado (8 digitos).
- `404 zipcode_not_found`: CEP inexistente no ViaCEP.
- `502 zipcode_service_unavailable` / `invalid_zipcode_response`: falha externa.

## Integridade incremental
- O sync reporta e loga pendencias para saneamento seguro:
  - estados sem `ibge_id`
  - cidades sem `ibge_id`/match oficial
  - duplicidades de `ibge_id` em estados/cidades
