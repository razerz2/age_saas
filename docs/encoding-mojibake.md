# Mojibake e encoding UTF-8

## O que e mojibake

Mojibake acontece quando um texto foi salvo em uma codificacao e lido como se fosse outra. Em portugues, isso costuma transformar textos como `Não`, `Você`, `configuração` e `horário` em sequencias quebradas de bytes exibidas como caracteres incorretos.

Este projeto possui comandos Artisan para detectar e corrigir esse problema de forma repetivel, com dry-run por padrao e escrita somente quando `--write` for informado.

## Diagnosticar

Diagnostico geral:

```bash
php artisan encoding:mojibake-check
```

Diagnostico em views:

```bash
php artisan encoding:mojibake-check --path=resources/views
```

O comando retorna exit code `1` quando encontra arquivos suspeitos e `0` quando nao encontra mojibake.

## Corrigir em dry-run

O comando de correcao nao altera arquivos por padrao. Ele apenas mostra quais arquivos seriam alterados:

```bash
php artisan encoding:mojibake-fix
```

Dry-run em views:

```bash
php artisan encoding:mojibake-fix --path=resources/views
```

## Corrigir gravando arquivos

Antes de rodar com escrita, faca um commit ou garanta que o `git diff` esta revisado. Use `--backup` para salvar uma copia dos arquivos originais em `storage/app/encoding-backups/YYYYmmdd-His/`.

Correcao em views com backup:

```bash
php artisan encoding:mojibake-fix --path=resources/views --write --backup
```

Correcao geral com backup:

```bash
php artisan encoding:mojibake-fix --write --backup
```

## Limitar por pasta

Use `--path` para limitar a analise ou correcao:

```bash
php artisan encoding:mojibake-check --path=app
php artisan encoding:mojibake-fix --path=resources --write --backup
php artisan encoding:mojibake-fix --path=database --write --backup
```

## Relatorio JSON

Para salvar um relatorio estruturado:

```bash
php artisan encoding:mojibake-fix --write --backup --report=storage/app/mojibake-fix.json
```

Tambem e possivel gerar relatorio no diagnostico:

```bash
php artisan encoding:mojibake-check --report=storage/app/mojibake-check.json
```

## Validar depois

Revise as alteracoes:

```bash
git diff
```

Rode o diagnostico novamente:

```bash
php artisan encoding:mojibake-check
```

## Restaurar backup

Os backups preservam a estrutura relativa dos arquivos dentro de `storage/app/encoding-backups/YYYYmmdd-His/`. Para restaurar, copie o arquivo do backup para o mesmo caminho relativo no projeto. Depois valide com:

```bash
git diff
php artisan encoding:mojibake-check
```

## Pastas e arquivos ignorados

Por padrao, a ferramenta ignora:

- `vendor`
- `node_modules`
- `storage/framework`
- `storage/logs`
- `storage/app/public`
- `bootstrap/cache`
- `public/build`
- `public/hot`
- `public/vendor`
- `.git`
- `coverage`
- `.phpunit.cache`
- arquivos de lock como `composer.lock`, `package-lock.json`, `yarn.lock` e `pnpm-lock.yaml`
- arquivos binarios, imagens, fontes, PDFs, zips e arquivos maiores que 2 MB

## Por que Artisan em vez de PowerShell

Artisan roda dentro do contexto do Laravel, usa caminhos relativos ao `base_path()`, funciona em Windows e Linux, pode gerar relatorios JSON e aplica as mesmas regras de seguranca em qualquer ambiente. Isso evita scripts manuais diferentes por sistema operacional e reduz o risco de corrigir arquivos gerados, binarios ou grandes demais.

## Recomendacao

Sempre commite ou guarde o estado atual antes de rodar `--write`. Use primeiro o dry-run, revise a lista de arquivos, rode a correcao com `--write --backup` e finalize validando com `git diff` e `php artisan encoding:mojibake-check`.
