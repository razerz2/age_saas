@extends('landing.layout')

@section('title', 'Manual do Sistema - Guia Completo de Uso')
@section('description', 'Manual completo do sistema de agendamentos. Aprenda a usar todas as funcionalidades passo a passo, desde o primeiro acesso até recursos avançados.')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-50 via-white to-blue-50 py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Manual do Sistema
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Guia completo para usar todas as funcionalidades do sistema de agendamentos. 
                    Aprenda passo a passo desde a configuração inicial até recursos avançados.
                </p>
            </div>
        </div>
    </section>

    <!-- Índice -->
    <section class="py-12 bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-blue-50 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">📋 Índice</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="#primeiros-passos" class="text-blue-600 hover:text-blue-700 hover:underline">1. Primeiros Passos</a>
                    <a href="#configuracao-inicial" class="text-blue-600 hover:text-blue-700 hover:underline">2. Configuração Inicial</a>
                    <a href="#estrutura-local" class="text-blue-600 hover:text-blue-700 hover:underline">3. Estrutura do Local</a>
                    <a href="#medicos" class="text-blue-600 hover:text-blue-700 hover:underline">4. Gerenciamento de Profissionais</a>
                    <a href="#pacientes" class="text-blue-600 hover:text-blue-700 hover:underline">5. Gerenciamento de Pacientes</a>
                    <a href="#calendarios" class="text-blue-600 hover:text-blue-700 hover:underline">6. Calendários e Horários</a>
                    <a href="#agendamentos" class="text-blue-600 hover:text-blue-700 hover:underline">7. Agendamentos</a>
                    <a href="#formularios" class="text-blue-600 hover:text-blue-700 hover:underline">8. Formulários Personalizados</a>
                    <a href="#integracao" class="text-blue-600 hover:text-blue-700 hover:underline">9. Integrações</a>
                    <a href="#relatorios" class="text-blue-600 hover:text-blue-700 hover:underline">10. Relatórios</a>
                    <a href="#portal-paciente" class="text-blue-600 hover:text-blue-700 hover:underline">11. Portal do Paciente</a>
                    <a href="#atendimento-medico" class="text-blue-600 hover:text-blue-700 hover:underline">12. Atendimento Médico</a>
                    <a href="#configuracoes" class="text-blue-600 hover:text-blue-700 hover:underline">13. Configurações</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Primeiros Passos -->
    <section id="primeiros-passos" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">1. Primeiros Passos</h2>
                <p class="text-lg text-gray-600">
                    Bem-vindo ao sistema! Siga estes passos para começar a usar o sistema de agendamentos.
                </p>
            </div>

            <div class="space-y-8">
                <!-- Passo 1 -->
                <div class="bg-gray-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">
                            1
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Criação da Conta</h3>
                            <p class="text-gray-700 mb-4">
                                Após realizar o pré-cadastro e pagamento, o sistema cria automaticamente:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                <li>Seu próprio ambiente isolado e seguro</li>
                                <li>Usuário administrador padrão</li>
                                <li>Todas as estruturas e configurações necessárias</li>
                            </ul>
                            <p class="text-gray-700 mt-4">
                                Você receberá por email as credenciais de acesso com o usuário admin já criado.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Passo 2 -->
                <div class="bg-gray-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">
                            2
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Primeiro Acesso</h3>
                            <p class="text-gray-700 mb-4">
                                Acesse o sistema através da URL:
                            </p>
                            <div class="bg-gray-800 text-green-400 p-4 rounded-lg font-mono text-sm mb-4">
                                http://seu-dominio.com/customer/{seu-subdomain}/login
                            </div>
                            <p class="text-gray-700 mb-4">
                                Use as credenciais enviadas por email:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                <li><strong>Email:</strong> admin@seu-subdomain.com</li>
                                <li><strong>Senha:</strong> (enviada por email)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Passo 3 -->
                <div class="bg-gray-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">
                            3
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">Dashboard</h3>
                            <p class="text-gray-700 mb-4">
                                Após o login, você será direcionado para o Dashboard, onde encontrará:
                            </p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                                <li>Estatísticas em tempo real sobre agendamentos</li>
                                <li>Informações sobre pacientes e médicos</li>
                                <li>Métricas de receita</li>
                                <li>Acesso rápido às principais funcionalidades</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Configuração Inicial -->
    <section id="configuracao-inicial" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">2. Configuração Inicial</h2>
                <p class="text-lg text-gray-600">
                    Configure o sistema antes de começar a usar. Essas configurações são essenciais para o funcionamento adequado.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">2.1. Configurações Gerais</h3>
                    <p class="text-gray-700 mb-4">
                        Acesse <strong>Configurações → Geral</strong> para definir:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>Nome da clínica</li>
                        <li>Email de contato</li>
                        <li>Telefone</li>
                        <li>Endereço completo</li>
                        <li>Personalização de labels (médico, dentista, psicólogo, etc.)</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">2.2. Configurações de Agendamentos</h3>
                    <p class="text-gray-700 mb-4">
                        Em <strong>Configurações → Agendamentos</strong>, configure:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Modo padrão:</strong> Presencial, Online ou Escolha do usuário</li>
                        <li>Antecedência mínima para agendamento</li>
                        <li>Horários permitidos para agendamento</li>
                        <li>Regras de cancelamento</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">2.3. Configurações de Notificações</h3>
                    <p class="text-gray-700 mb-4">
                        Configure em <strong>Configurações → Notificações</strong>:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>Envio automático de emails</li>
                        <li>Envio automático de WhatsApp</li>
                        <li>Lembretes de agendamento</li>
                        <li>Templates de mensagens</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Estrutura do Local -->
    <section id="estrutura-local" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">3. Estrutura do Local</h2>
                <p class="text-lg text-gray-600">
                    Defina a estrutura organizacional da sua clínica cadastrando usuários e definindo quantos profissionais da saúde e usuários comuns serão necessários.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">3.1. Planejamento da Estrutura</h3>
                    <p class="text-gray-700 mb-4">
                        Antes de começar a cadastrar, defina quantos usuários você precisa:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Profissionais da Saúde:</strong> Médicos, dentistas, psicólogos, fisioterapeutas, etc. que atendem pacientes</li>
                        <li><strong>Usuários Comuns:</strong> Recepcionistas, secretários, assistentes administrativos que ajudam no gerenciamento</li>
                        <li><strong>Administradores:</strong> Usuários com acesso completo ao sistema (geralmente você já tem um admin criado)</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">3.2. Cadastrar Usuários</h3>
                    <p class="text-gray-700 mb-4">
                        Para cadastrar usuários no sistema:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Usuários → Criar Usuário</strong></li>
                        <li>Preencha os dados do usuário:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Nome completo</li>
                                <li>Email (será usado para login)</li>
                                <li>Senha (ou deixe o sistema gerar automaticamente)</li>
                            </ul>
                        </li>
                        <li>Defina o <strong>Papel (Role)</strong> do usuário:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><strong>Admin:</strong> Acesso completo a todos os dados e funcionalidades</li>
                                <li><strong>Profissional da Saúde:</strong> Acesso apenas aos próprios dados (agendamentos, pacientes, etc.)</li>
                                <li><strong>Usuário Comum:</strong> Acesso restrito aos médicos aos quais tem permissão</li>
                            </ul>
                        </li>
                        <li>Configure os <strong>Módulos de Acesso</strong> permitidos para este usuário:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Atendimentos</li>
                                <li>Consultas Online</li>
                                <li>Atendimento Médico</li>
                                <li>Pacientes</li>
                                <li>Profissionais</li>
                                <li>Agenda</li>
                                <li>Formulários</li>
                                <li>Relatórios</li>
                                <li>E outros módulos conforme necessário</li>
                            </ul>
                        </li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                </div>

                <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <h4 class="font-semibold text-gray-900 mb-2">💡 Importante</h4>
                    <p class="text-gray-700 mb-2">
                        <strong>Diferença entre Roles:</strong>
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li><strong>Admin:</strong> Vê todos os dados sem restrições</li>
                        <li><strong>Profissional da Saúde:</strong> Só vê seus próprios dados (agendamentos, pacientes, formulários vinculados a ele)</li>
                        <li><strong>Usuário Comum:</strong> Só vê dados dos profissionais aos quais tem permissão explícita</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">3.3. Permissões de Acesso</h3>
                    <p class="text-gray-700 mb-4">
                        Para usuários com role "Usuário Comum", você pode definir quais profissionais da saúde eles podem acessar:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Usuários</strong></li>
                        <li>Clique no usuário desejado</li>
                        <li>Vá em <strong>Permissões de Médicos</strong></li>
                        <li>Selecione os profissionais que este usuário pode visualizar e gerenciar</li>
                        <li>Salve as permissões</li>
                    </ol>
                    <p class="text-gray-700 mt-4">
                        <strong>Nota:</strong> Quando um usuário comum cadastra um profissional da saúde, ele automaticamente recebe permissão para acessar esse profissional.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">3.4. Próximos Passos</h3>
                    <p class="text-gray-700 mb-4">
                        Após cadastrar os usuários, você está pronto para:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>Cadastrar os profissionais da saúde (médicos, dentistas, etc.) vinculando aos usuários criados</li>
                        <li>Configurar especialidades médicas</li>
                        <li>Definir calendários e horários de atendimento</li>
                        <li>Começar a cadastrar pacientes e criar agendamentos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Gerenciamento de Médicos -->
    <section id="medicos" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">4. Gerenciamento de Profissionais da Saúde</h2>
                <p class="text-lg text-gray-600">
                    Cadastre e gerencie os profissionais de saúde que atendem na sua clínica.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">4.1. Cadastrar Profissional da Saúde</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Médicos → Criar Médico</strong></li>
                        <li>Selecione um usuário existente (ou crie um novo usuário primeiro)</li>
                        <li>Preencha o número de registro profissional (CRM, CRP, CRO, etc.)</li>
                        <li>Selecione o estado do registro</li>
                        <li>Escolha uma ou mais especialidades médicas</li>
                        <li>(Opcional) Faça upload da assinatura digital</li>
                        <li>(Opcional) Personalize os labels (singular e plural)</li>
                        <li>(Opcional) Configure campos de registro personalizados</li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                </div>

                <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <h4 class="font-semibold text-gray-900 mb-2">💡 Dica Importante</h4>
                    <p class="text-gray-700">
                        Quando um usuário comum (role "user") cadastra um médico, ele automaticamente recebe permissão para visualizar e gerenciar esse médico. Isso facilita o workflow onde um usuário cria o médico e já pode trabalhar com ele.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">4.2. Especialidades Médicas</h3>
                    <p class="text-gray-700 mb-4">
                        Antes de cadastrar profissionais, certifique-se de ter as especialidades cadastradas:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Especialidades → Criar Especialidade</strong></li>
                        <li>Informe o nome da especialidade</li>
                        <li>Adicione uma descrição (opcional)</li>
                        <li>Salve a especialidade</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Gerenciamento de Pacientes -->
    <section id="pacientes" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">5. Gerenciamento de Pacientes</h2>
                <p class="text-lg text-gray-600">
                    Cadastre e gerencie os pacientes da sua clínica.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">5.1. Cadastrar Paciente</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Pacientes → Criar Paciente</strong></li>
                        <li>Preencha os dados pessoais:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Nome completo</li>
                                <li>CPF</li>
                                <li>Data de nascimento</li>
                                <li>Email</li>
                                <li>Telefone</li>
                                <li>Endereço (opcional)</li>
                            </ul>
                        </li>
                        <li>Se desejar que o paciente acesse o portal, marque <strong>Habilitar login no portal</strong></li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">5.2. Portal do Paciente</h3>
                    <p class="text-gray-700 mb-4">
                        Quando você habilita o login para um paciente:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>O sistema gera credenciais automaticamente</li>
                        <li>As credenciais são enviadas por email</li>
                        <li>O paciente pode acessar o portal em: <code class="bg-gray-100 px-2 py-1 rounded">/customer/{seu-subdominio}/paciente/login</code></li>
                        <li>No portal, o paciente pode:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Ver seus agendamentos</li>
                                <li>Criar novos agendamentos</li>
                                <li>Visualizar histórico</li>
                                <li>Receber notificações</li>
                                <li>Atualizar perfil</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Calendários e Horários -->
    <section id="calendarios" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">6. Calendários e Horários</h2>
                <p class="text-lg text-gray-600">
                    Configure os calendários de agendamento e horários comerciais para cada médico.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">6.1. Criar Calendário</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Calendários → Criar Calendário</strong></li>
                        <li>Selecione o médico associado</li>
                        <li>Defina um nome para o calendário</li>
                        <li>Configure os horários comerciais (veja próximo passo)</li>
                        <li>Salve o calendário</li>
                    </ol>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">6.2. Horários Comerciais</h3>
                    <p class="text-gray-700 mb-4">
                        Configure os horários de atendimento para cada médico:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Horários Comerciais → Criar Horário</strong></li>
                        <li>Selecione o médico</li>
                        <li>Escolha o dia da semana</li>
                        <li>Defina o horário de início e fim</li>
                        <li>Configure intervalos entre consultas (opcional)</li>
                        <li>Repita para todos os dias da semana</li>
                    </ol>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">6.3. Tipos de Consulta</h3>
                    <p class="text-gray-700 mb-4">
                        Defina os tipos de consulta disponíveis:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Tipos de Consulta → Criar Tipo</strong></li>
                        <li>Informe o nome do tipo (ex: "Consulta Normal", "Retorno", "Avaliação")</li>
                        <li>Defina a duração em minutos</li>
                        <li>Associe ao médico</li>
                        <li>Adicione uma descrição (opcional)</li>
                        <li>Salve o tipo de consulta</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Agendamentos -->
    <section id="agendamentos" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">7. Agendamentos</h2>
                <p class="text-lg text-gray-600">
                    Crie e gerencie agendamentos presenciais e online.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">7.1. Criar Agendamento</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Agendamentos → Criar Agendamento</strong></li>
                        <li>Selecione o paciente</li>
                        <li>Escolha o médico</li>
                        <li>Selecione o calendário</li>
                        <li>Escolha o tipo de consulta</li>
                        <li>Selecione o modo de atendimento:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><strong>Presencial:</strong> Consulta física na clínica</li>
                                <li><strong>Online:</strong> Consulta virtual via videoconferência</li>
                            </ul>
                        </li>
                        <li>Escolha data e horário</li>
                        <li>Adicione observações (opcional)</li>
                        <li>Clique em <strong>Salvar</strong></li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">7.2. Agendamentos Online</h3>
                    <p class="text-gray-700 mb-4">
                        Para agendamentos online, após criar o agendamento:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Agendamentos Online</strong></li>
                        <li>Encontre o agendamento criado</li>
                        <li>Clique em <strong>Configurar</strong></li>
                        <li>Adicione o link de videoconferência (Zoom, Google Meet, etc.)</li>
                        <li>Escreva instruções personalizadas para o paciente</li>
                        <li>Salve as configurações</li>
                        <li>Envie as instruções por email ou WhatsApp</li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">7.3. Agendamentos Recorrentes</h3>
                    <p class="text-gray-700 mb-4">
                        Crie agendamentos que se repetem automaticamente:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Agendamentos Recorrentes → Criar</strong></li>
                        <li>Preencha os dados do agendamento</li>
                        <li>Configure a regra de recorrência:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li><strong>Diária:</strong> Todos os dias</li>
                                <li><strong>Semanal:</strong> Dias específicos da semana</li>
                                <li><strong>Mensal:</strong> Dia específico do mês</li>
                            </ul>
                        </li>
                        <li>Defina a data final ou quantidade de ocorrências</li>
                        <li>Salve o agendamento recorrente</li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">7.4. Visualizar Calendário</h3>
                    <p class="text-gray-700 mb-4">
                        Visualize todos os agendamentos em formato de calendário:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li>Acesse <strong>Agendamentos</strong></li>
                        <li>Use os filtros para visualizar por médico, data, modo de atendimento</li>
                        <li>Clique em um agendamento para ver detalhes</li>
                        <li>Edite ou cancele agendamentos diretamente do calendário</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Formulários Personalizados -->
    <section id="formularios" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">8. Formulários Personalizados</h2>
                <p class="text-lg text-gray-600">
                    Crie formulários que são enviados automaticamente aos pacientes após o agendamento.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">8.1. Criar Formulário</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Formulários → Criar Formulário</strong></li>
                        <li>Informe o nome do formulário</li>
                        <li>Adicione uma descrição (opcional)</li>
                        <li>Vincule a médicos ou especialidades específicas (opcional)</li>
                        <li>Salve o formulário</li>
                        <li>Clique em <strong>Construir Formulário</strong> para adicionar seções e perguntas</li>
                    </ol>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">8.2. Construir Formulário</h3>
                    <p class="text-gray-700 mb-4">
                        No construtor de formulários, você pode:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Adicionar Seções:</strong> Organize o formulário em seções</li>
                        <li><strong>Criar Perguntas:</strong> Adicione perguntas de diferentes tipos:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Texto curto</li>
                                <li>Texto longo</li>
                                <li>Número</li>
                                <li>Data</li>
                                <li>Escolha única (radio)</li>
                                <li>Múltipla escolha (checkbox)</li>
                                <li>Lista suspensa (select)</li>
                            </ul>
                        </li>
                        <li><strong>Adicionar Opções:</strong> Para perguntas de escolha, adicione as opções disponíveis</li>
                        <li><strong>Reordenar:</strong> Arraste e solte para reorganizar seções e perguntas</li>
                    </ul>
                </div>

                <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-600">
                    <h4 class="font-semibold text-gray-900 mb-2">💡 Envio Automático</h4>
                    <p class="text-gray-700">
                        Quando um agendamento é criado, o sistema verifica se há formulários vinculados ao médico ou especialidade. Se houver, o formulário é enviado automaticamente por email ou WhatsApp ao paciente.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">8.3. Visualizar Respostas</h3>
                    <p class="text-gray-700 mb-4">
                        Para ver as respostas dos pacientes:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Respostas de Formulários</strong></li>
                        <li>Filtre por formulário, paciente ou médico</li>
                        <li>Clique em uma resposta para ver os detalhes</li>
                        <li>As respostas também aparecem no módulo de Atendimento Médico</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrações -->
    <section id="integracao" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">9. Integrações</h2>
                <p class="text-lg text-gray-600">
                    Conecte o sistema com outras ferramentas que você já usa.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">9.1. Google Calendar</h3>
                    <p class="text-gray-700 mb-4">
                        Sincronize agendamentos automaticamente com o Google Calendar:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Integrações → Google Calendar</strong></li>
                        <li>Encontre o médico na lista</li>
                        <li>Clique em <strong>Conectar Conta Google</strong></li>
                        <li>Autorize o acesso ao Google Calendar</li>
                        <li>Após conectar, os agendamentos serão sincronizados automaticamente:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Criação de agendamento → Cria evento no Google Calendar</li>
                                <li>Edição de agendamento → Atualiza evento no Google Calendar</li>
                                <li>Cancelamento → Remove evento do Google Calendar</li>
                            </ul>
                        </li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">9.2. Apple Calendar (CalDAV)</h3>
                    <p class="text-gray-700 mb-4">
                        Integre com iCloud usando o protocolo CalDAV:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Integrações → Apple Calendar</strong></li>
                        <li>Configure as credenciais do CalDAV</li>
                        <li>Teste a conexão</li>
                        <li>Os agendamentos serão sincronizados automaticamente</li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">9.3. WhatsApp</h3>
                    <p class="text-gray-700 mb-4">
                        Configure o envio automático de mensagens via WhatsApp:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Configurações → Integrações</strong></li>
                        <li>Configure as credenciais da API do WhatsApp</li>
                        <li>Ative o envio automático nas configurações de notificações</li>
                        <li>Os pacientes receberão lembretes e notificações via WhatsApp</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Relatórios -->
    <section id="relatorios" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">10. Relatórios</h2>
                <p class="text-lg text-gray-600">
                    Gere relatórios completos e exporte dados para análises externas.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">10.1. Relatório de Agendamentos</h3>
                    <p class="text-gray-700 mb-3">
                        Acesse <strong>Relatórios → Agendamentos</strong> para gerar relatórios com:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Filtros por período, médico, status</li>
                        <li>Estatísticas de agendamentos</li>
                        <li>Exportação em Excel, PDF ou CSV</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">10.2. Relatório de Pacientes</h3>
                    <p class="text-gray-700 mb-3">
                        Em <strong>Relatórios → Pacientes</strong>, visualize:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Listagem completa de pacientes</li>
                        <li>Histórico de atendimentos</li>
                        <li>Estatísticas por paciente</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">10.3. Relatório de Formulários</h3>
                    <p class="text-gray-700 mb-3">
                        Analise respostas em <strong>Relatórios → Formulários</strong>:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Taxa de resposta</li>
                        <li>Análise de respostas</li>
                        <li>Estatísticas por formulário</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">10.4. Outros Relatórios</h3>
                    <p class="text-gray-700 mb-3">
                        Também disponíveis:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Relatório de Médicos</li>
                        <li>Relatório de Recorrências</li>
                        <li>Relatório do Portal do Paciente</li>
                        <li>Relatório de Notificações</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal do Paciente -->
    <section id="portal-paciente" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">11. Portal do Paciente</h2>
                <p class="text-lg text-gray-600">
                    Permita que seus pacientes acessem o sistema e gerenciem seus próprios agendamentos.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">11.1. Habilitar Acesso</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>No cadastro do paciente, marque <strong>Habilitar login no portal</strong></li>
                        <li>Salve o paciente</li>
                        <li>O sistema gera credenciais automaticamente</li>
                        <li>As credenciais são enviadas por email ao paciente</li>
                    </ol>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">11.2. Funcionalidades do Portal</h3>
                    <p class="text-gray-700 mb-4">
                        No portal, o paciente pode:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Dashboard:</strong> Ver visão geral de agendamentos e informações importantes</li>
                        <li><strong>Meus Agendamentos:</strong> Visualizar, criar, editar e cancelar agendamentos</li>
                        <li><strong>Notificações:</strong> Receber alertas sobre agendamentos e lembretes</li>
                        <li><strong>Perfil:</strong> Atualizar dados pessoais e recuperar senha</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">11.3. Área Pública de Agendamento</h3>
                    <p class="text-gray-700 mb-4">
                        Pacientes também podem agendar sem precisar fazer login:
                    </p>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <code class="bg-gray-100 px-2 py-1 rounded">/customer/{seu-subdominio}/agendamento/identificar</code></li>
                        <li>Informe CPF ou email</li>
                        <li>Se já for paciente, escolha o agendamento</li>
                        <li>Se não for paciente, faça o cadastro rápido</li>
                        <li>Complete o agendamento</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Atendimento Médico -->
    <section id="atendimento-medico" class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">12. Atendimento Médico</h2>
                <p class="text-lg text-gray-600">
                    Módulo completo para gerenciar a sessão diária de atendimentos.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">12.1. Iniciar Sessão de Atendimento</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Acesse <strong>Atendimento Médico</strong></li>
                        <li>Escolha o dia que deseja atender</li>
                        <li>Clique em <strong>Iniciar Atendimento</strong></li>
                        <li>O sistema lista todos os agendamentos do dia filtrados conforme suas permissões</li>
                    </ol>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">12.2. Gerenciar Atendimentos</h3>
                    <p class="text-gray-700 mb-4">
                        Durante a sessão de atendimento, você pode:
                    </p>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                        <li><strong>Atualizar Status:</strong> Alterar o status do atendimento em tempo real:
                            <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                <li>Agendado</li>
                                <li>Chegou</li>
                                <li>Em Atendimento</li>
                                <li>Concluído</li>
                            </ul>
                        </li>
                        <li><strong>Visualizar Formulários:</strong> Ver respostas de formulários respondidos pelo paciente</li>
                        <li><strong>Navegação Automática:</strong> Após concluir um atendimento, o sistema navega automaticamente para o próximo</li>
                        <li><strong>Detalhes:</strong> Ver informações completas do agendamento e paciente</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Configurações -->
    <section id="configuracoes" class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">13. Configurações</h2>
                <p class="text-lg text-gray-600">
                    Personalize o sistema conforme as necessidades da sua clínica.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">13.1. Configurações Gerais</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Dados da clínica</li>
                        <li>Personalização de labels</li>
                        <li>Campos de registro profissional</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">13.2. Configurações de Agendamentos</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Modo padrão (presencial/online)</li>
                        <li>Antecedência mínima</li>
                        <li>Horários permitidos</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">13.3. Configurações de Notificações</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Envio automático de emails</li>
                        <li>Envio automático de WhatsApp</li>
                        <li>Templates de mensagens</li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">13.4. Configurações de Integrações</h3>
                    <ul class="list-disc list-inside space-y-1 text-gray-700 ml-4">
                        <li>Google Calendar</li>
                        <li>Apple Calendar</li>
                        <li>WhatsApp API</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Dicas e Boas Práticas -->
    <section class="py-16 lg:py-24 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">💡 Dicas e Boas Práticas</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-500 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Organização</h3>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Cadastre todos os médicos antes de criar agendamentos</li>
                        <li>Configure horários comerciais para cada médico</li>
                        <li>Defina tipos de consulta claros e consistentes</li>
                    </ul>
                </div>

                <div class="bg-blue-500 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Produtividade</h3>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Use agendamentos recorrentes para consultas regulares</li>
                        <li>Configure formulários para coletar informações automaticamente</li>
                        <li>Integre com Google Calendar para sincronização automática</li>
                    </ul>
                </div>

                <div class="bg-blue-500 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Comunicação</h3>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Ative notificações automáticas por email e WhatsApp</li>
                        <li>Habilite o portal do paciente para maior autonomia</li>
                        <li>Use a área pública de agendamento para novos pacientes</li>
                    </ul>
                </div>

                <div class="bg-blue-500 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Análise</h3>
                    <ul class="list-disc list-inside space-y-2 ml-4">
                        <li>Gere relatórios regularmente para análise</li>
                        <li>Exporte dados para análises externas quando necessário</li>
                        <li>Monitore estatísticas no dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Suporte -->
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Precisa de Ajuda?</h2>
            <p class="text-xl text-gray-600 mb-8">
                Se você tiver dúvidas ou precisar de suporte, entre em contato conosco.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('landing.contact') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors">
                    Falar com Suporte
                </a>
                <a href="{{ route('landing.home') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-4 rounded-lg font-semibold text-lg transition-colors">
                    Voltar ao Início
                </a>
            </div>
        </div>
    </section>
@endsection

