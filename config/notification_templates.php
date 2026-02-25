<?php

/*
|--------------------------------------------------------------------------
| Notification Templates Catalog (Default / Immutable)
|--------------------------------------------------------------------------
|
| Este arquivo define os templates padrão do sistema por chave semântica.
| Eles devem ser tratados como defaults imutáveis (fonte de verdade base).
| Personalizações de tenant/canal devem viver fora deste catálogo.
|
| Placeholders suportados (usar no formato {{...}}):
|
| - {{clinic.name}}                         Nome da clínica/tenant
| - {{clinic.phone}}                        Telefone da clínica
| - {{clinic.email}}                        E-mail da clínica
| - {{clinic.address}}                      Endereço da clínica
| - {{patient.name}}                        Nome do paciente
| - {{professional.name}}                   Nome do profissional/médico
| - {{doctor.name}}                         Nome do médico/profissional
| - {{doctor.specialty}}                    Especialidade do médico (opcional)
| - {{appointment.date}}                    Data do agendamento (ex.: 24/02/2026)
| - {{appointment.time}}                    Horário do agendamento (ex.: 14:30)
| - {{appointment.mode}}                    Modalidade (presencial/online)
| - {{appointment.type}}                    Tipo de atendimento (opcional)
| - {{appointment.confirmation_expires_at}} Prazo limite para confirmação
| - {{waitlist.offer_expires_at}}           Prazo limite da oferta de vaga
|
| Links:
| - {{links.appointment_confirm}}           Link para confirmar agendamento
| - {{links.appointment_cancel}}            Link para cancelar agendamento
| - {{links.appointment_details}}           Link para visualizar detalhes
| - {{links.waitlist_offer}}                Link da oferta de vaga da waitlist
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
                'content' => "Olá {{patient.name}},\n\n⏳ Seu agendamento está aguardando confirmação.\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n⏳ Confirme até: {{appointment.confirmation_expires_at}}\n\n✅ Confirmar:\n{{links.appointment_confirm}}\n\n❌ Cancelar:\n{{links.appointment_cancel}}\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "👋 Olá {{patient.name}}!\n\n⏳ Seu agendamento está aguardando confirmação.\n\n📅 {{appointment.date}}\n🕐 {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n📍 {{appointment.mode}}\n\n✅ Confirmar:\n{{links.appointment_confirm}}\n❌ Cancelar:\n{{links.appointment_cancel}}\n\n🏥 {{clinic.name}}",
            ],
        ],

        'appointment.confirmed' => [
            'label' => 'Agendamento confirmado',
            'email' => [
                'subject' => '✅ Agendamento confirmado — {{clinic.name}}',
                'content' => "Olá {{patient.name}},\n\n✅ Seu agendamento foi confirmado com sucesso.\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n🔗 Detalhes:\n{{links.appointment_details}}\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "✅ Agendamento confirmado, {{patient.name}}!\n\n📅 {{appointment.date}}\n🕐 {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n📍 {{appointment.mode}}\n\n🔗 Detalhes:\n{{links.appointment_details}}\n\n🏥 {{clinic.name}}",
            ],
        ],

        'appointment.canceled' => [
            'label' => 'Agendamento cancelado',
            'email' => [
                'subject' => '❌ Agendamento cancelado — {{clinic.name}}',
                'content' => "Olá {{patient.name}},\n\n❌ Seu agendamento foi cancelado.\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n📲 Se desejar, entre em contato para reagendar.\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "❌ Agendamento cancelado, {{patient.name}}.\n\n📅 {{appointment.date}}\n🕐 {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n📍 {{appointment.mode}}\n\n📲 Se quiser, fale com a clínica para reagendar.\n\n🏥 {{clinic.name}}",
            ],
        ],

        'appointment.expired' => [
            'label' => 'Agendamento expirado',
            'email' => [
                'subject' => '⚠️ Prazo de confirmação expirado — {{clinic.name}}',
                'content' => "Olá {{patient.name}},\n\n⚠️ O prazo para confirmar seu agendamento expirou.\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\n📝 Se ainda desejar atendimento, faça um novo agendamento.\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "⚠️ O prazo para confirmar expirou, {{patient.name}}.\n\n📅 {{appointment.date}}\n🕐 {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n\n📝 Faça um novo agendamento quando desejar.\n\n🏥 {{clinic.name}}",
            ],
        ],

        'waitlist.offered' => [
            'label' => 'Oferta de vaga na fila de espera',
            'email' => [
                'subject' => '🎉 Vaga disponível para você — {{clinic.name}}',
                'content' => "Olá {{patient.name}},\n\n🎉 Uma vaga ficou disponível para você.\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n⏳ Expira em: {{waitlist.offer_expires_at}}\n\n🔗 Confirmar vaga:\n{{links.waitlist_offer}}\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🎉 Vaga disponível para você, {{patient.name}}!\n\n📅 {{appointment.date}}\n🕐 {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n⏳ Expira em: {{waitlist.offer_expires_at}}\n\n🔗 Confirmar vaga:\n{{links.waitlist_offer}}\n\n🏥 {{clinic.name}}",
            ],
        ],

        'waitlist.joined' => [
            'label' => 'Entrada na fila de espera',
            'email' => [
                'subject' => '📝 Você entrou na fila de espera — {{clinic.name}}',
                'content' => "Olá {{patient.name}},\n\n📝 Você entrou na fila de espera.\n\n📅 Data desejada: {{appointment.date}}\n🕐 Horário desejado: {{appointment.time}}\n👨‍⚕️ Profissional: {{professional.name}}\n\n🔔 Avisaremos quando surgir uma vaga.\n\n🏥 {{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📝 Você entrou na fila de espera, {{patient.name}}.\n\n📅 Data desejada: {{appointment.date}}\n🕐 Horário desejado: {{appointment.time}}\n👨‍⚕️ {{professional.name}}\n\n🔔 Quando surgir vaga, você receberá uma notificação.\n\n🏥 {{clinic.name}}",
            ],
        ],
    ],
];
