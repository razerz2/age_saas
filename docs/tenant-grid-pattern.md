
## Padrão Oficial de Listagem Tenant (Grid Server-Side + TailAdmin)

Este documento define o **padrão obrigatório** para todas as telas de listagem do tenant utilizando **TailAdmin** e o componente Blade `<x-tenant.grid>` com **carregamento server-side**.

Aplica-se a **novas implementações** e à **migração gradual** de módulos antigos.

---

## 1. Padrão Oficial de Listagem (TailAdmin)

- **Obrigatório** usar o componente:

  ```blade
  <x-tenant.grid ... />
  ```

- **Proibido** em novas implementações e refatorações:
  - **DataTables** (jQuery DataTables ou similares)
  - **jQuery** (para grid / paginação / ordenação / busca)
  - **Tabelas Blade com `@foreach`** renderizando todos os registros
  - **Paginação Laravel tradicional na view** (`{{ $models->links() }}` etc.)

Todas as responsabilidades de **paginação, ordenação e filtros** devem ser delegadas ao **endpoint JSON** (`gridData`) consumido pelo `<x-tenant.grid>`.

---

## 2. Estrutura Obrigatória do `gridData()`

Todo controller responsável por listagem deve expor um método com a seguinte assinatura:

```php
public function gridData(Request $request, $slug)
```

### 2.1. Query base

- A query **sempre** deve partir do **Model principal** da listagem, com eager loading necessário:

```php
$query = Model::with([
    'relationship1',
    'relationship2.nestedRelationship',
]);
```

- Evitar `N+1` usando **`with()`** em todas as relações exibidas no grid.

### 2.2. Filtro por médico (`applyDoctorFilter` / `HasDoctorFilter`)

- Quando o módulo suportar filtro por médico (`doctor_id`):
  - Deve existir um método dedicado para aplicar esse filtro (ex.: `applyDoctorFilter($query, $request)` ou trait `HasDoctorFilter`).
  - A chamada do filtro deve acontecer **antes** da paginação:

```php
if ($request->filled('doctor_id')) {
    $this->applyDoctorFilter($query, $request->input('doctor_id'));
}
```

### 2.3. Paginação padrão

- Sempre normalizar os parâmetros de paginação e limitar `limit` entre **1 e 100**:

```php
$page  = max(1, (int) $request->input('page', 1));
$limit = max(1, min(100, (int) $request->input('limit', 10)));
```

### 2.4. Busca global (opcional)

- Quando houver busca global, ela deve ser aplicada na query usando um campo de texto ou múltiplos campos:

```php
if ($search = $request->input('search')) {
    $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
    });
}
```

### 2.5. Whitelist de ordenação (obrigatória)

- **Nunca** usar `orderBy` diretamente com valores vindos do request.
- Sempre definir um mapa de campos ordenáveis:

```php
$sortable = [
    'campo_grid'   => 'campo_database',
    'created_at'   => 'created_at',
    'doctor_name'  => 'doctors.name',
];

$sortField = $request->input('sort_field');
$sortDir   = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

if (isset($sortable[$sortField])) {
    $query->orderBy($sortable[$sortField], $sortDir);
} else {
    // Fallback seguro de ordenação
    $query->orderBy('created_at', 'desc');
}
```

### 2.6. Paginar e montar estrutura JSON

- A paginação deve usar `paginate()` com página e limite normalizados:

```php
$paginator = $query->paginate($limit, ['*'], 'page', $page);

$data = $paginator->getCollection()->map(function ($model) {
    return [
        // Campos simples
        'id'          => $model->id,
        'name'        => $model->name,

        // Campos HTML via partials (ver seção 5)
        // 'status_badge' => view('tenant.module.partials.status', compact('model'))->render(),
    ];
});

return response()->json([
    'data' => $data,
    'meta' => [
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'per_page'     => $paginator->perPage(),
        'total'        => $paginator->total(),
    ],
]);
```

Essa estrutura de resposta JSON é **padrão e obrigatória** para o `<x-tenant.grid>`.

