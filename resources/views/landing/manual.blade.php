@extends('landing.layout')

@section('title', 'Manual do Sistema')
@section('description', 'Manual prático do Tenant com os módulos e fluxos atuais do sistema.')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <section class="relative bg-gradient-to-br from-blue-50 via-white to-blue-50 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="mb-6 text-4xl font-bold text-gray-900 md:text-5xl lg:text-6xl">Manual do Sistema</h1>
            <p class="mx-auto max-w-3xl text-xl text-gray-600">
                Guia rápido dos módulos atuais do Tenant. Use o índice abaixo ou o botão <strong>Ajuda</strong> dentro das telas para abrir a seção certa.
            </p>
        </div>
    </section>

    <section class="border-b border-gray-200 bg-white py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-blue-50 p-6">
                <h2 class="mb-4 text-2xl font-bold text-gray-900">Índice</h2>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <a href="#primeiros-passos" class="text-blue-600 hover:text-blue-700 hover:underline">1. Primeiros Passos</a>
                    <a href="#dashboard" class="text-blue-600 hover:text-blue-700 hover:underline">2. Dashboard</a>
                    <a href="#atendimento" class="text-blue-600 hover:text-blue-700 hover:underline">3. Atendimento</a>
                    <a href="#agendamentos" class="text-blue-600 hover:text-blue-700 hover:underline">4. Agendamentos</a>
                    <a href="#agendamentos-recorrentes" class="text-blue-600 hover:text-blue-700 hover:underline">5. Agendamentos Recorrentes</a>
                    <a href="#consultas-online" class="text-blue-600 hover:text-blue-700 hover:underline">6. Consultas Online</a>
                    <a href="#campanhas" class="text-blue-600 hover:text-blue-700 hover:underline">7. Campanhas</a>
                    <a href="#templates-campanhas" class="text-blue-600 hover:text-blue-700 hover:underline">8. Templates de Campanhas</a>
                    <a href="#pacientes" class="text-blue-600 hover:text-blue-700 hover:underline">9. Pacientes</a>
                    <a href="#especialidades" class="text-blue-600 hover:text-blue-700 hover:underline">10. Especialidades</a>
                    <a href="#usuarios-e-permissoes" class="text-blue-600 hover:text-blue-700 hover:underline">11. Usuários e Permissões</a>
                    <a href="#agenda-profissional" class="text-blue-600 hover:text-blue-700 hover:underline">12. Agenda do Profissional</a>
                    <a href="#formularios" class="text-blue-600 hover:text-blue-700 hover:underline">13. Formulários</a>
                    <a href="#respostas" class="text-blue-600 hover:text-blue-700 hover:underline">14. Respostas</a>
                    <a href="#integracoes-e-sincronizacao" class="text-blue-600 hover:text-blue-700 hover:underline">15. Integrações e Sincronização</a>
                    <a href="#relatorios" class="text-blue-600 hover:text-blue-700 hover:underline">16. Relatórios</a>
                    <a href="#configuracoes" class="text-blue-600 hover:text-blue-700 hover:underline">17. Configurações</a>
                </div>
            </div>
        </div>
    </section>

    @php
        $sections = [
            'primeiros-passos' => [
                'title' => '1. Primeiros Passos',
                'bg' => 'bg-white',
                'intro' => 'Antes de operar o Tenant, revise configurações, usuários, permissões e agenda do profissional.',
                'items' => [
                    'Comece pelas Configurações gerais do Tenant.',
                    'Cadastre usuários, permissões e especialidades antes de abrir a agenda.',
                    'Use o botão Ajuda das telas principais para voltar ao ponto exato deste manual.',
                ],
            ],
            'dashboard' => [
                'title' => '2. Dashboard',
                'bg' => 'bg-gray-50',
                'intro' => 'O Dashboard é o ponto de entrada após o login e reflete os módulos liberados para o usuário atual.',
                'items' => [
                    'Os atalhos e itens do menu podem variar conforme papel, módulos atribuídos e configurações do Tenant.',
                    'Quando um módulo não aparecer, verifique permissões do usuário e habilitação do próprio Tenant.',
                ],
            ],
            'atendimento' => [
                'title' => '3. Atendimento',
                'bg' => 'bg-white',
                'intro' => 'Use o módulo Atendimento para abrir a sessão do dia e acompanhar a fila de atendimentos.',
                'items' => [
                    'Selecione a data da sessão e, quando permitido, um ou mais profissionais.',
                    'A sessão lista os agendamentos do dia e permite consultar detalhes, atualizar status e reorganizar a fila.',
                ],
            ],
            'agendamentos' => [
                'title' => '4. Agendamentos',
                'bg' => 'bg-gray-50',
                'intro' => 'Registre agendamentos avulsos com paciente, profissional, especialidade, tipo de consulta, data, horário e modo.',
                'items' => [
                    'Os agendamentos podem ser presenciais ou online.',
                    'A disponibilidade depende da agenda configurada para o profissional.',
                ],
            ],
            'agendamentos-recorrentes' => [
                'title' => '5. Agendamentos Recorrentes',
                'bg' => 'bg-white',
                'intro' => 'Use este módulo para atendimentos periódicos com repetição automática.',
                'items' => [
                    'Defina data inicial, regra de repetição e forma de término da recorrência.',
                    'A tela de detalhes mostra regras aplicadas, status e sessões geradas.',
                ],
            ],
            'consultas-online' => [
                'title' => '6. Consultas Online',
                'bg' => 'bg-gray-50',
                'intro' => 'Esse módulo aparece quando o Tenant permite consultas online e lista somente agendamentos nesse modo.',
                'items' => [
                    'Cadastre link da reunião, aplicativo e instruções para o paciente.',
                    'Se notificações estiverem habilitadas, as instruções podem ser enviadas por email e WhatsApp.',
                ],
            ],
            'campanhas' => [
                'title' => '7. Campanhas',
                'bg' => 'bg-white',
                'intro' => 'O módulo depende dos canais configurados no Tenant. Sem canais disponíveis, a própria tela informa o bloqueio.',
                'items' => [
                    'Monte a campanha com nome, canais, conteúdo, público e regras.',
                    'Acompanhe os dados da campanha, execuções e destinatários nas telas de detalhes.',
                ],
            ],
            'templates-campanhas' => [
                'title' => '8. Templates de Campanhas',
                'bg' => 'bg-gray-50',
                'intro' => 'Os templates complementam campanhas e variam conforme o provedor de WhatsApp usado pelo Tenant.',
                'items' => [
                    'No modo oficial, os templates vêm do catálogo aprovado e o cadastro local fica bloqueado.',
                    'No modo não oficial, é possível criar e editar templates locais com conteúdo e variáveis.',
                ],
            ],
            'pacientes' => [
                'title' => '9. Pacientes',
                'bg' => 'bg-white',
                'intro' => 'O cadastro de pacientes sustenta agendamentos, respostas de formulários, atendimento e notificações.',
                'items' => [
                    'Mantenha email e telefone atualizados para consultas online e comunicações.',
                ],
            ],
            'especialidades' => [
                'title' => '10. Especialidades',
                'bg' => 'bg-gray-50',
                'intro' => 'Use Especialidades para organizar o cadastro clínico e facilitar filtros em agenda, agendamentos e relatórios.',
                'items' => [
                    'Padronize nomes para evitar duplicidade e inconsistência nos filtros do sistema.',
                ],
            ],
            'usuarios-e-permissoes' => [
                'title' => '11. Usuários e Permissões',
                'bg' => 'bg-white',
                'intro' => 'Em Usuários, o Tenant controla acesso, papel e permissões operacionais.',
                'items' => [
                    'Perfis como admin, doctor e user alteram o que cada pessoa pode ver e operar.',
                    'Usuários comuns podem ter acesso limitado aos profissionais vinculados nas permissões.',
                ],
            ],
            'agenda-profissional' => [
                'title' => '12. Agenda do Profissional',
                'bg' => 'bg-gray-50',
                'intro' => 'Esse fluxo concentra agenda, horários e tipos de consulta do profissional.',
                'items' => [
                    'Configure nome da agenda, identificador externo e status.',
                    'Cadastre horários de atendimento e tipos de consulta do profissional.',
                    'Os agendamentos dependem dessa configuração para disponibilidade correta.',
                ],
            ],
            'formularios' => [
                'title' => '13. Formulários',
                'bg' => 'bg-white',
                'intro' => 'Use Formulários para coletar informações estruturadas antes ou durante o atendimento.',
                'items' => [
                    'Crie o formulário, depois organize seções e perguntas no construtor.',
                    'Revise o formulário antes de acompanhar as respostas recebidas.',
                ],
            ],
            'respostas' => [
                'title' => '14. Respostas',
                'bg' => 'bg-gray-50',
                'intro' => 'O módulo Respostas centraliza o que foi preenchido pelos pacientes ou pela equipe.',
                'items' => [
                    'A listagem mostra formulário, paciente, agendamento associado, data de envio e status.',
                    'A visualização detalhada facilita conferência e uso das respostas no contexto do atendimento.',
                ],
            ],
            'integracoes-e-sincronizacao' => [
                'title' => '15. Integrações e Sincronização',
                'bg' => 'bg-white',
                'intro' => 'O fluxo atual de integração com agenda está concentrado na sincronização da agenda do profissional e nas configurações do Tenant.',
                'items' => [
                    'A tela de sincronização mostra profissional, agenda e última sincronização registrada.',
                    'Google Calendar e Apple Calendar podem aparecer conforme disponibilidade do Tenant.',
                ],
            ],
            'relatorios' => [
                'title' => '16. Relatórios',
                'bg' => 'bg-gray-50',
                'intro' => 'O menu Relatórios reúne os relatórios operacionais disponíveis hoje no Tenant.',
                'items' => [
                    'Há relatórios de Agendamentos, Pacientes, Profissionais, Recorrências, Formulários, Portal do Paciente e Notificações.',
                    'Os relatórios atuais contam com exportação em Excel e PDF.',
                ],
            ],
            'configuracoes' => [
                'title' => '17. Configurações',
                'bg' => 'bg-white',
                'intro' => 'Configurações reúne os ajustes gerais do Tenant e impacta campanhas, notificações, integrações e modo de agendamento.',
                'items' => [
                    'Alguns módulos do menu só aparecem quando estiverem habilitados, como Consultas Online, Campanhas e Financeiro.',
                ],
            ],
        ];
    @endphp

    @foreach ($sections as $id => $section)
        <section id="{{ $id }}" class="{{ $section['bg'] }} py-16 lg:py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">{{ $section['title'] }}</h2>
                    <p class="text-lg text-gray-600">{{ $section['intro'] }}</p>
                </div>

                <div class="rounded-lg {{ $section['bg'] === 'bg-white' ? 'bg-gray-50' : 'bg-white' }} p-6 shadow-md">
                    <ul class="ml-4 list-disc list-inside space-y-2 text-gray-700">
                        @foreach ($section['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endforeach

    <section class="bg-blue-600 py-16 text-white">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="mb-4 text-3xl font-bold md:text-4xl">Precisa de Ajuda?</h2>
            <p class="mb-8 text-xl text-blue-100">Se você ainda tiver dúvidas sobre o fluxo do Tenant, fale com o suporte.</p>
            <div class="flex flex-col justify-center gap-4 sm:flex-row">
                <a href="{{ route('landing.contact') }}" class="rounded-lg bg-white px-8 py-4 text-lg font-semibold text-blue-700 transition-colors hover:bg-blue-50">
                    Falar com Suporte
                </a>
                <a href="{{ route('landing.home') }}" class="rounded-lg bg-blue-500 px-8 py-4 text-lg font-semibold text-white transition-colors hover:bg-blue-400">
                    Voltar ao Início
                </a>
            </div>
        </div>
    </section>
@endsection
