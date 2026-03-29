# Browser Dusk Packs

## Smoke pack (regressao critica)

Executa apenas os fluxos mais valiosos para detectar quebra operacional grave:

1. Login tenant
2. Paciente (criacao)
3. Agendamento (criacao)
4. Atendimento (conclusao)

Comando:

```bash
composer test:dusk-smoke
```

## Packs por modulo

```bash
composer test:dusk-patients
composer test:dusk-appointments
composer test:dusk-attendances
```

## Suite Browser completa

```bash
composer test:dusk-all
```

Observacao: os scripts Composer acima ja desabilitam o timeout padrao do Composer para evitar interrupcao em suites Dusk mais longas.