---

## 3. Whitelist de Ordenação (Obrigatório)

- **Regra de segurança:** nunca confiar em valores de ordenação vindos diretamente do request.

- Sempre definir um array `$sortable`:

```php
$sortable = [
    'campo_grid'  => 'tabela.campo_database',
    'outro_campo' => 'outra_tabela.coluna',
];
``;

- Se o campo solicitado **não existir** no whitelist, **não** aplicar ordenação personalizada e usar o fallback padrão (por exemplo, `created_at desc`).

- A direção (`asc` / `desc`) deve ser validada e normalizada:

```php
$sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
```

---

## 4. Estrutura de Pastas Padrão (Views Tenant)

### 4.1. Exemplo: módulo `appointments`

```text
resources/views/tenant/appointments/index.blade.php
resources/views/tenant/appointments/partials/status.blade.php
resources/views/tenant/appointments/partials/actions.blade.php
```

### 4.2. Exemplo: módulo `form_responses`

```text
resources/views/tenant/form_responses/index.blade.php
resources/views/tenant/form_responses/partials/actions.blade.php
```

### 4.3. Convenção de nomes de partials

- Sempre usar o prefixo **`tenant.{modulo}.partials.{arquivo}`**.
- Exemplos comuns:
  - `tenant.appointments.partials.status`
  - `tenant.appointments.partials.actions`
  - `tenant.form_responses.partials.actions`
  - `tenant.users.partials.status`

Esses partials são responsáveis por **qualquer HTML de célula** do grid.

---

## 5. Proibição de HTML no Controller

### 5.1. Regra geral

O controller **não** pode montar HTML manualmente.

#### Errado ❌

```php
$status = '<span class="badge bg-success">Ativo</span>';
```

#### Correto ✅

```php
'status_badge' => view('tenant.module.partials.status', [
    'model' => $model,
])->render(),
```

- Toda célula com HTML deve ser renderizada via **view parcial Blade** e retornada como **string** no JSON.
- O controller fica responsável apenas por **montar dados** e **delegar o HTML** para as views.

---

## 6. Estrutura da View `index.blade.php`

A view principal de listagem deve usar o componente `<x-tenant.grid>` seguindo o padrão abaixo:

```blade
<x-tenant.grid
    id="module-grid"
    :columns="[
        ['name' => 'campo_json', 'label' => 'Label'],
        ['name' => 'status_badge', 'label' => 'Status'],
        ['name' => 'actions', 'label' => 'Ações'],
    ]"
    ajaxUrl="{{ workspace_route('tenant.module.grid-data', $slug ?? null) }}"
    :pagination="true"
    :search="true"
    :sort="true"
    :defaultSort="['campo_json', 'asc']"
/> 
```

Regras:

- `id` deve ser único por página.
- `columns` define o **nome do campo no JSON** (`name`) e o **título exibido** (`label`).
- `ajaxUrl` deve apontar para a rota `module.grid-data` do módulo correspondente.
- `pagination`, `search` e `sort` devem ser usados conforme a necessidade da tela.

---

## 7. Nome das Rotas

### 7.1. Rota do grid (obrigatória)

- Todo módulo de listagem deve expor uma rota `grid-data` com o seguinte padrão:

```php
Route::get('module/grid-data', [ModuleController::class, 'gridData'])
    ->name('module.grid-data');
