# Troubleshooting

## Estados/cidades nao aparecem
- Verifique se as migrations foram aplicadas:
  - `php artisan migrate`
- Execute: `php artisan locations:sync-ibge --write-cache`
- Depois: `php artisan db:seed --class=OfficialIbgeLocationsSeeder`

## CEP retorna rua/bairro mas nao seleciona cidade
- Verifique se `cidades.ibge_id` foi preenchido.
- O mapeamento usa `ibge` do ViaCEP; nomes textuais sao apenas fallback.
- Verifique se o front chama `GET /api/zipcode/{cep}` e nao ViaCEP direto.

## CEP nao preenche nas telas
- Confirme no navegador se a requisicao para `/api/zipcode/{cep}` esta sendo feita.
- Se retornar `422`, CEP esta invalido (menos de 8 digitos).
- Se retornar `404`, CEP nao existe (preenchimento manual deve permanecer).
- Se retornar `502`, houve falha externa do ViaCEP; tente novamente.
- Em tenant create/edit, confirme inclusao do helper:
  - `resources/views/platform/tenants/partials/address-lookup-script.blade.php`

## Pendencias de saneamento IBGE
- Rode `php artisan locations:sync-ibge --from-cache` e revise os indicadores finais:
  - `states_without_ibge`
  - `cities_without_match`
  - `states_with_duplicate_ibge`
  - `cities_with_duplicate_ibge`
- Os exemplos de registros pendentes sao gravados no log da aplicacao.

## Inconsistencias de base antiga
- O sync nao remove registros em massa automaticamente.
- Registros sem match ficam preservados para revisao manual segura.

## Base sem `ibge_id` apos deploy
- Geralmente indica ambiente sem migration/seed/sync recentes.
- Ordem recomendada:
  - `php artisan migrate`
  - `php artisan locations:sync-ibge --write-cache`
  - `php artisan db:seed --class=OfficialIbgeLocationsSeeder`

## Estados/cidades com divergencia de base oficial
- Reexecute o sync oficial e revise os logs de inconsistencias.
- Valide contagens esperadas:
  - `27` estados
  - `5570` cidades

## Falha de rede no IBGE ou ViaCEP
- Reexecute o comando de sync em horario diferente.
- Em producao, mantenha o cache local `database/data/ibge_localidades.json` atualizado.
