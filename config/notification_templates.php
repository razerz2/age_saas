<?php

/*
|--------------------------------------------------------------------------
| Notification Templates Catalog (Default / Immutable)
|--------------------------------------------------------------------------
|
| Este arquivo define os templates padrao do sistema por chave semantica.
| Eles devem ser tratados como defaults imutaveis (fonte de verdade base).
| Personalizacoes de tenant/canal devem viver fora deste catalogo.
|
| Placeholders suportados (usar no formato {{...}}):
| - {{clinic.name}}
| - {{patient.name}}
| - {{professional.name}}
| - {{appointment.date}}
| - {{appointment.time}}
| - {{appointment.mode}}
| - {{links.appointment_confirm}}
| - {{links.appointment_cancel}}
| - {{links.appointment_details}}
| - {{links.waitlist_offer}}
| - {{waitlist.offer_expires_at}}
|
*/

return [
    'channels' => [
        'email',
        'whatsapp',
    ],

    'templates' => [
        'appointment.pending_confirmation' => [
            'label' => 'Agendamento pendente de confirmação',
            'email' => [
                'subject' => '⏳ Confirme seu agendamento — {{clinic.name}}',
                'content' => "👋 Olá {{patient.name}}!\n\n⏳ Para confirmar seu agendamento em {{clinic.name}}, use o link abaixo:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n✅ Confirmar: {{links.appointment_confirm}}\n❌ Cancelar: {{links.appointment_cancel}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "👋 Olá {{patient.name}}!\n\n⏳ Para confirmar seu agendamento em {{clinic.name}}, use o link abaixo:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n✅ Confirmar: {{links.appointment_confirm}}\n❌ Cancelar: {{links.appointment_cancel}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.confirmed' => [
            'label' => 'Agendamento confirmado',
            'email' => [
                'subject' => '✅ Agendamento confirmado — {{clinic.name}}',
                'content' => "✅ Agendamento confirmado — {{clinic.name}}\n\nOlá {{patient.name}}! Seu horário está confirmado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n🔎 Detalhes: {{links.appointment_details}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "✅ Agendamento confirmado — {{clinic.name}}\n\nOlá {{patient.name}}! Seu horário está confirmado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n🔎 Detalhes: {{links.appointment_details}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.canceled' => [
            'label' => 'Agendamento cancelado',
            'email' => [
                'subject' => '❌ Agendamento cancelado — {{clinic.name}}',
                'content' => "❌ Agendamento cancelado — {{clinic.name}}\n\nOlá {{patient.name}},\nSeu agendamento foi cancelado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nSe precisar, você pode reagendar.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "❌ Agendamento cancelado — {{clinic.name}}\n\nOlá {{patient.name}},\nSeu agendamento foi cancelado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nSe precisar, você pode reagendar.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.expired' => [
            'label' => 'Agendamento expirado',
            'email' => [
                'subject' => '⚠️ Prazo expirado — {{clinic.name}}',
                'content' => "⚠️ Prazo expirado — {{clinic.name}}\n\nOlá {{patient.name}},\nO prazo para confirmar seu agendamento expirou e o horário foi liberado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nSe desejar, faça um novo agendamento.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "⚠️ Prazo expirado — {{clinic.name}}\n\nOlá {{patient.name}},\nO prazo para confirmar seu agendamento expirou e o horário foi liberado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nSe desejar, faça um novo agendamento.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'waitlist.joined' => [
            'label' => 'Entrada na fila de espera',
            'email' => [
                'subject' => '📝 Fila de espera — {{clinic.name}}',
                'content' => "📝 Fila de espera — {{clinic.name}}\n\nOlá {{patient.name}}!\nVocê entrou na fila de espera para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nQuando a vaga ficar disponível, enviaremos um link para confirmação.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📝 Fila de espera — {{clinic.name}}\n\nOlá {{patient.name}}!\nVocê entrou na fila de espera para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\nQuando a vaga ficar disponível, enviaremos um link para confirmação.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'waitlist.offered' => [
            'label' => 'Oferta de vaga na fila de espera',
            'email' => [
                'subject' => '🎉 Vaga disponível — {{clinic.name}}',
                'content' => "🎉 Vaga disponível — {{clinic.name}}\n\nOlá {{patient.name}}!\nUma vaga foi liberada para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\n⏳ Confirme até: {{waitlist.offer_expires_at}}\n✅ Confirmar vaga: {{links.waitlist_offer}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🎉 Vaga disponível — {{clinic.name}}\n\nOlá {{patient.name}}!\nUma vaga foi liberada para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\n⏳ Confirme até: {{waitlist.offer_expires_at}}\n✅ Confirmar vaga: {{links.waitlist_offer}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],
    ],
];
