<?php

use App\Services\Tenant\WhatsAppBot\Conversation\WhatsAppBotMessageFormatter;
use Tests\TestCase;

uses(TestCase::class);

it('formats numbered options using the 1) pattern', function () {
    $formatter = app(WhatsAppBotMessageFormatter::class);

    $text = $formatter->numberedOptions([
        'Agendar consulta',
        'Ver meus agendamentos',
        'Cancelar agendamento',
    ]);

    expect($text)->toBe(
        "1) Agendar consulta\n" .
        "2) Ver meus agendamentos\n" .
        "3) Cancelar agendamento"
    )
        ->and($text)->not->toContain('1.')
        ->and($text)->not->toContain('1 -')
        ->and($text)->not->toContain('[1]');
});

it('formats confirmation messages with readable spacing', function () {
    $formatter = app(WhatsAppBotMessageFormatter::class);

    $text = $formatter->confirmation(
        'Confirma o agendamento abaixo?',
        [
            'Especialidade: Cardiologia',
            'Profissional: Dr. João Silva',
            'Data: 06/04/2026',
            'Horário: 09:00',
        ],
        ['Confirmar', 'Cancelar']
    );

    expect($text)->toBe(
        "Confirma o agendamento abaixo?\n\n" .
        "Especialidade: Cardiologia\n" .
        "Profissional: Dr. João Silva\n" .
        "Data: 06/04/2026\n" .
        "Horário: 09:00\n\n" .
        "1) Confirmar\n" .
        "2) Cancelar"
    );
});

it('formats final success message with details and menu using 1) options', function () {
    $formatter = app(WhatsAppBotMessageFormatter::class);

    $text = $formatter->compose([
        'Agendamento realizado com sucesso!',
        "Data: 06/04/2026\nHorário: 09:00\nProfissional: Dr. João Silva",
        'Se precisar de algo mais, escolha uma opção:',
        $formatter->numberedOptions([
            'Agendar nova consulta',
            'Ver meus agendamentos',
            'Encerrar atendimento',
        ]),
    ]);

    expect($text)->toBe(
        "Agendamento realizado com sucesso!\n\n" .
        "Data: 06/04/2026\nHorário: 09:00\nProfissional: Dr. João Silva\n\n" .
        "Se precisar de algo mais, escolha uma opção:\n\n" .
        "1) Agendar nova consulta\n" .
        "2) Ver meus agendamentos\n" .
        "3) Encerrar atendimento"
    );
});

it('sanitizes technical-looking display names', function () {
    $formatter = app(WhatsAppBotMessageFormatter::class);

    expect($formatter->sanitizeDisplayName('Especialidade Dusk 20866574', 'Especialidade disponível'))
        ->toBe('Especialidade disponível')
        ->and($formatter->sanitizeDisplayName('Dra. Maria Souza', 'Profissional'))
        ->toBe('Dra. Maria Souza');
});

it('keeps key whatsapp bot text files free from mojibake markers and utf8 bom', function () {
    $paths = [
        base_path('app/Services/Tenant/WhatsAppBot/Conversation/WhatsAppBotConversationOrchestrator.php'),
        base_path('app/Services/Tenant/WhatsAppBot/Conversation/WhatsAppBotMessageFormatter.php'),
        base_path('app/Services/Tenant/WhatsAppBotConfigService.php'),
        base_path('resources/views/tenant/settings/tabs/bot-whatsapp.blade.php'),
    ];

    foreach ($paths as $path) {
        $content = file_get_contents($path);

        expect($content)->not->toBeFalse();
        expect($content)->not->toContain('Ã');
        expect($content)->not->toContain('Â');
        expect($content)->not->toContain('�');
        expect(str_starts_with((string) $content, "\xEF\xBB\xBF"))->toBeFalse();
    }
});
