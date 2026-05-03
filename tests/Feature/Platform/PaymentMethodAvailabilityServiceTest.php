<?php

use App\Services\Platform\PaymentMethodAvailabilityService;

test('payment method availability defaults are applied correctly', function () {
    expect(app(PaymentMethodAvailabilityService::class)->isEnabled('PIX'))->toBeTrue();
    expect(app(PaymentMethodAvailabilityService::class)->isEnabled('BOLETO'))->toBeTrue();
    expect(app(PaymentMethodAvailabilityService::class)->isEnabled('CREDIT_CARD'))->toBeTrue();
    expect(app(PaymentMethodAvailabilityService::class)->isEnabled('PIX_RECURRENT'))->toBeFalse();
    expect(app(PaymentMethodAvailabilityService::class)->isEnabled('DEBIT_CARD'))->toBeFalse();
});

