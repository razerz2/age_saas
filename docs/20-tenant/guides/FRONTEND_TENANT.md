# Documentário do Frontend do Tenant (Workspace + Público)

Este documento descreve o frontend **atual** do tenant no projeto `age_saas`: estrutura de pastas, navegação/rotas, layout base, assets (CSS/JS), personalização por tenant, e um inventário **view por view**.

## Visão geral (como o tenant “aparece” no navegador)

O frontend do tenant é majoritariamente **Laravel Blade** com um tema admin chamado **Connect Plus**, carregando assets estáticos via `asset()` a partir de `public/connect_plus/`.

Existem 3 “faces” principais:

- **Área autenticada do tenant (Workspace)**: URLs sob `/workspace/{slug}/...`
  - Views em `resources/views/tenant/**`
  - Layout base: `resources/views/layouts/connect_plus/app.blade.php`
- **Login do tenant**: URLs sob `/customer/{slug}/login` (e compatibilidade `/t/{slug}/login`)
  - Views em `resources/views/tenant/auth/**`
- **Área pública (agendamento e formulários)**: URLs sob `/customer/{slug}/agendamento/...` e `/customer/{slug}/formulario/...`
  - Views em `resources/views/tenant/public/**`
  - Importante: várias páginas públicas são HTML “full page” e não estendem o layout do workspace.

## Rotas e convenções de URL (tenant)

As rotas do tenant ficam em `routes/tenant.php`. Padrões:

- **Público**: `Route::prefix('customer/{slug}')->as('public.')->middleware(['tenant-web'])`
- **Login**: `Route::prefix('customer/{slug}')->as('tenant.')->middleware(['tenant-web'])`
- **Autenticado (workspace)**: `Route::prefix('workspace/{slug}')->as('tenant.')->middleware([... tenant.auth ...])`

### Helper de rota do workspace (padrão do frontend)

O projeto usa o helper `workspace_route()` para evitar repetir o `slug` em todas as views:

- Arquivo: `app/Helpers/helpers.php`
- Comportamento: tenta obter o `slug` do tenant do `request()->route('slug')`, do segmento da URL, da sessão, ou do tenant atual; injeta automaticamente em `route(...)`.

Recomendação prática: **em views do workspace, prefira** `workspace_route('tenant.algo')` em vez de `route('tenant.algo', ['slug' => ...])`.

## Layout e tema (Connect Plus)

### Layout principal do workspace

- **Layout base**: `resources/views/layouts/connect_plus/app.blade.php`
  - Inclui CSS do tema (vendors + `style.css`)
  - Inclui DataTables via CDN
  - Carrega `public/css/tenant-sidebar-fixed.css`
  - Expõe stacks para extensão:
    - `@stack('styles')`
    - `@stack('scripts')`
  - Inclui os parciais:
    - `layouts.connect_plus.navbar`
    - `layouts.connect_plus.navigation`

### Parciais do layout

- `resources/views/layouts/connect_plus/navbar.blade.php`
  - Logo/Logo mini com fallback (custom do tenant → padrão tenant na plataforma → padrão do sistema)
  - Link de “Ajuda/Manual”
  - Inclui `notifications` e `profile`
- `resources/views/layouts/connect_plus/navigation.blade.php`
  - Menu lateral com:
    - controle de módulos via `has_module(...)` e perfil/role
    - labels customizáveis via `professional_label_*()`
    - lógica de “Calendário” unificado vs menu separado (conforme role e quantidade de médicos associados)
- `resources/views/layouts/connect_plus/notifications.blade.php`
- `resources/views/layouts/connect_plus/profile.blade.php`
- Erros: `resources/views/layouts/connect_plus/error/error-404.blade.php` e `error-500.blade.php`

## Assets CSS/JS (o que o tenant realmente carrega)

### Vite existe, mas não é o “runtime” do tenant

O repo possui Vite configurado (`vite.config.js` com entradas `resources/css/app.css` e `resources/js/app.js`), porém o layout do tenant (`connect_plus/app.blade.php`) **não usa** `@vite(...)`. Na prática, o tenant carrega assets do tema e CSS/JS em `public/`.

### CSS do tenant

Local: `public/css/` (carregados conforme a página, via `@push('styles')` ou diretamente no layout).

Principais arquivos observados:

- `tenant-sidebar-fixed.css` (sempre no layout)
- `tenant-dashboard.css`
- `tenant-common.css`
- `tenant-users.css`
- `tenant-doctors.css`
- `tenant-appointments.css`
- `tenant-calendar.css`
- `tenant-forms.css`
- `tenant-business-hours.css`
- `tenant-medical-appointments.css`
- `tenant-settings.css`

