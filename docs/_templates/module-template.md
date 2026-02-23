# Template de Módulo

> Use este template como referência para organizar a documentação completa de um **módulo** dentro de uma área.

## 1. README do Módulo (Mapa Rápido)
- Nome canônico do módulo.
- Em qual área ele vive (Platform, Tenant, Landing Page, Portal do Paciente).
- Lista de arquivos deste módulo (overview, routes, views, backend, etc.).
- Links rápidos para cada arquivo.

## 2. overview.md
- O que o módulo faz.
- Quem usa e em quais cenários.
- Fluxo principal de uso (do ponto de vista do usuário).

## 3. routes.md
- Rotas HTTP principais (paths, verbs, names).
- Middlewares relevantes (auth, guards, module.access, etc.).
- Observações importantes de ordem de rotas (quando aplicável).

## 4. views.md
- Lista de telas e partials principais (paths Blade).
- Componentes reutilizáveis da área.
- Padrões visuais específicos do módulo (linkar para `frontend.md` ou `docs/00-global/03-padroes-frontend.md` quando existirem).

## 5. backend.md
- Controllers envolvidos e responsabilidades.
- Form Requests e regras de validação.
- Services, Jobs, Observers relevantes.
- Regras de negócio importantes.

## 6. frontend.md
- JS por página (entrypoints, data-attributes relevantes).
- CSS/SCSS por página (arquivos, escopo, seletores canônicos).
- Comportamentos de UI importantes (ex.: row-click, máscaras, interação com Grid.js).

## 7. database.md
- Tabelas envolvidas.
- Relações principais.
- Migrations relevantes (paths) e seeders (se houver).

## 8. permissions.md
- Chaves canônicas de permissão (roles/modules) relacionadas ao módulo.
- Regras de acesso principais.

## 9. troubleshooting.md
- Problemas comuns.
- Passos de diagnóstico.
- Referências para docs globais de troubleshooting (`docs/00-global/07-troubleshooting.md`).
