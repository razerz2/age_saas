# Troubleshooting

## Modulo vazio (sem templates)

- rode os seeders da Platform:
  - `php artisan db:seed`
  - ou `php artisan db:seed --class=Database\\Seeders\\NotificationTemplatesSeeder`

## Seeder nao cria templates novos

- comportamento esperado: `NotificationTemplatesSeeder` usa `firstOrCreate` (nao duplica e nao sobrescreve registros existentes).

## Um template baseline reapareceu apos `db:seed`

- se a `key` de um template baseline foi renomeada manualmente na edicao, o seeder pode recriar a `key` original ausente.

## Teste de envio falha

- valide a configuracao de email (SMTP/driver) do ambiente;
- valide o email de destino no modal;
- se o `body` nao for HTML, o envio de teste converte quebras de linha para HTML (fallback).

