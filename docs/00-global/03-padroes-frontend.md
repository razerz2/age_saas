# Padrões de Frontend (Global)

Este documento concentra padrões gerais de frontend aplicáveis às áreas Platform, Tenant, Landing Page e Portal do Paciente.

## Estrutura geral

- Uso de Blade para views server‑side.
- Assets organizados por área (ex.: `resources/css/tenant`, `resources/js/tenant`).
- Preferência por componentes reutilizáveis quando existir suporte (ex.: componentes Blade).

## Padrões de UI

- Layouts base definidos por área (ver documentação específica de cada área).
- Padrões de formulários, grids e navegação documentados inicialmente em:
  - `TENANT.md` (padrão de views, Grid.js no Tenant, formulários Tenant).

## Dark mode

- Suporte a tema claro/escuro nas áreas que já foram migradas.
- Overrides de CSS devem ser escopados por área/módulo, evitando estilos globais.

## Referências

- `ARQUITETURA.md` → seção "Frontend Tenant (Views/Assets)".
- `TENANT.md` → seções de padrão de views, Grid.js e formulários.

> Este arquivo funciona como índice de padrões. Detalhes finos continuam nos documentos das áreas/módulos.