### JS do tenant

1) **Vendor/tema (Connect Plus)** em `public/connect_plus/assets/`:

- jQuery/Bootstrap bundle (`vendor.bundle.base.js`)
- Chart.js (para dashboard)
- scripts do tema (`off-canvas.js`, `hoverable-collapse.js`, `misc.js`, `dashboard.js`)
- DataTables via CDN

2) **Custom JS** em `public/js/`:

- `password-generator.js`
  - `generateStrongPassword()`
  - `initPasswordGenerator(passwordFieldId, confirmFieldId = null)`

3) **JS inline em views**

Padrão: diversas páginas usam `<script> ... </script>` e/ou `@push('scripts')` para lógicas específicas (máscaras, fetch para APIs internas/públicas, inicialização de DataTables, modais, etc).

### JS global do layout (comportamento do menu)

No `connect_plus/app.blade.php` há scripts inline para:

- persistir estado do menu “retraído” (`sidebar-icon-only`) em `localStorage`
- bloquear dropdown/collapse quando o menu está retraído e criar popover de submenu

## Personalização por tenant (branding e labels)

### Branding (logo / logo mini / favicon)

Usa `TenantSetting` (tabela/config do tenant) com chaves:

- `appearance.logo`
- `appearance.logo_mini`
- `appearance.favicon`

**Aplicação no HTML:**

- Navbar (`layouts/connect_plus/navbar.blade.php`): resolve logo e logo mini com fallback
- Layout (`layouts/connect_plus/app.blade.php`): resolve favicon com fallback

**Upload/remoção:**

- Controller: `app/Http/Controllers/Tenant/SettingsController.php` (método `updateAppearance`)
- Armazenamento em disco: `storage/app/public/tenant/{tenant_id}/...` (ex.: `tenant/<uuid>/logo_...png`)

### Labels de “profissionais” (Médico/Profissional/etc)

O menu e algumas telas usam helpers do tipo `professional_label_singular()`/`plural()`/`registration_label()`.

As chaves de configuração (TenantSetting) relacionadas:

- `professional.customization_enabled`
- `professional.label_singular`
- `professional.label_plural`
- `professional.registration_label`

Gerenciamento: `SettingsController` (métodos de atualização de profissionais).

## Estrutura de views do tenant (inventário view por view)

As views do tenant ficam em `resources/views/tenant/`.

### Estrutura (árvore)

```
resources/views/tenant/
├── appointment-types/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── appointments/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── partials/appointment_mode_select.blade.php
│   └── recurring/
│       ├── cancel.blade.php
│       ├── create.blade.php
│       ├── edit.blade.php
│       ├── index.blade.php
│       └── show.blade.php
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── two-factor-challenge.blade.php
├── business-hours/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── calendar-sync/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── calendars/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── events.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── dashboard/
│   └── index.blade.php
├── dashboard.blade.php
├── doctor-settings/
│   └── index.blade.php
├── doctors/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── finance/
│   ├── accounts/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── categories/
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── charges/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── commissions/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── reports/
│   │   ├── cashflow.blade.php
│   │   ├── charges.blade.php
│   │   ├── commissions.blade.php
│   │   ├── income_expense.blade.php
│   │   ├── index.blade.php
│   │   └── payments.blade.php
│   └── transactions/
│       ├── create.blade.php
│       ├── edit.blade.php
│       ├── index.blade.php
│       └── show.blade.php
├── forms/
│   ├── builder.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   ├── preview.blade.php
│   ├── show.blade.php
│   ├── options/create.blade.php
│   ├── questions/create.blade.php
│   ├── sections/create.blade.php
│   └── partials/
│       ├── preview-question.blade.php
│       └── question-item.blade.php
├── integrations/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── google/index.blade.php
│   └── apple/
│       ├── connect.blade.php
│       └── index.blade.php
├── medical_appointments/
│   ├── index.blade.php
│   ├── session.blade.php
│   └── partials/
│       ├── details.blade.php
│       ├── error.blade.php
│       └── form-response-modal.blade.php
├── notifications/
│   ├── index.blade.php
│   └── show.blade.php
├── oauth-accounts/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── online_appointments/
│   ├── index.blade.php
│   └── show.blade.php
├── patient_portal/
│   ├── dashboard.blade.php
│   ├── auth/login.blade.php
│   └── layouts/
│       ├── app.blade.php
│       ├── auth.blade.php
│       ├── navbar.blade.php
│       ├── navigation.blade.php
│       ├── notifications.blade.php
│       └── profile.blade.php
├── patients/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── login-form.blade.php
│   ├── login-show.blade.php
│   └── emails/login-credentials.blade.php
├── plan-change-request/create.blade.php
├── profile/
│   ├── edit.blade.php
│   └── two-factor/index.blade.php
├── public/
│   ├── patient-identify.blade.php
│   ├── patient-register.blade.php
│   ├── appointment-create.blade.php
│   ├── appointment-show.blade.php
│   ├── appointment-success.blade.php
│   ├── form-response-create.blade.php
│   ├── form-response-success.blade.php
│   └── partials/form-question.blade.php
├── reports/
│   ├── index.blade.php
│   ├── appointments/index.blade.php
│   ├── appointments/partials/filters.blade.php
│   ├── doctors/index.blade.php
│   ├── patients/index.blade.php
│   ├── forms/index.blade.php
│   ├── portal/index.blade.php
│   ├── recurring/index.blade.php
│   └── notifications/index.blade.php
├── responses/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── settings/
│   ├── index.blade.php
│   ├── finance.blade.php
│   └── public-booking-link.blade.php
├── specialties/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
├── subscription/show.blade.php
├── user-doctor-permissions/index.blade.php
└── users/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    ├── show.blade.php
    └── change-password.blade.php
```

