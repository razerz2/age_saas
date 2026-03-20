# Troubleshooting

## Estados/cidades nao aparecem
- Execute: `php artisan locations:sync-ibge --write-cache`
- Depois: `php artisan db:seed --class=OfficialIbgeLocationsSeeder`

## CEP retorna rua/bairro mas nao seleciona cidade
- Verifique se `cidades.ibge_id` foi preenchido.
- O mapeamento usa `ibge` do ViaCEP; nomes textuais sao apenas fallback.

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

## Falha de rede no IBGE ou ViaCEP
- Reexecute o comando de sync em horario diferente.
- Em producao, mantenha o cache local `database/data/ibge_localidades.json` atualizado.
