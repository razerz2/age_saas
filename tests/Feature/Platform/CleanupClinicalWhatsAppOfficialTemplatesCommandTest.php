<?php

use App\Models\Platform\WhatsAppOfficialTemplate;
use Illuminate\Support\Facades\Artisan;

function createOfficialTemplateForCleanup(string $key, string $status = WhatsAppOfficialTemplate::STATUS_DRAFT): WhatsAppOfficialTemplate
{
    return WhatsAppOfficialTemplate::query()->create([
        'key' => $key,
        'meta_template_name' => str_replace('.', '_', $key),
        'provider' => WhatsAppOfficialTemplate::PROVIDER,
        'category' => 'UTILITY',
        'language' => 'pt_BR',
        'body_text' => 'Ola {{1}}.',
        'variables' => ['1' => 'tenant.trade_name'],
        'sample_variables' => ['1' => 'Clinica Exemplo'],
        'version' => 1,
        'status' => $status,
    ]);
}

it('runs in dry-run mode by default and does not alter records', function () {
    $clinical = createOfficialTemplateForCleanup('appointment.confirmed', WhatsAppOfficialTemplate::STATUS_DRAFT);
    $platform = createOfficialTemplateForCleanup('platform.billing.invoice_due', WhatsAppOfficialTemplate::STATUS_DRAFT);

    Artisan::call('whatsapp-official-templates:clean-clinical');

    $clinical->refresh();
    $platform->refresh();

    expect($clinical->status)->toBe(WhatsAppOfficialTemplate::STATUS_DRAFT)
        ->and($platform->status)->toBe(WhatsAppOfficialTemplate::STATUS_DRAFT);

    expect(Artisan::output())->toContain('Dry-run concluido');
});

it('archives only legacy clinical keys when running with --apply --mode=archive', function () {
    $clinicalDraft = createOfficialTemplateForCleanup('appointment.confirmed', WhatsAppOfficialTemplate::STATUS_DRAFT);
    $clinicalRejected = createOfficialTemplateForCleanup('waitlist.offered', WhatsAppOfficialTemplate::STATUS_REJECTED);
    $platform = createOfficialTemplateForCleanup('platform.billing.invoice_due', WhatsAppOfficialTemplate::STATUS_DRAFT);

    Artisan::call('whatsapp-official-templates:clean-clinical', [
        '--apply' => true,
        '--mode' => 'archive',
    ]);

    $clinicalDraft->refresh();
    $clinicalRejected->refresh();
    $platform->refresh();

    expect($clinicalDraft->status)->toBe(WhatsAppOfficialTemplate::STATUS_ARCHIVED)
        ->and($clinicalRejected->status)->toBe(WhatsAppOfficialTemplate::STATUS_ARCHIVED)
        ->and($platform->status)->toBe(WhatsAppOfficialTemplate::STATUS_DRAFT);
});