### “View por view” (descrição curta por módulo)

Observação: todas as telas abaixo são “tenant workspace” **a menos** que explicitamente marcadas como **(público)** ou **(portal do paciente)**.

#### `auth/`

- `login.blade.php`: tela de login do tenant (rota `/customer/{slug}/login`).
- `register.blade.php`: cadastro (se habilitado).
- `two-factor-challenge.blade.php`: desafio de 2FA.

#### `dashboard/`

- `dashboard/index.blade.php`: dashboard principal (cards + gráficos Chart.js; usa `@push('styles')` para `tenant-dashboard.css` e `@push('scripts')` para inicialização dos gráficos).
- `dashboard.blade.php`: dashboard “simples/legado” (cards estáticos). Em geral, **não** é o entrypoint atual (o entrypoint padrão aponta para `dashboard/index.blade.php`).

#### `users/` (admin)

- `index.blade.php`: listagem de usuários (tipicamente com DataTables).
- `create.blade.php`: criação de usuário (inclui upload de avatar e modal de webcam; contém JS inline para UX).
- `edit.blade.php`: edição de usuário.
- `show.blade.php`: detalhes do usuário.
- `change-password.blade.php`: troca de senha do usuário.

#### `user-doctor-permissions/`

- `index.blade.php`: gerenciamento das permissões (médicos permitidos) por usuário.

#### `doctors/` (profissionais)

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `specialties/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `patients/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.
- `login-form.blade.php`: formulário para credenciais/acesso do paciente (fluxos internos).
- `login-show.blade.php`: tela de resultado/visualização do login do paciente.
- `emails/login-credentials.blade.php`: template de e-mail com credenciais.

#### `appointments/`

- `index.blade.php`: listagem e filtros.
- `create.blade.php`: criação manual.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.
- `partials/appointment_mode_select.blade.php`: parcial reutilizável para seleção do modo (presencial/online) conforme configuração.

##### `appointments/recurring/`

- `index.blade.php`: lista de recorrências.
- `create.blade.php`: criar recorrência.
- `edit.blade.php`: editar recorrência.
- `show.blade.php`: detalhes.
- `cancel.blade.php`: cancelamento.

#### `online_appointments/`

- `index.blade.php`: listagem/entrada.
- `show.blade.php`: detalhes/sessão.

#### `medical_appointments/`

- `index.blade.php`: lista/sessões.
- `session.blade.php`: tela de atendimento (fluxo de sessão do dia).
- `partials/details.blade.php`: parcial de detalhes.
- `partials/error.blade.php`: parcial de erro.
- `partials/form-response-modal.blade.php`: modal para resposta/formulário durante o atendimento.

#### `calendars/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.
- `events.blade.php`: agenda/eventos (normalmente para médicos).

#### `doctor-settings/`

- `index.blade.php`: tela unificada de configurações do médico (quando a UI decide mostrar “1 página só” em vez de menu separado).

#### `business-hours/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `appointment-types/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `forms/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.
- `preview.blade.php`: pré-visualização.
- `builder.blade.php`: builder (montagem do formulário).
- `partials/question-item.blade.php` e `partials/preview-question.blade.php`: peças reutilizadas no builder/preview.
- `options/create.blade.php`: criação de opção.
- `questions/create.blade.php`: criação de pergunta.
- `sections/create.blade.php`: criação de seção.

#### `responses/`

