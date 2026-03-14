<?php

use App\Support\WhatsAppOfficialTemplateValidator;

it('rejects body_text starting with placeholder', function () {
    $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
        "{{1}} seu agendamento foi confirmado.",
        ['1' => 'patient.name']
    );

    expect($errors)->toHaveKey('body_text')
        ->and($errors['body_text'])->toContain('início ou no fim');
});

it('rejects body_text ending with placeholder', function () {
    $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
        "Seu agendamento foi confirmado. Link: {{1}}",
        ['1' => 'links.appointment_manage']
    );

    expect($errors)->toHaveKey('body_text')
        ->and($errors['body_text'])->toContain('início ou no fim');
});

it('rejects body_text where last non empty line is only placeholder', function () {
    $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
        "Seu agendamento foi confirmado.\n\n{{1}}",
        ['1' => 'links.appointment_manage']
    );

    expect($errors)->toHaveKey('body_text')
        ->and($errors['body_text'])->toContain('início ou no fim');
});

it('accepts body_text with placeholders away from edges', function () {
    $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
        "Ola {{1}}.\n\nData: {{2}}.\n\nLink: {{3}}.\n\nAguardamos sua confirmacao.",
        [
            '1' => 'patient.name',
            '2' => 'appointment.date',
            '3' => 'links.appointment_confirm',
        ]
    );

    expect($errors)->toBe([]);
});

it('rejects submit when sample_variables are missing for placeholders', function () {
    $errors = WhatsAppOfficialTemplateValidator::validateSampleVariablesConsistency(
        "Ola {{1}}.\n\nData: {{2}}.\n\nMensagem final fixa.",
        [],
        true
    );

    expect($errors)->toHaveKey('sample_variables');
});

it('accepts sample_variables when all placeholders are mapped', function () {
    $errors = WhatsAppOfficialTemplateValidator::validateSampleVariablesConsistency(
        "Ola {{1}}.\n\nData: {{2}}.\n\nMensagem final fixa.",
        [
            '1' => 'Rafael',
            '2' => '14/03/2026 as 09:00',
        ],
        true
    );

    expect($errors)->toBe([]);
});

it('accepts normalized platform variables mapping for saas templates', function () {
    $errors = WhatsAppOfficialTemplateValidator::validatePlaceholderConsistency(
        "Ola {{1}}.\n\nValor: {{2}}.\nVencimento: {{3}}.\nLink: {{4}}.\n\nMensagem final fixa.",
        [
            '1' => 'customer_name',
            '2' => 'invoice_amount',
            '3' => 'due_date',
            '4' => 'payment_link',
        ]
    );

    expect($errors)->toBe([]);
});
