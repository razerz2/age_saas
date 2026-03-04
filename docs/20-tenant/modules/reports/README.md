# Modulo: Reports (Tenant)

Padronizacao do modulo de relatorios do Tenant para Grid.js server-side com exportacoes Excel/PDF.

## Arquivos deste modulo

- `overview.md` - objetivo, escopo e status da migracao.
- `routes.md` - endpoints `/workspace/{slug}/reports/*`.
- `views.md` - telas Blade e partials de coluna/acoes/PDF.
- `backend.md` - controllers, query-base, payloads, exportacoes.
- `frontend.md` - `reports.js`, estado de filtros/search/sort/paginacao, exports.
- `database.md` - modelos e tabelas envolvidas por relatorio.
- `permissions.md` - regras de acesso e middlewares.
- `troubleshooting.md` - checklist de diagnostico e problemas comuns.

## Fontes consultadas

- `routes/tenant/reports.php`
- `app/Http/Controllers/Tenant/Reports/*.php`
- `resources/views/tenant/reports/**/*`
- `resources/js/tenant/pages/reports.js`
- `composer.json`
