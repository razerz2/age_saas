# Comandos Artisan (Tenant)

Este documento centraliza os comandos Artisan relevantes para operacao, QA e manutencao na area **Tenant** (clinicas) do sistema multi-tenant.

## Regras Gerais (Seguranca)

- **Multi-tenant**: comandos que aceitam `--tenant=<slug>` e/ou `--all-tenants` alternam o contexto do tenant antes de ler/gravar.
- **Producao**: comandos de geracao/purge de dados de teste bloqueiam por padrao em `APP_ENV=production` e exigem `--force`.
- **Purge**: comandos destrutivos devem rodar com `--confirm` (ou confirmacao interativa). Em modo nao interativo, `--confirm` e obrigatorio.
- **Sempre valide antes**: use `--dry-run` quando existir para ver contagens/impacto antes de efetivar.

## Comandos de QA/Teste (Dados Ficticios)

### Pacientes

#### `tenant:patients:seed`

Gera pacientes ficticios (PT-BR) na tabela `patients` do(s) tenant(s) selecionado(s), marcando como teste para permitir remocao segura.

Opcoes:
- `--tenant=<slug>` executa em um tenant especifico
- `--all-tenants` executa em todos os tenants
- `--count=50` quantidade por tenant
- `--tag=test` tag do lote de teste
- `--force` libera execucao em producao

Exemplos:
- `php artisan tenant:patients:seed --tenant=clinica_boavida --count=200`
- `php artisan tenant:patients:seed --all-tenants --count=50`

Marcacao de teste:
- Preferencial: colunas `is_test` e `test_tag` em `patients` (ver migration abaixo).

#### `tenant:patients:purge-test`

Apaga **somente** pacientes de teste (por `is_test`/`test_tag`) no(s) tenant(s) selecionado(s). Por padrao, se houver bloqueio por FK, o comando **nao** remove relacionados; use `--cascade` se existir no comando.

Opcoes:
- `--tenant=<slug>` ou `--all-tenants`
- `--tag=test`
- `--confirm` obrigatorio para efetivar sem prompt interativo
- `--cascade` remove dependencias principais antes (desabilitado por padrao)
- `--force` libera execucao em producao

Exemplos:
- `php artisan tenant:patients:purge-test --tenant=clinica_boavida --confirm`
- `php artisan tenant:patients:purge-test --all-tenants --tag=test --confirm`

### Agendamentos

Antes de usar, garanta que os tenants aplicaram a migration que adiciona marcadores de teste em `appointments` (ver "Migrations de suporte" abaixo).

#### `tenant:appointments:seed-random`

Gera agendamentos ficticios em um periodo, sorteando medico/paciente/especialidade/tipo e escolhendo slots validos (business hours + sem overlap + bloqueio por recorrencias).

Opcoes:
- `--tenant=<slug>` ou `--all-tenants`
- `--count=50` quantidade por tenant
- `--days=14` janela padrao (proximos N dias)
- `--start-date=YYYY-MM-DD` (opcional) inicio do range
- `--end-date=YYYY-MM-DD` (opcional) fim do range
- `--duration=30` fallback em minutos quando nao houver `appointment_type.duration_min`
- `--tag=test` tag do lote
- `--dry-run` simula sem gravar
- `--force` libera execucao em producao

Exemplos:
- `php artisan tenant:appointments:seed-random --tenant=clinica_boavida --count=200 --days=30`
- `php artisan tenant:appointments:seed-random --all-tenants --count=50 --dry-run`

Saida por tenant:
- `Solicitados`, `Criados`, `Tentativas`, `NoSlot`, `NoDoctor`, `NoPatient`, `Erros`, `Tempo`.

#### `tenant:appointments:seed-date`

Mesmo comportamento do random, mas fixando o dia.

Opcoes:
- `--tenant=<slug>` ou `--all-tenants`
- `--date=YYYY-MM-DD` (obrigatorio)
- `--count=50`
- `--duration=30`
- `--tag=test`
- `--dry-run`
- `--force`

Exemplo:
- `php artisan tenant:appointments:seed-date --tenant=clinica_boavida --date=2026-03-10 --count=80`

#### `tenant:appointments:purge-test`

Apaga **somente** agendamentos marcados como teste. Mostra contagem por tenant antes e exige confirmacao.

