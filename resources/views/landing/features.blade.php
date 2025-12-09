@extends('landing.layout')

@section('title', 'Funcionalidades Completas - Sistema de Agendamentos')
@section('description', 'Conheça todas as funcionalidades do sistema completo de agendamentos para clínicas e profissionais de saúde.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-50 via-white to-blue-50 py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Funcionalidades <span class="text-blue-600">Completas</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Conheça todas as funcionalidades do sistema completo de agendamentos para clínicas, 
                    psicólogos, odontologias e profissionais de saúde.
                </p>
            </div>
        </div>
    </section>

    <!-- Funcionalidades Detalhadas -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Dashboard -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Dashboard</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Visão geral completa:</strong> Visualize estatísticas em tempo real sobre agendamentos, 
                        pacientes, médicos e receita da clínica.
                    </p>
                    <p class="text-sm text-gray-500">
                        Métricas importantes, gráficos de desempenho e indicadores-chave de performance (KPIs) 
                        para tomar decisões baseadas em dados.
                    </p>
                </div>

                <!-- Gerenciamento de Usuários -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Gerenciamento de Usuários</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Controle total:</strong> Gerencie usuários do sistema com diferentes níveis de acesso 
                        e permissões por módulo.
                    </p>
                    <p class="text-sm text-gray-500">
                        Sistema de roles (admin, doctor, user) com filtros automáticos e permissões granulares 
                        para garantir segurança e organização.
                    </p>
                </div>

                <!-- Médicos e Especialidades -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Médicos e Especialidades</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Cadastro completo:</strong> Gerencie profissionais de saúde com personalização de labels, 
                        assinatura digital e campos de registro personalizados.
                    </p>
                    <p class="text-sm text-gray-500">
                        Suporte para diferentes tipos de profissionais (médicos, dentistas, psicólogos) com 
                        personalização de terminologia e múltiplas especialidades por profissional.
                    </p>
                </div>

                <!-- Pacientes -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pacientes</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Cadastro completo:</strong> Gerencie pacientes com dados completos, histórico de atendimentos 
                        e possibilidade de acesso ao portal.
                    </p>
                    <p class="text-sm text-gray-500">
                        Cadastro com CPF, dados pessoais, contatos e habilitação de login no portal do paciente 
                        com credenciais enviadas automaticamente.
                    </p>
                </div>

                <!-- Calendários -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Calendários</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Múltiplos calendários:</strong> Crie calendários individuais para cada médico com 
                        visualização em formato de agenda ou calendário mensal.
                    </p>
                    <p class="text-sm text-gray-500">
                        Visualização FullCalendar com eventos, arrastar e soltar, e sincronização automática 
                        com Google Calendar.
                    </p>
                </div>

                <!-- Horários Comerciais -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Horários Comerciais</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Configuração flexível:</strong> Defina horários de atendimento por dia da semana 
                        com intervalos personalizados.
                    </p>
                    <p class="text-sm text-gray-500">
                        Configure horários de abertura, fechamento e intervalos entre consultas para cada médico, 
                        garantindo disponibilidade correta para agendamentos.
                    </p>
                </div>

                <!-- Tipos de Consulta -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tipos de Consulta</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Diversos tipos:</strong> Crie diferentes tipos de consulta com durações variadas 
                        e descrições personalizadas.
                    </p>
                    <p class="text-sm text-gray-500">
                        Configure tipos como consulta inicial, retorno, procedimento, etc., cada um com duração 
                        específica e vinculação a médicos e especialidades.
                    </p>
                </div>

                <!-- Agendamentos Presenciais -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agendamentos Presenciais</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Agendamento completo:</strong> Gerencie consultas presenciais com seleção de paciente, 
                        médico, tipo de consulta, data e horário.
                    </p>
                    <p class="text-sm text-gray-600">
                        Sistema completo de agendamento com verificação de disponibilidade, bloqueio de horários, 
                        status de atendimento e histórico completo.
                    </p>
                </div>

                <!-- Agendamentos Online -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agendamentos Online</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Consultas virtuais:</strong> Gerencie consultas online com links de videoconferência, 
                        instruções personalizadas e envio automático.
                    </p>
                    <p class="text-sm text-gray-500">
                        Configure links de reunião (Zoom, Google Meet, etc.), adicione instruções e envie 
                        automaticamente por email ou WhatsApp para os pacientes.
                    </p>
                </div>

                <!-- Agendamentos Recorrentes -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Agendamentos Recorrentes</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Recorrência avançada:</strong> Crie agendamentos recorrentes com regras personalizadas 
                        de frequência e término.
                    </p>
                    <p class="text-sm text-gray-500">
                        Configure recorrências diárias, semanais ou mensais com múltiplas regras, término por data 
                        ou número de sessões, e geração automática de agendamentos.
                    </p>
                </div>

                <!-- Atendimento Médico -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Atendimento Médico</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Sessão diária:</strong> Módulo completo para gerenciar a sessão de atendimentos do dia 
                        com atualização de status em tempo real.
                    </p>
                    <p class="text-sm text-gray-500">
                        Visualize todos os agendamentos do dia, atualize status (agendado, chegou, em atendimento, 
                        concluído), veja formulários respondidos e navegue entre atendimentos.
                    </p>
                </div>

                <!-- Formulários Personalizados -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Formulários Personalizados</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Criador visual:</strong> Crie formulários completos com seções, perguntas e múltiplos 
                        tipos de resposta.
                    </p>
                    <p class="text-sm text-gray-500">
                        Construtor visual de formulários com seções organizadas, perguntas de múltiplos tipos 
                        (texto, múltipla escolha, checkbox, etc.) e envio automático após agendamento.
                    </p>
                </div>

                <!-- Respostas de Formulários -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Respostas de Formulários</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Gerenciamento completo:</strong> Visualize e gerencie todas as respostas de formulários 
                        com filtros avançados.
                    </p>
                    <p class="text-sm text-gray-500">
                        Acompanhe respostas de formulários, visualize dados coletados, relacione com agendamentos 
                        e exporte dados quando necessário.
                    </p>
                </div>

                <!-- Sincronização Google Calendar -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sincronização Google Calendar</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Sincronização automática:</strong> Sincronize agendamentos automaticamente com o 
                        Google Calendar de cada médico.
                    </p>
                    <p class="text-sm text-gray-500">
                        Cada médico conecta sua própria conta Google, e os agendamentos são sincronizados 
                        automaticamente (criação, edição, cancelamento). Suporte para eventos recorrentes com RRULE.
                    </p>
                </div>

                <!-- Portal do Paciente -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Portal do Paciente</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Acesso completo:</strong> Portal personalizado para pacientes visualizarem agendamentos, 
                        histórico e perfil.
                    </p>
                    <p class="text-sm text-gray-500">
                        Dashboard do paciente, lista de agendamentos, criação e edição de agendamentos, 
                        notificações e gerenciamento de perfil com recuperação de senha.
                    </p>
                </div>

                <!-- Área Pública de Agendamento -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Área Pública de Agendamento</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Agendamento sem login:</strong> Pacientes podem agendar consultas sem precisar estar 
                        logados no sistema.
                    </p>
                    <p class="text-sm text-gray-500">
                        Fluxo completo de identificação, cadastro (se necessário), seleção de médico, tipo de consulta, 
                        data e horário disponível, tudo sem necessidade de autenticação.
                    </p>
                </div>

                <!-- Relatórios Completos -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Relatórios Completos</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Análise completa:</strong> Gere relatórios detalhados de agendamentos, pacientes, 
                        médicos, formulários e muito mais.
                    </p>
                    <p class="text-sm text-gray-500">
                        Relatórios com filtros avançados e exportação em múltiplos formatos (Excel, PDF, CSV) 
                        para análises externas e apresentações.
                    </p>
                </div>

                <!-- Sistema de Roles & Permissões -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sistema de Roles & Permissões</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Controle granular:</strong> Sistema completo de roles (admin, doctor, user) com 
                        permissões por módulo e filtros automáticos.
                    </p>
                    <p class="text-sm text-gray-500">
                        Controle de acesso baseado em papéis com filtros automáticos de dados, permissões por 
                        módulo e controle de médicos permitidos para usuários comuns.
                    </p>
                </div>

                <!-- Notificações -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Notificações</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Comunicação automática:</strong> Sistema completo de notificações por email e WhatsApp 
                        para pacientes e médicos.
                    </p>
                    <p class="text-sm text-gray-500">
                        Notificações automáticas de agendamentos, lembretes, formulários e instruções de consultas online 
                        com configurações flexíveis por clínica.
                    </p>
                </div>

                <!-- Configurações Avançadas -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Configurações Avançadas</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Personalização completa:</strong> Configure modo de atendimento, notificações, 
                        profissionais e integrações.
                    </p>
                    <p class="text-sm text-gray-500">
                        Configurações de agendamentos, notificações, profissionais personalizados, integrações 
                        (Google Calendar, WhatsApp, Email) e muito mais.
                    </p>
                </div>

                <!-- Banco de Dados Isolado -->
                <div class="bg-gray-50 rounded-lg p-8 hover:shadow-lg transition-shadow">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Ambiente de Dados Isolado</h3>
                    <p class="text-gray-600 mb-4">
                        <strong>Segurança total:</strong> Cada clínica possui seu próprio ambiente isolado 
                        garantindo privacidade e segurança.
                    </p>
                    <p class="text-sm text-gray-500">
                        Ambiente completamente isolado por clínica, criado automaticamente após 
                        o pagamento, garantindo total separação e segurança dos dados.
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
