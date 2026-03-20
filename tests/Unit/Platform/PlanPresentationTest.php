<?php

use App\Models\Platform\Plan;

test('plan presentation labels expose type and landing visibility for platform screens', function () {
    $productionPlan = Plan::factory()->make([
        'plan_type' => Plan::TYPE_REAL,
        'show_on_landing_page' => true,
        'category' => Plan::CATEGORY_COMMERCIAL,
    ]);

    expect($productionPlan->planTypeLabel())->toBe('Producao')
        ->and($productionPlan->landingVisibilityLabel())->toBe('Visivel na Landing')
        ->and($productionPlan->categoryLabel())->toBe('Comercial');

    $testHiddenPlan = Plan::factory()->make([
        'plan_type' => Plan::TYPE_TEST,
        'show_on_landing_page' => false,
        'category' => Plan::CATEGORY_SANDBOX,
    ]);

    expect($testHiddenPlan->planTypeLabel())->toBe('Teste')
        ->and($testHiddenPlan->landingVisibilityLabel())->toBe('Oculto na Landing')
        ->and($testHiddenPlan->categoryLabel())->toBe('Sandbox');
});
