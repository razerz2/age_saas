@extends('landing.layout')

@section('title', 'Sistema de Agendamentos para Clínicas e Profissionais de Saúde')
@section('description', 'Sistema completo de agendamentos para clínicas, psicólogos, odontologias e profissionais de saúde. Agende consultas presenciais e online, gerencie pacientes, médicos e muito mais.')

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-50 via-white to-blue-50 py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    O sistema completo de agendamentos para
                    <span class="text-blue-600">clínicas, psicólogos, odontologias</span>
                    e profissionais de saúde
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Agende consultas presenciais e online, gerencie pacientes, médicos, formulários, calendários e muito mais — 
                    tudo em um único sistema SaaS multi-tenant.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#pre-cadastro" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors shadow-lg hover:shadow-xl">
                        Testar Agora
                    </a>
                    <a href="{{ route('landing.features') }}" class="bg-white hover:bg-gray-50 text-blue-600 border-2 border-blue-600 px-8 py-4 rounded-lg font-semibold text-lg transition-colors">
                        Ver Funcionalidades
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Como Funciona -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Como Funciona</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Em apenas 3 passos simples, sua clínica está pronta para usar o sistema completo
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Passo 1 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Clínica cria conta</h3>
                    <p class="text-gray-600">
                        Faça seu pré-cadastro, escolha o plano ideal e realize o pagamento
                    </p>
                </div>
                
                <!-- Passo 2 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sistema cria automaticamente o banco do tenant</h3>
                    <p class="text-gray-600">
                        Após o pagamento, o sistema cria automaticamente seu banco de dados PostgreSQL isolado
                    </p>
                </div>
                
                <!-- Passo 3 -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full text-2xl font-bold mb-4">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Começa a usar com dashboard completo</h3>
                    <p class="text-gray-600">
                        Acesse o sistema com as credenciais enviadas por email e comece a gerenciar sua clínica
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Principais Funcionalidades -->
    <section class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Principais Funcionalidades</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Tudo que você precisa para gerenciar sua clínica de forma eficiente e profissional
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Funcionalidade 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Dashboard com Estatísticas</h3>
                    <p class="text-gray-600">Visualize métricas importantes em tempo real sobre agendamentos, pacientes e receita</p>
                </div>
                
                <!-- Funcionalidade 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Agendamentos Presencial e Online</h3>
                    <p class="text-gray-600">Gerencie consultas presenciais e virtuais com links de videoconferência integrados</p>
                </div>
                
                <!-- Funcionalidade 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Recorrências Avançadas</h3>
                    <p class="text-gray-600">Crie agendamentos recorrentes com regras personalizadas (diária, semanal, mensal)</p>
                </div>
                
                <!-- Funcionalidade 4 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Atendimento Médico do Dia</h3>
                    <p class="text-gray-600">Módulo completo para gerenciar a sessão diária de atendimentos com status em tempo real</p>
                </div>
                
                <!-- Funcionalidade 5 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Formulários Automatizados</h3>
                    <p class="text-gray-600">Crie formulários personalizados que são enviados automaticamente aos pacientes após agendamento</p>
                </div>
                
                <!-- Funcionalidade 6 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Portal do Paciente</h3>
                    <p class="text-gray-600">Permita que pacientes acessem seus agendamentos, histórico e notificações</p>
                </div>
                
                <!-- Funcionalidade 7 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Área Pública de Agendamento</h3>
                    <p class="text-gray-600">Pacientes podem agendar consultas sem precisar estar logados no sistema</p>
                </div>
                
                <!-- Funcionalidade 8 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Integração Google Calendar</h3>
                    <p class="text-gray-600">Sincronize automaticamente agendamentos com o Google Calendar de cada médico</p>
                </div>
                
                <!-- Funcionalidade 9 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Notificações Automáticas</h3>
                    <p class="text-gray-600">Envie notificações por email e WhatsApp automaticamente para pacientes e médicos</p>
                </div>
                
                <!-- Funcionalidade 10 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Controle de Acesso por Módulos</h3>
                    <p class="text-gray-600">Sistema de roles e permissões para controlar acesso a funcionalidades específicas</p>
                </div>
                
                <!-- Funcionalidade 11 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Relatórios Completos</h3>
                    <p class="text-gray-600">Gere relatórios de agendamentos, pacientes, médicos e formulários exportáveis em Excel/PDF</p>
                </div>
                
                <!-- Funcionalidade 12 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Banco Isolado por Tenant</h3>
                    <p class="text-gray-600">Cada clínica possui seu próprio banco PostgreSQL isolado garantindo total segurança dos dados</p>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('landing.features') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    Ver todas as funcionalidades →
                </a>
            </div>
        </div>
    </section>

    <!-- Módulo de Atendimento Médico -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Módulo de Atendimento Médico</h2>
                    <p class="text-lg text-gray-600 mb-6">
                        O módulo de Atendimento Médico permite realizar sessões de atendimento do dia, facilitando o fluxo de trabalho durante o atendimento aos pacientes.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Lista todos os agendamentos do dia filtrados conforme permissões</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Atualiza status do atendimento em tempo real (agendado, chegou, em atendimento, concluído)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Permite visualizar formulários respondidos diretamente durante o atendimento</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Navegação automática entre agendamentos após conclusão</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-100 rounded-lg p-8 flex items-center justify-center">
                    <p class="text-gray-500 text-center">Screenshot do módulo de atendimento médico</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Agendamentos Online -->
    <section class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1 bg-gray-100 rounded-lg p-8 flex items-center justify-center">
                    <p class="text-gray-500 text-center">Screenshot de agendamento online</p>
                </div>
                <div class="order-1 lg:order-2">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Agendamentos Online</h2>
                    <p class="text-lg text-gray-600 mb-6">
                        Gerencie consultas virtuais com toda a praticidade e funcionalidades necessárias para atendimentos online.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Configure link de videoconferência (Zoom, Google Meet, etc.)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Adicione instruções personalizadas para o paciente</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Envio automático via email ou WhatsApp com todas as informações</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700">Área de administração exclusiva para gerenciar consultas online</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal do Paciente -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Portal do Paciente</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Ofereça uma experiência completa aos seus pacientes com acesso personalizado
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gray-50 p-6 rounded-lg text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Dashboard</h3>
                    <p class="text-sm text-gray-600">Visão geral de agendamentos e informações importantes</p>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Meus Agendamentos</h3>
                    <p class="text-sm text-gray-600">Visualize, crie e gerencie seus agendamentos</p>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Notificações</h3>
                    <p class="text-sm text-gray-600">Receba alertas sobre seus agendamentos e lembretes</p>
                </div>
                
                <div class="bg-gray-50 p-6 rounded-lg text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Perfil</h3>
                    <p class="text-sm text-gray-600">Atualize seus dados pessoais e recupere senha</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Formulários Personalizados -->
    <section class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Formulários Personalizados</h2>
                    <p class="text-lg text-gray-600 mb-6">
                        Crie formulários completos e personalizados que são enviados automaticamente aos pacientes após cada agendamento.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700"><strong>Criador visual</strong> com seções, perguntas e múltiplos tipos de resposta</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700"><strong>Envio automático</strong> após criação do agendamento via email ou WhatsApp</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700"><strong>Vinculação inteligente</strong> com médicos e especialidades</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-700"><strong>Respostas públicas</strong> - pacientes respondem sem precisar fazer login</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-gray-100 rounded-lg p-8 flex items-center justify-center">
                    <p class="text-gray-500 text-center">Screenshot do construtor de formulários</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Relatórios Avançados -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Relatórios Avançados</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Analise e exporte dados completos do seu negócio em múltiplos formatos
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Agendamentos</h3>
                    <p class="text-sm text-gray-600">Relatórios completos com filtros avançados por período, médico, status e modo</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Pacientes</h3>
                    <p class="text-sm text-gray-600">Listagem completa com histórico de atendimentos e informações detalhadas</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Formulários</h3>
                    <p class="text-sm text-gray-600">Análise de respostas e estatísticas de formulários respondidos</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Portal do Paciente</h3>
                    <p class="text-sm text-gray-600">Estatísticas de uso e ações realizadas pelos pacientes</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Recorrências</h3>
                    <p class="text-sm text-gray-600">Relatórios de agendamentos recorrentes e regras aplicadas</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Exportação</h3>
                    <p class="text-sm text-gray-600">Exporte em Excel, PDF ou CSV para análises externas</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Segurança -->
    <section class="py-16 lg:py-24 bg-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Segurança e Isolamento de Dados</h2>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                    Cada clínica possui seu próprio ambiente isolado garantindo total privacidade e segurança
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Banco Isolado</h3>
                    <p class="text-blue-100 text-sm">Cada tenant possui seu próprio banco PostgreSQL isolado</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Autenticação Separada</h3>
                    <p class="text-blue-100 text-sm">Sistema de guards isolados por tenant</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Filtros Automáticos</h3>
                    <p class="text-blue-100 text-sm">Baseados em roles para garantir acesso seguro</p>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Permissões por Módulo</h3>
                    <p class="text-blue-100 text-sm">Controle granular de acesso a funcionalidades</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrações -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Integrações</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Conecte-se com as ferramentas que você já usa no dia a dia
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2c0 .55.45 1 1 1h.5c.25 0 .5-.1.7-.29L13.9 18H20c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 13H13l-2 2v-2H4V4h16v11z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Google Calendar</h3>
                    <p class="text-gray-600">Sincronização automática de agendamentos com o calendário do Google</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Apple CalDAV</h3>
                    <p class="text-gray-600">Integração com iCloud usando protocolo CalDAV</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">WhatsApp API</h3>
                    <p class="text-gray-600">Envio automático de notificações e lembretes via WhatsApp</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Planos (Resumo) -->
    @if($plans && $plans->count() > 0)
    <section class="py-16 lg:py-24 bg-gray-50" id="planos">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Planos e Preços</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Escolha o plano ideal para sua clínica
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($plans->take(3) as $plan)
                <div class="bg-white rounded-lg shadow-lg p-8 {{ $loop->index === 1 ? 'ring-2 ring-blue-600 transform scale-105' : '' }}">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                        <div class="text-4xl font-bold text-blue-600 mb-2">
                            {{ $plan->formatted_price }}
                        </div>
                        <p class="text-gray-600">por mês</p>
                    </div>
                    
                    <ul class="space-y-3 mb-8">
                        @if($plan->features)
                            @foreach(array_slice($plan->features, 0, 5) as $feature)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-700">{{ $feature }}</span>
                            </li>
                            @endforeach
                        @endif
                    </ul>
                    
                    <a href="{{ route('landing.plans') }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        Escolher Plano
                    </a>
                </div>
                @endforeach
            </div>
            
            <div class="text-center mt-12">
                <a href="{{ route('landing.plans') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    Ver todos os planos e detalhes →
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- FAQ -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Perguntas Frequentes</h2>
                <p class="text-xl text-gray-600">
                    Tire suas dúvidas sobre o sistema
                </p>
            </div>
            
            <div class="space-y-6">
                <details class="group bg-gray-50 rounded-lg p-6">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none">
                        <span class="flex items-center justify-between">
                            Como funciona o sistema multi-tenant?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-4 text-gray-600">
                        Cada clínica (tenant) possui seu próprio banco de dados PostgreSQL completamente isolado. 
                        Isso garante total segurança e privacidade dos dados, sem compartilhamento entre diferentes clientes.
                    </p>
                </details>
                
                <details class="group bg-gray-50 rounded-lg p-6">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none">
                        <span class="flex items-center justify-between">
                            Posso personalizar o sistema para minha clínica?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-4 text-gray-600">
                        Sim! O sistema permite personalização de labels (médico, dentista, psicólogo, etc.), 
                        campos de registro profissional (CRM, CRP, CRO), e configurações específicas para cada tipo de clínica.
                    </p>
                </details>
                
                <details class="group bg-gray-50 rounded-lg p-6">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none">
                        <span class="flex items-center justify-between">
                            Como funciona o pré-cadastro e criação do tenant?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-4 text-gray-600">
                        Após realizar o pré-cadastro e pagamento, o sistema cria automaticamente o banco de dados, 
                        executa todas as migrações e configura o ambiente completo. Você receberá por email as credenciais 
                        de acesso com usuário admin já criado.
                    </p>
                </details>
                
                <details class="group bg-gray-50 rounded-lg p-6">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none">
                        <span class="flex items-center justify-between">
                            Os agendamentos online funcionam com quais plataformas?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-4 text-gray-600">
                        O sistema é compatível com qualquer plataforma de videoconferência que forneça links de reunião, 
                        como Zoom, Google Meet, Microsoft Teams, etc. Basta configurar o link e as instruções no agendamento online.
                    </p>
                </details>
                
                <details class="group bg-gray-50 rounded-lg p-6">
                    <summary class="font-semibold text-gray-900 cursor-pointer list-none">
                        <span class="flex items-center justify-between">
                            Como funciona a sincronização com Google Calendar?
                            <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-4 text-gray-600">
                        Cada médico pode conectar sua própria conta do Google Calendar. Os agendamentos são sincronizados 
                        automaticamente - quando você cria, edita ou cancela um agendamento no sistema, o evento é atualizado 
                        automaticamente no Google Calendar.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-16 lg:py-24 bg-gradient-to-r from-blue-600 to-blue-700 text-white" id="pre-cadastro">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Pronto para transformar sua clínica?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Comece hoje mesmo e veja como é fácil gerenciar agendamentos de forma profissional
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing.plans') }}" class="bg-white hover:bg-gray-100 text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg transition-colors shadow-lg">
                    Ver Planos e Preços
                </a>
                <a href="{{ route('landing.contact') }}" class="bg-blue-500 hover:bg-blue-400 text-white border-2 border-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors">
                    Falar com Comercial
                </a>
            </div>
        </div>
    </section>
@endsection
