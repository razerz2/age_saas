# 📚 Documentação do Projeto

Este diretório concentra a documentação estruturada do sistema de agendamento SaaS.

> Para o índice antigo desta pasta, consulte **[README_legacy.md](README_legacy.md)**.

## 🧭 Navegação por áreas

- **00-global**  → Padrões, arquitetura geral, convenções de código e frontend.
- **10-platform** → Área administrativa central (Platform).
- **20-tenant**   → Área do tenant (clínicas).
- **30-landing-page** → Landing page pública / marketing.
- **40-portal-paciente** → Portal do paciente.

### Links rápidos

- **[00-global/00-visao-geral.md](00-global/00-visao-geral.md)**
- **[10-platform/README.md](10-platform/README.md)**
- **[20-tenant/README.md](20-tenant/README.md)**
- **[30-landing-page/README.md](30-landing-page/README.md)**
- **[40-portal-paciente/README.md](40-portal-paciente/README.md)**

---

## 🧩 Convenções

### Estrutura de pastas

- Diretórios numerados definem o agrupamento lógico:
  - `00-global/` → conteúdo transversal a todas as áreas.
  - `10-*/`, `20-*/`, `30-*/`, `40-*/` → áreas principais do produto.
- Cada área possui:
  - `README.md` com visão geral da área.
  - Arquivos numerados (`01-*.md`, `02-*.md`, ...) para tópicos internos.
  - Um diretório `modules/` reservado para documentação por módulo (Etapa 2+).

### Nome de arquivos

- Prefixo numérico (`01-`, `02-`, ...) define a ordem de leitura.
- Sufixo descritivo em **kebab-case** (ex.: `01-visao-geral.md`, `03-estrutura-de-pastas.md`).
- Para rascunhos ou versões alternativas usar:
  - `*-new.md` ou `*_draft.md`.

### Como adicionar um novo módulo (Etapas futuras)

1. Escolha a área (`10-platform`, `20-tenant`, `30-landing-page`, `40-portal-paciente`).
2. Dentro de `modules/`, crie a pasta do módulo usando o nome canônico (ex.: `patients`, `appointments`).
3. Use o template de módulo em `./_templates/module-template.md` como guia de estrutura.
4. Atualize o `README.md` da área para incluir o novo módulo na lista.

> **Importante:** Nesta etapa estamos apenas criando a estrutura base. A documentação detalhada de módulos virá em etapas futuras.

### Como registrar mudanças de documentação

1. Edite `docs/CHANGELOG.md`.
2. Adicione uma nova seção com a data no formato `YYYY-MM-DD`.
3. Liste, em bullet points, as mudanças relevantes na documentação (arquivos criados/alterados).

Exemplo:

```markdown
2026-02-22
- Criada estrutura base de documentação em /docs.
- Adicionados templates em /docs/_templates.
```

---

## Como contribuir

Para novo módulo, copie `docs/_templates/module-template.md` e crie pasta em `<area>/modules/<modulo>/`.

## Rascunhos

- Os arquivos de rascunho ficam em `docs/_drafts/README.md`.
- Nota: vazio por enquanto.

---

## 📎 Referências legadas

Parte da documentação histórica permanece em arquivos na raiz do projeto:

- `ARQUITETURA.md`
- `PLATFORM.md`
- `TENANT.md`

Esses documentos continuarão válidos e serão, aos poucos, consolidados dentro da nova estrutura (`00-global/*` e `*/modules/*`).

Para o índice legacy desta pasta, veja **[README_legacy.md](README_legacy.md)**.

