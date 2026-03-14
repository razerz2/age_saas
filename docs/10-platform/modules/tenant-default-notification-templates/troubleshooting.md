# Troubleshooting

## Modulo vazio na Platform

Verifique se o baseline foi seedado:

```bash
php artisan db:seed --class=TenantDefaultNotificationTemplatesSeeder
```

## Tenant novo sem templates em `notification_templates`

1. confirme que existem registros ativos em `tenant_default_notification_templates`;
2. confirme se a migration tenant de `notification_templates` foi aplicada;
3. execute backfill:

```bash
php artisan tenants:seed-default-notification-templates --tenant=<slug> --apply
```

## Backfill em todos os tenants (dry-run)

```bash
php artisan tenants:seed-default-notification-templates --all-tenants
```

## Atualizar tambem registros existentes

```bash
php artisan tenants:seed-default-notification-templates --all-tenants --apply --overwrite
```