```

- Essa rota deve ser registrada **antes** das rotas que usam `{id}` para evitar conflitos de resolução de rota.

### 7.2. Exemplo completo de rotas

```php
Route::prefix('appointments')->group(function () {
    Route::get('grid-data', [AppointmentController::class, 'gridData'])
        ->name('appointments.grid-data');

    Route::get('/', [AppointmentController::class, 'index'])
        ->name('appointments.index');

    Route::get('{appointment}', [AppointmentController::class, 'show'])
        ->name('appointments.show');
});
```

---

## 8. Padrão de Segurança

- **Whitelist de ordenação:** obrigatório (ver seção 3).
- **Nunca** confiar em `sort_field` ou `sort_dir` diretamente do request.
- **Limitar `limit`** entre 1 e 100.
- **Eager loading obrigatório** (`with()`) para evitar N+1.
- **Filtro por médico**: aplicar `HasDoctorFilter` ou `applyDoctorFilter()` quando existir `doctor_id` no módulo.
- **Validação de parâmetros**: normalizar `page`, `limit`, `sort_dir`.

Essas regras são obrigatórias para **todas** as novas listagens e refatorações.

---

## 9. Migração Oficial do Sistema (DataTables → TailAdmin Grid)

- Todo o sistema está em processo de migração (ou já migrou) de:
  - **DataTables (ConnectPlus/jQuery)**
  - **Tabelas Blade com `@foreach`** e paginação tradicional

Para o novo padrão:

- **TailAdmin Grid Server-Side** com:
  - `<x-tenant.grid>`
  - Endpoint JSON `gridData()`
  - Paginação, ordenação e filtros controlados via backend

Regras:

- **DataTables não deve mais ser usado** em nenhum novo módulo.
- Qualquer nova tela de listagem deve nascer diretamente com o padrão `<x-tenant.grid>` + `gridData()`.
- Módulos antigos devem ser gradualmente migrados para esse padrão.

---

## 10. Checklist para Novo Módulo de Listagem

Checklist obrigatório para criar um novo módulo de listagem Tenant:

- [ ] **Criar rota `grid-data`**
  - `Route::get('module/grid-data', [Controller::class, 'gridData'])->name('module.grid-data');`
  - Registrar **antes** das rotas com `{id}`.

- [ ] **Implementar `gridData()`**
  - Query base com `Model::with([...])`.
  - Normalizar `page` e `limit` (1–100).
  - Implementar busca global (se aplicável).
  - Implementar whitelist de ordenação + fallback.
  - Retornar JSON no formato padrão (`data` + `meta`).

- [ ] **Criar partials Blade para colunas HTML**
  - `resources/views/tenant/{module}/partials/status.blade.php`
  - `resources/views/tenant/{module}/partials/actions.blade.php`
  - Outras colunas HTML necessárias.

- [ ] **Montar view `index.blade.php` com `<x-tenant.grid>`**
  - Definir `id` único.
  - Definir `columns` com campos JSON retornados pelo `gridData()`.
  - Apontar `ajaxUrl` para a rota `module.grid-data`.
  - Habilitar/ desabilitar paginação, busca e sort conforme necessidade.

- [ ] **Aplicar eager loading adequado**
  - Usar `with()` para todas as relações exibidas no grid.

- [ ] **Implementar whitelist de ordenação**
  - Definir `$sortable` completo.
  - Normalizar `sort_dir`.
  - Usar fallback seguro (`created_at desc` ou outro campo adequado).

- [ ] **Aplicar filtro por médico (quando aplicável)**
  - Integrar `HasDoctorFilter` ou `applyDoctorFilter()`.
  - Permitir filtragem via `doctor_id`.

- [ ] **Testar JSON direto na URL**
  - Acessar `.../module/grid-data` com parâmetros (`page`, `limit`, `search`, `sort_field`, `sort_dir`).
  - Verificar estrutura padrão:
    - `data` (array de registros)
    - `meta.current_page`
    - `meta.last_page`
    - `meta.per_page`
    - `meta.total`

---

## 11. Resumo

- `<x-tenant.grid>` é o **único padrão oficial** para listagens no Tenant.
- `gridData()` concentra **toda a lógica de leitura**, filtros, paginação e ordenação.
- Controllers **não** montam HTML; toda célula HTML vem de **partials Blade**.
- Estrutura de pastas e rotas segue sempre o padrão `tenant.{module}.partials.*` e `module.grid-data`.
- Whitelist de ordenação, limites de paginação e eager loading são **obrigatórios** por segurança e performance.
- DataTables e jQuery não devem mais ser utilizados em novas listagens.

Este documento é a **referência oficial** para qualquer implementação ou refatoração de grids no contexto Tenant.

