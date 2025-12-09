# 🔐 Controle de Funcionalidades por Plano

Este documento descreve como implementar o controle de acesso a funcionalidades baseado no plano de assinatura do tenant.

## 📋 Visão Geral

O sistema permite que cada tenant tenha acesso apenas às funcionalidades incluídas no seu plano de assinatura. Isso é feito através de:

1. **Features** (`subscription_features`): Funcionalidades disponíveis no sistema
2. **Plan Access Rules** (`plan_access_rules`): Regras de acesso por plano
3. **Plan Access Rule Features** (`plan_access_rule_feature`): Relacionamento entre planos e features (com flag `allowed`)

## 🏗️ Estrutura

### Models Relacionados

- `App\Models\Platform\SubscriptionFeature`: Funcionalidades do sistema
- `App\Models\Platform\PlanAccessRule`: Regras de acesso do plano
- `App\Models\Platform\Plan`: Plano de assinatura
- `App\Models\Platform\Subscription`: Assinatura do tenant
- `App\Models\Platform\Tenant`: Tenant

### Service Principal

- `App\Services\FeatureAccessService`: Service responsável por verificar acesso a features

### Middlewares

- `App\Http\Middleware\EnsureFeatureAccess`: Protege rotas exigindo TODAS as features especificadas
- `App\Http\Middleware\EnsureAnyFeatureAccess`: Protege rotas exigindo QUALQUER uma das features

### Trait

- `App\Traits\HasFeatureAccess`: Trait para facilitar verificação de features em controllers

## 🚀 Como Usar

### 1. Definindo Features no Banco de Dados

Primeiro, você precisa criar as features no banco de dados:

```php
use App\Models\Platform\SubscriptionFeature;

// Criar uma feature
SubscriptionFeature::create([
    'name' => 'whatsapp_integration',
    'label' => 'Integração WhatsApp',
    'is_default' => false,
]);
```

### 2. Associando Features aos Planos

Associe as features aos planos através das regras de acesso:

```php
use App\Models\Platform\PlanAccessRule;
use App\Models\Platform\SubscriptionFeature;

$plan = Plan::find($planId);
$accessRule = $plan->accessRule;

$feature = SubscriptionFeature::where('name', 'whatsapp_integration')->first();

// Permitir a feature para o plano
$accessRule->features()->attach($feature->id, ['allowed' => true]);
```

### 3. Verificando Features em Controllers

#### Usando o Trait

```php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Traits\HasFeatureAccess;

class WhatsAppController extends Controller
{
    use HasFeatureAccess;

    public function index()
    {
        // Verifica se tem acesso antes de executar
        $this->requireFeature('whatsapp_integration');
        
        // Se chegou aqui, tem acesso
        return view('tenant.whatsapp.index');
    }

    public function send()
    {
        // Verifica múltiplas features (todas são necessárias)
        $this->requireAllFeatures(['whatsapp_integration', 'sms_notifications']);
        
        // Lógica de envio
    }
}
```

#### Usando Helpers Globais

```php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;

class CalendarController extends Controller
{
    public function index()
    {
        // Verifica se tem acesso
        if (!has_feature('google_calendar')) {
            abort(403, 'Funcionalidade não disponível no seu plano');
        }
        
        return view('tenant.calendar.index');
    }

    public function sync()
    {
        // Verifica múltiplas features
        if (!has_all_features(['google_calendar', 'calendar_sync'])) {
            return redirect()->back()->with('error', 'Acesso negado');
        }
        
        // Lógica de sincronização
    }
}
```

#### Usando o Service Diretamente

```php
use App\Services\FeatureAccessService;

class SomeController extends Controller
{
    public function index(FeatureAccessService $featureService)
    {
        if (!$featureService->hasFeature('whatsapp_integration')) {
            abort(403);
        }
        
        // Lógica
    }
}
```

### 4. Protegendo Rotas com Middleware

#### Exigindo TODAS as features

```php
// routes/tenant.php

Route::middleware(['feature:whatsapp_integration'])->group(function () {
    Route::get('/whatsapp', [WhatsAppController::class, 'index']);
    Route::post('/whatsapp/send', [WhatsAppController::class, 'send']);
});

// Exigindo múltiplas features (todas são necessárias)
Route::middleware(['feature:whatsapp_integration,calendar_sync'])->group(function () {
    Route::get('/integrations', [IntegrationController::class, 'index']);
});
```

#### Exigindo QUALQUER uma das features

```php
// Basta ter uma das features
Route::middleware(['feature.any:whatsapp_integration,sms_integration'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
});
```

### 5. Verificando Limites do Plano

Além de features, você pode verificar limites do plano (ex: número máximo de médicos):

```php
use App\Traits\HasFeatureAccess;

class DoctorController extends Controller
{
    use HasFeatureAccess;

    public function store(Request $request)
    {
        $maxDoctors = $this->getPlanLimit('max_doctors');
        
        if ($maxDoctors !== null) {
            $currentCount = Doctor::count();
            
            if ($currentCount >= $maxDoctors) {
                return redirect()->back()
                    ->with('error', "Limite de {$maxDoctors} médicos atingido no seu plano");
            }
        }
        
        // Criar médico
    }
}
```

