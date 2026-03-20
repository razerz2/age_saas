# Modulo: Localizacao (Brasil)

Este modulo passou a operar com base oficial do IBGE para estados e municipios brasileiros.

## Principios
- Base mestra: IBGE (estados e municipios).
- ViaCEP: apenas auxiliar de preenchimento de CEP nos formularios.
- Pais: Brasil implicito, sem cadastro funcional na interface administrativa.
- Compatibilidade: `estado_id` e `cidade_id` foram preservados para nao quebrar FKs legadas.
- Legado tecnico de pais (`paises`, `pais_id`, `country_id`) permanece apenas por compatibilidade de schema.

## Arquivos principais
- `app/Console/Commands/SyncIbgeLocationsCommand.php`
- `app/Services/Platform/IbgeLocationApiService.php`
- `app/Services/Platform/IbgeLocationSyncService.php`
- `database/seeders/OfficialIbgeLocationsSeeder.php`
- `app/Services/Platform/ZipcodeLookupService.php`
- `app/Http/Controllers/Platform/ZipcodeController.php`

## Operacao recomendada
1. Sincronizar IBGE e atualizar cache local:
   - `php artisan locations:sync-ibge --write-cache`
2. Popular ambiente local/CI com seed oficial:
   - `php artisan db:seed --class=OfficialIbgeLocationsSeeder`

## Referencias desta pasta
- `overview.md`
- `routes.md`
- `backend.md`
- `frontend.md`
- `database.md`
- `permissions.md`
- `views.md`
- `troubleshooting.md`
