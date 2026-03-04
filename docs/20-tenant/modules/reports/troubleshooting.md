# Reports - Troubleshooting

## 1) Grid nao carrega

Checklist:

- Confirmar `data-grid-url` na view do relatorio.
- Confirmar rota `.../grid-data` em `routes/tenant/reports.php`.
- Confirmar retorno JSON com `data` e `meta.total`.
- Verificar erro de autenticacao/tenant no network (`401`, `403`, `500`).

## 2) Exportacao nao respeita filtros

Checklist:

- Conferir querystring atual da pagina (`search`, `sort`, `dir`, filtros custom).
- Conferir se botao usa `data-export-format`.
- Validar no controller se `buildBaseQuery` + `applySearch` + `applySort` sao chamados em export.

## 3) Excel falha no ambiente local

`maatwebsite/excel` depende de `phpoffice/phpspreadsheet`, que requer extensao `ext-gd`.
Se ausente no CLI, ajuste o `php.ini` para habilitar `gd`.

## 4) PDF muito grande/lento

Os controllers limitam exportacao PDF em `PDF_MAX_ROWS` (5000) por seguranca.
Para volumes maiores, usar Excel.

## 5) Relatorio do portal sem dados

Se a tabela `patient_logins` nao existir no tenant:

- tela mostra aviso
- `gridData` retorna vazio
- exportacoes retornam sem linhas

## 6) Sort/search sem efeito

- Verificar se ids de colunas no JS (`reports.js`) estao mapeados no backend (`resolveSort`).
- Verificar se `search` esta sendo aplicado em `applySearch()` do controller correto.
