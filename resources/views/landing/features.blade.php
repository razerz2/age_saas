@extends('landing.layout')

@section('title', 'Funcionalidades Completas - Sistema de Agendamentos')
@section('description', 'Conheça as funcionalidades do sistema de agendamentos para clínicas e profissionais de saúde, com operação, comunicação e agenda em um só lugar.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-50 via-white to-blue-50 py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Funcionalidades <span class="text-blue-600">Completas</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Conheça as funcionalidades do sistema para clínicas, consultórios e profissionais de saúde,
                    com agenda, atendimento, formulários, campanhas e relatórios em um único ambiente.
                </p>
            </div>
        </div>
    </section>

    <!-- Funcionalidades Detalhadas -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Dashboard</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Visão operacional do Tenant:</strong> acompanhe indicadores de uso, rotina de agendamentos e acessos rápidos aos módulos principais.
                    </p>
                    <p class="text-sm text-gray-500">
                        Um ponto central para abrir o dia de trabalho, navegar pelo menu e acompanhar o que precisa de ação.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Usuários e Permissões</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Controle de acesso por papel:</strong> organize administradores, profissionais e usuários operacionais com permissões por módulo.
                    </p>
                    <p class="text-sm text-gray-500">
                        O sistema trabalha com roles e filtros de acesso para manter cada pessoa focada apenas no que precisa operar.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Profissionais e Especialidades</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Cadastros clínicos organizados:</strong> vincule profissionais, especialidades e dados necessários para atendimento e agenda.
                    </p>
                    <p class="text-sm text-gray-500">
                        O produto suporta terminologia personalizada para diferentes áreas da saúde e organização por especialidade.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pacientes</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Cadastro completo:</strong> mantenha dados pessoais, contatos e vínculo com agendamentos, formulários e portal do paciente.
                    </p>
                    <p class="text-sm text-gray-500">
                        Uma base organizada para atendimento, comunicação e acompanhamento da jornada do paciente.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Calendários</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Visualização da agenda:</strong> acompanhe os compromissos por profissional e navegue pelos registros do calendário de atendimento.
                    </p>
                    <p class="text-sm text-gray-500">
                        Uma visão prática para consultar a agenda, revisar eventos e apoiar a operação diária da clínica.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agenda do Profissional</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Configuração central da disponibilidade:</strong> monte a agenda do profissional com horários, tipos de consulta e status da agenda.
                    </p>
                    <p class="text-sm text-gray-500">
                        O fluxo atual concentra a estrutura que sustenta agendamentos presenciais, online e recorrentes.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Horários Comerciais</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Disponibilidade por dia da semana:</strong> defina faixas de atendimento e intervalos conforme a rotina de cada profissional.
                    </p>
                    <p class="text-sm text-gray-500">
                        Ideal para padronizar a oferta de horários e reduzir conflitos na marcação de consultas.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tipos de Consulta</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Duração e regras por atendimento:</strong> cadastre tipos de consulta com tempo previsto e uso por profissional.
                    </p>
                    <p class="text-sm text-gray-500">
                        Uma forma simples de padronizar agendas, organizar encaixes e refletir a rotina real do serviço.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agendamentos</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Fluxo operacional completo:</strong> registre consultas com paciente, profissional, especialidade, tipo, data, horário e modo de atendimento.
                    </p>
                    <p class="text-sm text-gray-500">
                        O sistema apoia a rotina da recepção e da equipe com status, detalhes do agendamento e histórico de acompanhamento.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agendamentos Recorrentes</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Repetição com controle:</strong> crie séries de atendimentos com regras de frequência, data inicial e critérios de término.
                    </p>
                    <p class="text-sm text-gray-500">
                        Indicado para acompanhamentos periódicos, sessões contínuas e rotinas que exigem previsibilidade na agenda.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Consultas Online</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Atendimento remoto com instruções:</strong> organize links, aplicativo de videoconferência e orientações para o paciente.
                    </p>
                    <p class="text-sm text-gray-500">
                        O fluxo inclui registro das instruções e envio por canais de comunicação quando habilitados no Tenant.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Atendimento</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Sessão do dia organizada:</strong> acompanhe a fila de atendimentos, atualize status e visualize informações do agendamento em contexto.
                    </p>
                    <p class="text-sm text-gray-500">
                        O módulo foi pensado para apoiar a rotina operacional de quem conduz o dia de atendimento.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Formulários</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Coleta estruturada de informações:</strong> crie formulários com seções, perguntas e múltiplos tipos de resposta.
                    </p>
                    <p class="text-sm text-gray-500">
                        Um recurso útil para pré-atendimento, triagem, coleta de dados e padronização de processos internos.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Respostas</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Acompanhamento do que foi preenchido:</strong> visualize respostas recebidas, status e vínculos com pacientes e agendamentos.
                    </p>
                    <p class="text-sm text-gray-500">
                        Um ponto central para consulta rápida dos dados coletados antes ou durante o atendimento.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882l-2 .756A2 2 0 007 8.507V17a2 2 0 002 2h8a2 2 0 002-2v-8.493a2 2 0 00-1.999-1.869l-2-.756M11 5.882A2 2 0 0112 5a2 2 0 011 .882M11 5.882V4a1 1 0 112 0v1.882" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Campanhas</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Comunicação ativa com a base:</strong> crie campanhas, faça envio de teste e acompanhe o público alcançado.
                    </p>
                    <p class="text-sm text-gray-500">
                        O sistema já contempla fluxo de criação, agendamento, início, pausa, retomada e acompanhamento de execuções e destinatários.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Templates de Campanha</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Mensagens reaproveitáveis:</strong> organize templates usados nas campanhas para acelerar a comunicação e manter consistência.
                    </p>
                    <p class="text-sm text-gray-500">
                        O fluxo se adapta ao provedor configurado e ajuda a padronizar o conteúdo usado nas campanhas do Tenant.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sincronização de Agenda</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Fluxo próprio para integrações de calendário:</strong> conecte a agenda do profissional e acompanhe o status de sincronização.
                    </p>
                    <p class="text-sm text-gray-500">
                        O produto já organiza esse processo em uma área específica, com suporte ao fluxo de conexão e revisão da última sincronização.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notificações e Comunicação</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Email e WhatsApp na operação:</strong> trabalhe com notificações, lembretes e testes de comunicação a partir das configurações do Tenant.
                    </p>
                    <p class="text-sm text-gray-500">
                        A estrutura atual contempla canais e provedores configuráveis para suportar a comunicação com pacientes e equipe.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Portal do Paciente</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Autonomia para o paciente:</strong> ofereça uma área própria para acompanhar agendamentos e acessar a experiência digital do serviço.
                    </p>
                    <p class="text-sm text-gray-500">
                        O portal complementa a operação da clínica e amplia o relacionamento digital com o paciente.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Área Pública de Agendamento</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Agendamento sem login:</strong> permita que novos pacientes ou pacientes identificados avancem no fluxo público de marcação.
                    </p>
                    <p class="text-sm text-gray-500">
                        Um recurso útil para facilitar a captação e reduzir barreiras na marcação inicial.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Relatórios</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Análise por área:</strong> acompanhe relatórios de agendamentos, pacientes, profissionais, recorrências, formulários e notificações.
                    </p>
                    <p class="text-sm text-gray-500">
                        O sistema também conta com exportação em formatos usados na rotina administrativa e gerencial.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Configurações</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Parâmetros do Tenant em um só lugar:</strong> ajuste preferências operacionais, comunicação, integrações e regras do ambiente.
                    </p>
                    <p class="text-sm text-gray-500">
                        Uma área essencial para adaptar o produto ao modelo de atendimento e à estrutura de cada operação.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-10V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Financeiro</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Módulo opcional para gestão financeira:</strong> quando habilitado no Tenant, apoia contas, categorias, transações e relatórios financeiros.
                    </p>
                    <p class="text-sm text-gray-500">
                        Um complemento comercial relevante para operações que desejam concentrar agenda e gestão em um mesmo sistema.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Ambiente de Dados Isolado</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Estrutura multi-tenant:</strong> cada operação trabalha em um ambiente próprio, com separação lógica e organização independente.
                    </p>
                    <p class="text-sm text-gray-500">
                        Isso ajuda a sustentar privacidade, segurança e autonomia por clínica ou unidade.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-blue-600 to-blue-700 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Pronto para usar todas essas funcionalidades?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Comece agora e tenha acesso a todas essas funcionalidades
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing.plans') }}" class="bg-white hover:bg-gray-100 text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg transition-colors shadow-lg">
                    Ver Planos
                </a>
                <a href="{{ route('landing.home') }}" class="bg-blue-500 hover:bg-blue-400 text-white border-2 border-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors">
                    Voltar para Início
                </a>
            </div>
        </div>
    </section>
@endsection
