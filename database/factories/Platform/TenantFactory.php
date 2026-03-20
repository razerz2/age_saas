<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $subdomain = fake()->unique()->slug(2);

        return [
            'legal_name' => fake()->company(),
            'trade_name' => fake()->optional()->company(),
            'document' => fake()->optional()->numerify('##############'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->e164PhoneNumber(),
            'subdomain' => $subdomain,
            'admin_login_url' => null,
            'admin_email' => null,
            'admin_password' => null,
            'db_host' => '127.0.0.1',
            'db_port' => 5432,
            'db_name' => 'tenant_' . Str::lower(Str::random(10)),
            'db_username' => 'postgres',
            'db_password' => 'secret',
            'network_id' => null,
            'plan_id' => null,
            'status' => 'trial',
            'suspended_at' => null,
            'canceled_at' => null,
            'trial_ends_at' => now()->addDays(7),
            'asaas_customer_id' => null,
            'asaas_synced' => false,
            'asaas_sync_status' => 'pending',
            'asaas_last_sync_at' => null,
            'asaas_last_error' => null,
        ];
    }
}