Opcoes:
- `--tenant=<slug>` ou `--all-tenants`
- `--tag=test`
- `--dry-run` mostra contagens e nao apaga
- `--confirm` obrigatorio para efetivar sem prompt interativo
- `--cascade` remove relacionados (ex.: `online_appointment_instructions`, `calendar_sync_state`, `form_responses`) antes de apagar `appointments`
- `--force` libera execucao em producao

Exemplos:
- `php artisan tenant:appointments:purge-test --tenant=clinica_boavida --confirm`
- `php artisan tenant:appointments:purge-test --all-tenants --tag=test --confirm --dry-run`

## Migrations de Suporte (Marcacao de Teste)

Os comandos de QA acima preferem colunas dedicadas para garantir purge seguro.

- Pacientes: `database/migrations/tenant/2026_02_27_120000_add_is_test_and_test_tag_to_patients_table.php`
- Agendamentos: `database/migrations/tenant/2026_02_27_130000_add_is_test_and_test_tag_to_appointments_table.php`

Aplicacao em tenants:
- `php artisan tenant:migrate --all`
- `php artisan tenant:migrate --tenant=<slug>`

## Comandos Operacionais (Ja Existentes)

Esta lista e uma referencia rapida. Para opcoes/parametros detalhados, rode `php artisan <comando> --help`.

### Tenant (multi-tenant e suporte)

- `tenant:migrate` Executa migrations pendentes nos bancos dos tenants
- `tenants:migrate-all` Executa migrations pendentes em TODAS as tenants existentes
- `tenant:seed-genders` Executa o seeder de generos para uma tenant especifica ou todas
- `tenant:seed-specialties` Executa o seeder de especialidades medicas para uma tenant especifica
- `tenant:reset-admin-password` Redefine a senha do usuario admin de um tenant
- `tenant:fix-password` Corrige ou redefine a senha de um usuario do tenant
- `tenant:fix-db-password` Atualiza a senha do usuario PostgreSQL do tenant
- `tenant:diagnose` Diagnostica problemas de login para um tenant especifico
- `tenant:test-login` Testa o login de um usuario em um tenant

### Agendamentos (rotinas)

- `appointments:expire-pending` Expira agendamentos pendentes de confirmacao cujo prazo venceu
- `appointments:expire-waitlist-offers` Expira ofertas de waitlist vencidas e oferta o slot para o proximo paciente
- `appointments:mark-overdue` Marca appointments vencidos (scheduled/rescheduled) como no_show
- `appointments:notify-upcoming` Envia lembretes de agendamentos proximos aos pacientes via email e WhatsApp
- `recurring-appointments:process` Processa agendamentos recorrentes e gera sessoes automaticamente

### Campanhas/Notificacoes

- `campaigns:run-automated` Run automated campaigns for eligible tenants

### Financeiro/Faturas (cuidado)

- `finance:health-check` Verifica saude do modulo financeiro
- `finance:reconcile` Reconcilia cobrancas financeiras com o Asaas
- `invoices:generate` Gera faturas automaticamente
- `invoices:notify-upcoming` Notifica tenants sobre faturas proximas do vencimento
- `invoices:invoices-check-overdue` Marca faturas vencidas e suspende tenants imediatamente
- `invoices:invoices-clear` Apaga todas as faturas do Asaas e do banco local (modo testes)
- `invoices:clear-asaas-invoices` Apaga TODAS as faturas diretamente no Asaas (modo manutencao/testes)
- `tenants:clear-asaas` Apaga todos os clientes (tenants) no Asaas e suas faturas locais (modo testes)

### Manutencao/Diagnostico

- `email:test` Testa a configuracao de email enviando um email de teste
- `asaas:generate-token` Gera uma nova chave de autenticacao para o webhook Asaas e atualiza o .env
- `test:session-expiration` Limpa sessoes para testar redirecionamento quando sessao expira

## Agendador (Scheduler)

Alguns comandos rodam automaticamente via scheduler. Para ver o estado atual:
- `php artisan schedule:list`

Referencia de definicao:
- `app/Console/Kernel.php` (agenda, por exemplo, `appointments:expire-pending`, `appointments:mark-overdue`, `campaigns:run-automated`).