Ou usando o helper:

```php
$maxDoctors = get_plan_limit('max_doctors');
$maxUsers = get_plan_limit('max_admin_users');
```

### 6. Listando Features Disponíveis

```php
// Usando o trait
$features = $this->getAvailableFeatures();
// Retorna: ['whatsapp_integration', 'google_calendar', ...]

// Usando o helper
$features = get_available_features();
```

### 7. Verificando Features em Views

```blade
{{-- Verifica se tem acesso antes de mostrar o botão --}}
@if(has_feature('whatsapp_integration'))
    <a href="{{ route('tenant.whatsapp.index') }}" class="btn btn-primary">
        WhatsApp
    </a>
@endif

{{-- Verifica múltiplas features --}}
@if(has_all_features(['whatsapp_integration', 'sms_notifications']))
    <div class="notification-panel">
        <!-- Conteúdo -->
    </div>
@endif

{{-- Mostrar aviso se não tiver acesso --}}
@unless(has_feature('google_calendar'))
    <div class="alert alert-warning">
        Esta funcionalidade não está disponível no seu plano.
        <a href="{{ route('tenant.subscription.upgrade') }}">Fazer upgrade</a>
    </div>
@endunless
```

## 📝 Exemplos de Features Comuns

Algumas features que você pode criar:

- `whatsapp_integration`: Integração com WhatsApp
- `google_calendar`: Integração com Google Calendar
- `apple_calendar`: Integração com Apple Calendar
- `sms_notifications`: Notificações por SMS
- `email_marketing`: Marketing por e-mail
- `advanced_reports`: Relatórios avançados
- `custom_branding`: Personalização de marca
- `api_access`: Acesso à API
- `white_label`: White label
- `multi_location`: Múltiplas localizações

## 🔧 Configuração

### Registrar Middlewares (já feito)

Os middlewares já estão registrados no `app/Http/Kernel.php`:

```php
'feature' => \App\Http\Middleware\EnsureFeatureAccess::class,
'feature.any' => \App\Http\Middleware\EnsureAnyFeatureAccess::class,
```

### Helpers Globais (já configurados)

Os helpers já estão disponíveis globalmente através de `app/Helpers/helpers.php`:

- `has_feature(string $featureName): bool`
- `has_any_feature(array $featureNames): bool`
- `has_all_features(array $featureNames): bool`
- `get_available_features(): array`
- `get_plan_limit(string $limitType): ?int`

## ⚠️ Observações Importantes

1. **Conexão de Banco**: O `FeatureAccessService` automaticamente usa a conexão da plataforma (landlord) para buscar subscriptions e features, mesmo quando executado no contexto de um tenant.

2. **Performance**: As verificações são feitas em tempo real. Para melhorar performance, considere cachear as features disponíveis do tenant.

3. **Assinatura Ativa**: O sistema verifica apenas assinaturas com status `active` e que não expiraram (`ends_at` é null ou futuro).

4. **Fallback**: Se o tenant não tiver assinatura ativa, todas as verificações retornam `false`.

## 🎯 Exemplo Completo

```php
// Controller
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Traits\HasFeatureAccess;

class IntegrationController extends Controller
{
    use HasFeatureAccess;

    public function __construct()
    {
        // Protege todas as rotas do controller
        $this->middleware('feature:whatsapp_integration');
    }

    public function index()
    {
        // Já verificado pelo middleware
        $features = $this->getAvailableFeatures();
        
        return view('tenant.integrations.index', compact('features'));
    }

    public function syncGoogleCalendar()
    {
        // Verifica feature específica
        $this->requireFeature('google_calendar');
        
        // Verifica limite
        $maxIntegrations = $this->getPlanLimit('max_integrations');
        
        // Lógica de sincronização
    }
}
```

```php
// routes/tenant.php
Route::middleware(['tenant.auth', 'feature:whatsapp_integration'])->group(function () {
    Route::get('/whatsapp', [WhatsAppController::class, 'index']);
    Route::post('/whatsapp/send', [WhatsAppController::class, 'send']);
});

Route::middleware(['tenant.auth', 'feature.any:google_calendar,apple_calendar'])->group(function () {
    Route::get('/calendar/sync', [CalendarController::class, 'sync']);
});
```

```blade
{{-- resources/views/tenant/dashboard.blade.php --}}
<div class="features-grid">
    @if(has_feature('whatsapp_integration'))
        <div class="feature-card">
            <h3>WhatsApp</h3>
            <a href="{{ route('tenant.whatsapp.index') }}">Configurar</a>
        </div>
    @endif

    @if(has_feature('google_calendar'))
        <div class="feature-card">
            <h3>Google Calendar</h3>
            <a href="{{ route('tenant.calendar.sync') }}">Sincronizar</a>
        </div>
    @endif
</div>
```

