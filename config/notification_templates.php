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
| - {{links.form_fill}}
| - {{links.waitlist_offer}}
| - {{links.form_response}}
| - {{links.online_appointment_details}}
| - {{waitlist.offer_expires_at}}
| - {{form.name}}
| - {{response.submitted_at}}
| - {{online.meeting_link}}
| - {{online.meeting_app}}
| - {{online.instructions_sent}}
| - {{labels.professional_singular}}
| - {{labels.professional_plural}}
| - {{labels.professional_registration}}
| - {{labels.professional_singular_lower}}
| - {{labels.professional_plural_lower}}
| - {{labels.professional_registration_lower}}
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
                'content' => "👋 Olá {{patient.name}}!\n\n⏳ Para confirmar seu agendamento em {{clinic.name}}, use o link abaixo:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n✅ Confirmar: {{links.appointment_confirm}}\n❌ Cancelar: {{links.appointment_cancel}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "👋 Olá {{patient.name}}!\n\n⏳ Para confirmar seu agendamento em {{clinic.name}}, use o link abaixo:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n✅ Confirmar: {{links.appointment_confirm}}\n❌ Cancelar: {{links.appointment_cancel}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.confirmed' => [
            'label' => 'Agendamento confirmado',
            'email' => [
                'subject' => '✅ Agendamento confirmado — {{clinic.name}}',
                'content' => "✅ Agendamento confirmado — {{clinic.name}}\n\nOlá {{patient.name}}! Seu horário está confirmado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n🔎 Detalhes: {{links.appointment_details}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "✅ Agendamento confirmado — {{clinic.name}}\n\nOlá {{patient.name}}! Seu horário está confirmado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n📍 Modalidade: {{appointment.mode}}\n\n🔎 Detalhes: {{links.appointment_details}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.canceled' => [
            'label' => 'Agendamento cancelado',
            'email' => [
                'subject' => '❌ Agendamento cancelado — {{clinic.name}}',
                'content' => "❌ Agendamento cancelado — {{clinic.name}}\n\nOlá {{patient.name}},\nSeu agendamento foi cancelado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nSe precisar, você pode reagendar.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "❌ Agendamento cancelado — {{clinic.name}}\n\nOlá {{patient.name}},\nSeu agendamento foi cancelado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nSe precisar, você pode reagendar.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.expired' => [
            'label' => 'Agendamento expirado',
            'email' => [
                'subject' => '⚠️ Prazo expirado — {{clinic.name}}',
                'content' => "⚠️ Prazo expirado — {{clinic.name}}\n\nOlá {{patient.name}},\nO prazo para confirmar seu agendamento expirou e o horário foi liberado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nSe desejar, faça um novo agendamento.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "⚠️ Prazo expirado — {{clinic.name}}\n\nOlá {{patient.name}},\nO prazo para confirmar seu agendamento expirou e o horário foi liberado:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nSe desejar, faça um novo agendamento.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.form_requested.patient' => [
            'label' => 'Solicitação de formulário para paciente',
            'email' => [
                'subject' => '📝 Formulário disponível — {{clinic.name}}',
                'content' => "Olá {{patient.name}}!\n\nSeu formulário pré-consulta está disponível para o agendamento abaixo:\n\n📄 Formulário: {{form.name}}\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\n➡️ Preencher formulário: {{links.form_fill}}\n\nPor favor, preencha antes da consulta.\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "Olá {{patient.name}}!\n\nSeu formulário pré-consulta está disponível para o agendamento abaixo:\n\n📄 Formulário: {{form.name}}\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\n➡️ Preencher formulário: {{links.form_fill}}\n\nPor favor, preencha antes da consulta.",
            ],
        ],

        'waitlist.joined' => [
            'label' => 'Entrada na fila de espera',
            'email' => [
                'subject' => '📝 Fila de espera — {{clinic.name}}',
                'content' => "📝 Fila de espera — {{clinic.name}}\n\nOlá {{patient.name}}!\nVocê entrou na fila de espera para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nQuando a vaga ficar disponível, enviaremos um link para confirmação.\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📝 Fila de espera — {{clinic.name}}\n\nOlá {{patient.name}}!\nVocê entrou na fila de espera para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\nQuando a vaga ficar disponível, enviaremos um link para confirmação.\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'waitlist.offered' => [
            'label' => 'Oferta de vaga na fila de espera',
            'email' => [
                'subject' => '🎉 Vaga disponível — {{clinic.name}}',
                'content' => "🎉 Vaga disponível — {{clinic.name}}\n\nOlá {{patient.name}}!\nUma vaga foi liberada para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\n⏳ Confirme até: {{waitlist.offer_expires_at}}\n✅ Confirmar vaga: {{links.waitlist_offer}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🎉 Vaga disponível — {{clinic.name}}\n\nOlá {{patient.name}}!\nUma vaga foi liberada para:\n\n📅 Data: {{appointment.date}}\n🕐 Horário: {{appointment.time}}\n👨‍⚕️ {{labels.professional_singular}}: {{professional.name}}\n\n⏳ Confirme até: {{waitlist.offer_expires_at}}\n✅ Confirmar vaga: {{links.waitlist_offer}}\n\nAtenciosamente,\n{{clinic.name}}",
            ],
        ],

        'appointment.created.doctor' => [
            'label' => 'Novo agendamento para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '🩺 Novo agendamento na agenda — {{clinic.name}}',
                'content' => "🩺 Novo agendamento na agenda\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\nStatus: {{appointment.status}}\n\nDetalhes: {{links.appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🩺 Novo agendamento na agenda\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\nStatus: {{appointment.status}}\n\nDetalhes: {{links.appointment_details}}",
            ],
        ],

        'appointment.confirmed.doctor' => [
            'label' => 'Agendamento confirmado para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '✅ Agendamento confirmado na agenda',
                'content' => "✅ Agendamento confirmado\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "✅ Agendamento confirmado\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}",
            ],
        ],

        'appointment.canceled.doctor' => [
            'label' => 'Agendamento cancelado para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '❌ Agendamento cancelado na agenda',
                'content' => "❌ Agendamento cancelado\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\n\nStatus atual: {{appointment.status}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "❌ Agendamento cancelado\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\n\nStatus atual: {{appointment.status}}",
            ],
        ],

        'appointment.rescheduled.doctor' => [
            'label' => 'Agendamento remarcado para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '🔄 Agendamento remarcado na agenda',
                'content' => "🔄 Agendamento remarcado\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nNova data: {{appointment.date}}\nNovo horário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🔄 Agendamento remarcado\n\nPaciente: {{patient.name}}\nNova data: {{appointment.date}}\nNovo horário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nModalidade: {{appointment.mode}}\n\nDetalhes: {{links.appointment_details}}",
            ],
        ],

        'form.response_submitted.doctor' => [
            'label' => 'Resposta de formulário para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '📝 Nova resposta de formulário recebida',
                'content' => "📝 Nova resposta de formulário\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nEnviado em: {{response.submitted_at}}\n\nAbrir resposta: {{links.form_response}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📝 Nova resposta de formulário\n\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nEnviado em: {{response.submitted_at}}\n\nAbrir resposta: {{links.form_response}}",
            ],
        ],

        'waitlist.offered.doctor' => [
            'label' => 'Oferta de waitlist para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '📣 Vaga ofertada para paciente da sua agenda',
                'content' => "📣 Oferta de vaga enviada\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nValidade da oferta: {{waitlist.offer_expires_at}}\n\nLink enviado ao paciente: {{links.waitlist_offer}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📣 Oferta de vaga enviada\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\nValidade da oferta: {{waitlist.offer_expires_at}}\n\nLink enviado ao paciente: {{links.waitlist_offer}}",
            ],
        ],

        'waitlist.accepted.doctor' => [
            'label' => 'Oferta de waitlist aceita pelo paciente',
            'email' => [
                'subject' => '✅ Paciente aceitou oferta da fila de espera',
                'content' => "✅ Oferta aceita na fila de espera\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\n\nStatus do agendamento: {{appointment.status}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "✅ Oferta aceita na fila de espera\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nEspecialidade: {{doctor.specialty}}\n\nStatus do agendamento: {{appointment.status}}",
            ],
        ],

        'online_appointment.updated.doctor' => [
            'label' => 'Consulta online atualizada para {{labels.professional_singular_lower}}',
            'email' => [
                'subject' => '🧩 Atualização na consulta online',
                'content' => "🧩 Consulta online atualizada\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nModalidade: {{appointment.mode}}\nAplicativo: {{online.meeting_app}}\nLink da reunião: {{online.meeting_link}}\n\nDetalhes: {{links.online_appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "🧩 Consulta online atualizada\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nModalidade: {{appointment.mode}}\nAplicativo: {{online.meeting_app}}\nLink da reunião: {{online.meeting_link}}\n\nDetalhes: {{links.online_appointment_details}}",
            ],
        ],

        'online_appointment.instructions_sent.doctor' => [
            'label' => 'Instruções da consulta online enviadas',
            'email' => [
                'subject' => '📨 Instruções da consulta online enviadas',
                'content' => "📨 Instruções enviadas ao paciente\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nModalidade: {{appointment.mode}}\n\nStatus das instruções: {{online.instructions_sent}}\nÚltimo envio por e-mail: {{online.instructions_sent_email_at}}\nÚltimo envio por WhatsApp: {{online.instructions_sent_whatsapp_at}}\n\nDetalhes: {{links.online_appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📨 Instruções enviadas ao paciente\n\nPaciente: {{patient.name}}\nData: {{appointment.date}}\nHorário: {{appointment.time}}\nModalidade: {{appointment.mode}}\n\nStatus das instruções: {{online.instructions_sent}}\nÚltimo envio por e-mail: {{online.instructions_sent_email_at}}\nÚltimo envio por WhatsApp: {{online.instructions_sent_whatsapp_at}}\n\nDetalhes: {{links.online_appointment_details}}",
            ],
        ],

        'online_appointment.form_response_submitted.doctor' => [
            'label' => 'Resposta de formulário da consulta online',
            'email' => [
                'subject' => '📝 Resposta de formulário da consulta online',
                'content' => "📝 Nova resposta de formulário da consulta online\n\nOlá {{doctor.name}},\n\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nData/Hora da consulta: {{appointment.datetime}}\nEnviado em: {{response.submitted_at}}\n\nAbrir resposta: {{links.form_response}}\nDetalhes da consulta online: {{links.online_appointment_details}}\n\n{{clinic.name}}",
            ],
            'whatsapp' => [
                'content' => "📝 Nova resposta de formulário da consulta online\n\nPaciente: {{patient.name}}\nFormulário: {{form.name}}\nData/Hora da consulta: {{appointment.datetime}}\nEnviado em: {{response.submitted_at}}\n\nAbrir resposta: {{links.form_response}}\nDetalhes da consulta online: {{links.online_appointment_details}}",
            ],
        ],
    ],
];
