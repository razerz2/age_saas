<?php

use App\TenantFinder\HybridTenantFinder;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\CallQueuedClosure;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;
//use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask;
use App\Multitenancy\Tasks\SwitchTenantTask;
use App\TenantFinder\PathTenantFinder;


return [

    /*
    |--------------------------------------------------------------------------
    | Classe responsável por identificar o Tenant
    |--------------------------------------------------------------------------
    |
    | Aqui definimos que o tenant será encontrado pelo domínio/subdomínio,
    | usando DomainTenantFinder. Exemplo:
    |   - clinicaabc.agepro.com -> busca Tenant com domain = clinicaabc.agepro.com
    |
    */
    'tenant_finder' => PathTenantFinder::class,

    /*
    |--------------------------------------------------------------------------
    | Campos utilizados em comandos tenant:artisan
    |--------------------------------------------------------------------------
    */
    'tenant_artisan_search_fields' => ['id'],

    /*
    |--------------------------------------------------------------------------
    | Tarefas executadas ao alternar de Tenant
    |--------------------------------------------------------------------------
    |
    | Aqui dizemos ao pacote que, ao mudar de tenant, ele deve também
    | trocar a conexão de banco de dados.
    |
    */
    'switch_tenant_tasks' => [
        SwitchTenantTask::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modelo que representa cada Tenant (tabela tenants)
    |--------------------------------------------------------------------------
    */
    'tenant_model' => App\Models\Platform\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Fila e Jobs com consciência de Tenant
    |--------------------------------------------------------------------------
    */
    'queues_are_tenant_aware_by_default' => true,

    /*
    |--------------------------------------------------------------------------
    | Nome da conexão dos bancos
    |--------------------------------------------------------------------------
    |
    | - landlord_database_connection_name → banco da plataforma (central)
    | - tenant_database_connection_name → base dos clientes (clínicas)
    |
    */
    'tenant_database_connection_name' => 'tenant',
    'landlord_database_connection_name' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Chave do Tenant atual no container
    |--------------------------------------------------------------------------
    */
    'current_tenant_container_key' => 'currentTenant',

    /*
    |--------------------------------------------------------------------------
    | Domínios centrais (Platform)
    |--------------------------------------------------------------------------
    |
    | Aqui listamos os domínios que pertencem à área administrativa
    | e não são tenants. Exemplo: app.agepro.com
    |
    */
    'central_domains' => [
        env('APP_DOMAIN', 'app.agepro.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rotas cacheadas compartilhadas (não usar aqui)
    |--------------------------------------------------------------------------
    */
    'shared_routes_cache' => false,

    /*
    |--------------------------------------------------------------------------
    | Ações principais do pacote
    |--------------------------------------------------------------------------
    */
    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant' => MigrateTenantAction::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeamento de jobs
    |--------------------------------------------------------------------------
    */
    'queueable_to_job' => [
        SendQueuedMailable::class => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedClosure::class => 'closure',
        CallQueuedListener::class => 'class',
        BroadcastEvent::class => 'event',
    ],

    'tenant_aware_jobs' => [
        // ...
    ],

    'not_tenant_aware_jobs' => [
        // ...
    ],
];
