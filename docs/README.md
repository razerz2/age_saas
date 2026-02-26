# Documentacao do Projeto

Este diretorio concentra a documentacao estruturada do sistema de agendamento SaaS.

> Para o indice legado desta pasta, consulte **[_legacy/README_legacy.md](_legacy/README_legacy.md)**.

## Navegacao por areas

- **00-global**: [00-global/00-visao-geral.md](00-global/00-visao-geral.md)
- **10-platform**: [10-platform/README.md](10-platform/README.md)
- **20-tenant**: [20-tenant/README.md](20-tenant/README.md)
- **30-landing-page**: [30-landing-page/README.md](30-landing-page/README.md)
- **40-portal-paciente**: [40-portal-paciente/README.md](40-portal-paciente/README.md)

## Atalhos por dominio

### 10-platform

- Guides: [10-platform/guides/](10-platform/guides)
- Integrations: [10-platform/integrations/](10-platform/integrations)
- Operations: [10-platform/operations/](10-platform/operations)
- Modules: [10-platform/modules/README.md](10-platform/modules/README.md)

### 20-tenant

- Guides: [20-tenant/guides/](20-tenant/guides)
- Integrations: [20-tenant/integrations/](20-tenant/integrations)
- Finance: [20-tenant/finance/README.md](20-tenant/finance/README.md)
- Troubleshooting: [20-tenant/troubleshooting/](20-tenant/troubleshooting)
- Operations: [20-tenant/operations/](20-tenant/operations)
- Modules: [20-tenant/modules/](20-tenant/modules)

### 30-landing-page

- Guides: [30-landing-page/guides/](30-landing-page/guides)
- Modules: [30-landing-page/modules/README.md](30-landing-page/modules/README.md)

### 40-portal-paciente

- Guides: [40-portal-paciente/guides/](40-portal-paciente/guides)
- Modules: [40-portal-paciente/modules/README.md](40-portal-paciente/modules/README.md)

## Convencoes

- Diretorios numerados (`10-`, `20-`, `30-`, `40-`) representam dominios.
- `README.md` de cada dominio funciona como indice local.
- A documentacao por modulo fica em `<dominio>/modules/<modulo>/`.
- O padrao de modulo usa 9 arquivos:
  - `README.md`, `overview.md`, `routes.md`, `views.md`, `backend.md`, `frontend.md`, `database.md`, `permissions.md`, `troubleshooting.md`.

## Changelog da documentacao

Registre mudancas em [CHANGELOG.md](CHANGELOG.md) com data `YYYY-MM-DD`.

## Rascunhos e templates

- Rascunhos: `docs/_drafts/`
- Templates: `docs/_templates/`

## Referencias legadas

Documentos historicos permanecem na raiz do repositorio:

- `ARQUITETURA.md`
- `PLATFORM.md`
- `TENANT.md`

O indice historico desta pasta esta em [_legacy/README_legacy.md](_legacy/README_legacy.md).
