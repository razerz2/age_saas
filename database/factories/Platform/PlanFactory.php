<?php

namespace Database\Factories\Platform;

use App\Models\Platform\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Platform\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Plan',
            'description' => fake()->optional()->sentence(),
            'periodicity' => 'monthly',
            'period_months' => 1,
            'price_cents' => fake()->numberBetween(0, 50000),
            'category' => Plan::CATEGORY_COMMERCIAL,
            'plan_type' => Plan::TYPE_REAL,
            'show_on_landing_page' => true,
            'trial_enabled' => false,
            'trial_days' => null,
            'features' => [],
            'is_active' => true,
        ];
    }
}
