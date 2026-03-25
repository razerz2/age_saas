# Modulo: Localizacao (Brasil)

Este modulo passou a operar com base oficial do IBGE para estados e municipios brasileiros.

## Principios
- Base mestra: IBGE (estados e municipios).
- ViaCEP: apenas auxiliar de preenchimento de CEP nos formularios.
- Pais: Brasil implicito, sem cadastro funcional na interface administrativa.
- Compatibilidade: `estado_id` e `cidade_id` foram preservados para nao quebrar FKs legadas.
- Legado tecnico de pais (`paises`, `pais_id`, `country_id`) permanece apenas por compatibilidade de schema.

## Estado final esperado da base
- Estados oficiais: `27`
- Municipios oficiais: `5570`
- Campos oficiais: `estados.ibge_id` e `cidades.ibge_id`

## Arquivos principais
- `app/Console/Commands/SyncIbgeLocationsCommand.php`
- `app/Services/Platform/IbgeLocationApiService.php`
- `app/Services/Platform/IbgeLocationSyncService.php`
- `database/seeders/OfficialIbgeLocationsSeeder.php`
- `app/Services/Platform/ZipcodeLookupService.php`
- `app/Http/Controllers/Platform/ZipcodeController.php`

## Operacao recomendada
1. Aplicar migrations pendentes:
   - `php artisan migrate`
2. Sincronizar IBGE e atualizar cache local:
   - `php artisan locations:sync-ibge --write-cache`
3. Popular ambiente local/CI com seed oficial:
   - `php artisan db:seed --class=OfficialIbgeLocationsSeeder`
4. Opcional: executar sync usando somente cache local:
   - `php artisan locations:sync-ibge --from-cache`

## Referencias desta pasta
- `overview.md`
- `routes.md`
- `backend.md`
- `frontend.md`
- `database.md`
- `permissions.md`
- `views.md`
- `troubleshooting.md`