- `index.blade.php`: listagem de respostas.
- `create.blade.php`: criação manual (se aplicável).
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `integrations/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.
- `google/index.blade.php`: tela específica da integração Google.
- `apple/index.blade.php`: tela específica da integração Apple.
- `apple/connect.blade.php`: fluxo de conexão Apple.

#### `oauth-accounts/`

- `index.blade.php`: listagem.
- `create.blade.php`: criação.
- `edit.blade.php`: edição.
- `show.blade.php`: detalhes.

#### `calendar-sync/`

- `index.blade.php`: listagem.
- `create.blade.php`: criar estado/integração.
- `edit.blade.php`: editar.
- `show.blade.php`: detalhes.

#### `notifications/`

- `index.blade.php`: listagem.
- `show.blade.php`: detalhes.

#### `reports/`

- `index.blade.php`: “hub” de relatórios.
- `appointments/index.blade.php`: relatório de agendamentos.
- `appointments/partials/filters.blade.php`: filtros do relatório.
- `doctors/index.blade.php`: relatório de médicos.
- `patients/index.blade.php`: relatório de pacientes.
- `forms/index.blade.php`: relatório de formulários.
- `portal/index.blade.php`: relatório do portal.
- `recurring/index.blade.php`: relatório de recorrência.
- `notifications/index.blade.php`: relatório de notificações.

#### `finance/`

Módulo financeiro do tenant, com submódulos:

- `accounts/*`: contas financeiras (index/create/edit/show)
- `categories/*`: categorias (index/create/edit/show)
- `transactions/*`: transações (index/create/edit/show)
- `charges/*`: cobranças (index/show)
- `commissions/*`: comissões (index/show)
- `reports/*`: relatórios financeiros (cashflow, charges, commissions, income_expense, payments, index)

#### `settings/`

- `index.blade.php`: configurações gerais (inclui abas/sections e, tipicamente, JS para busca de dados auxiliares como CEP/estado/cidade).
- `finance.blade.php`: configurações do módulo financeiro.
- `public-booking-link.blade.php`: link/config do agendamento público.

#### `subscription/`

- `show.blade.php`: informações da assinatura/plano.

#### `plan-change-request/`

- `create.blade.php`: solicitação de troca de plano.

#### `patient_portal/` (portal do paciente)

- `dashboard.blade.php`: dashboard do portal.
- `auth/login.blade.php`: login do portal.
- `layouts/*`: layout próprio do portal (não é o mesmo do Connect Plus do workspace).

#### `public/` (público)

Fluxos de agendamento e formulários públicos do tenant:

- `patient-identify.blade.php`: identificar paciente.
- `patient-register.blade.php`: cadastro de paciente.
- `appointment-create.blade.php`: criar agendamento (carrega dados via `fetch` em rotas `/customer/{slug}/agendamento/api/...`).
- `appointment-show.blade.php`: exibir agendamento.
- `appointment-success.blade.php`: sucesso.
- `form-response-create.blade.php`: responder formulário.
- `form-response-success.blade.php`: sucesso resposta.
- `partials/form-question.blade.php`: renderização de pergunta no formulário.

## Funções JS (onde estão e como são usadas)

### 1) `public/js/password-generator.js`

Funções:

- `generateStrongPassword()`: gera senha forte (12 chars, com tipos variados).
- `initPasswordGenerator(passwordFieldId, confirmFieldId)`: injeta botão “Gerar” e preenche campos.

Uso típico: telas de criação/edição de usuários (workspace).

### 2) Exemplo de JS por tela (público): `public/appointment-create.blade.php`

Essa view contém funções JS inline para:

- carregar calendários/tipos/especialidades ao selecionar médico
- carregar horários disponíveis por data (e tipo de consulta opcional)
- preencher hidden inputs (`starts_at`, `ends_at`, `calendar_id`)
- validar antes do submit

Além disso, há um modal “Dias trabalhados do médico” com conteúdo preenchido via JS.

### 3) Exemplo de JS por tela (workspace): `dashboard/index.blade.php`

Usa `Chart.js` para:

- gráfico de linha (últimos 12 meses)
- gráfico donut por especialidade

Os dados são serializados via `@json(...)` e a inicialização fica dentro de `@push('scripts')`.

## Checklist rápido para manutenção/evolução do frontend do tenant

- **Nova tela no workspace**: criar Blade em `resources/views/tenant/<modulo>/...`, apontar rota em `routes/tenant.php`, e usar `@extends('layouts.connect_plus.app')`.
- **CSS por tela**: criar `public/css/tenant-<modulo>.css` e incluir via `@push('styles')`.
- **JS por tela**: preferir `@push('scripts')` e, se virar reutilizável, mover para `public/js/...` (ou considerar integrar via Vite).
- **Personalização**: para branding/labels, usar `TenantSetting::get/set` e manter fallbacks via `sysconfig(...)`.

