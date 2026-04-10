@extends('landing.layout')

@section('title', 'Manual do Sistema')
@section('description', 'Manual operacional do Tenant com instruções passo a passo para configurar e usar os módulos atuais do sistema.')

@push('meta')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <section class="relative bg-gradient-to-br from-blue-50 via-white to-blue-50 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="mb-6 text-4xl font-bold text-gray-900 md:text-5xl lg:text-6xl">Manual do Sistema</h1>
            <p class="mx-auto max-w-3xl text-xl text-gray-600">
                Guia operacional do Tenant com a ordem recomendada de uso, botões, campos e etapas para configurar
                o sistema e executar a rotina do dia.
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
                    <a href="#templates-campanhas" class="text-blue-600 hover:text-blue-700 hover:underline">8. Templates de Campanha</a>
                    <a href="#pacientes" class="text-blue-600 hover:text-blue-700 hover:underline">9. Pacientes</a>
                    <a href="#especialidades" class="text-blue-600 hover:text-blue-700 hover:underline">10. Profissionais e Especialidades</a>
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
                'intro' => 'Use esta ordem para começar: primeiro ajuste as Configurações, depois cadastre usuários, profissionais e agenda, e só então abra a operação diária.',
                'steps' => [
                    [
                        'title' => 'Revise as Configurações iniciais do Tenant',
                        'items' => [
                            'No menu lateral, acesse Configurações.',
                            'Abra as abas Clínica, Geral, Agendamentos, Calendário, Profissionais, Usuários, Notificações, Campanhas, Integrações e Link Público conforme os módulos liberados para o seu perfil.',
                            'Preencha os dados básicos, revise os comportamentos padrão e clique em Salvar Alterações sempre que editar uma aba.',
                        ],
                    ],
                    [
                        'title' => 'Monte a estrutura administrativa antes de atender',
                        'items' => [
                            'Acesse Usuários e clique em Novo Usuário para criar administradores, recepção e demais pessoas da equipe.',
                            'Depois cadastre Especialidades para padronizar filtros e vínculos clínicos.',
                            'Em seguida, cadastre os Profissionais vinculando cada pessoa ao usuário correspondente quando a tela solicitar.',
                        ],
                    ],
                    [
                        'title' => 'Prepare a agenda do profissional',
                        'items' => [
                            'Acesse Agenda do Profissional e clique em Nova Agenda do Profissional.',
                            'Preencha o profissional, o nome da agenda, o status da agenda e os horários de atendimento.',
                            'Adicione os tipos vinculados e salve a agenda antes de tentar criar agendamentos.',
                        ],
                    ],
                    [
                        'title' => 'Abra a rotina do dia',
                        'items' => [
                            'Cadastre os pacientes com telefone e e-mail atualizados.',
                            'Crie Agendamentos avulsos ou Agendamentos Recorrentes conforme a necessidade.',
                            'Use Atendimento, Consultas Online, Formulários, Respostas, Campanhas e Relatórios para operar o dia.',
                        ],
                    ],
                ],
                'notes' => [
                    'Se um item de menu não aparecer, revise o papel do usuário, os módulos liberados para esse usuário e se o recurso está habilitado no próprio Tenant.',
                    'A sequência mais segura é: Configurações, Usuários, Profissionais e Especialidades, Agenda do Profissional, Pacientes, Agendamentos.',
                ],
            ],
            'dashboard' => [
                'title' => '2. Dashboard',
                'bg' => 'bg-gray-50',
                'intro' => 'O Dashboard é a entrada do sistema depois do login e serve para orientar a operação do dia e o acesso rápido aos módulos.',
                'steps' => [
                    [
                        'title' => 'Entre no sistema e confira a visão geral',
                        'items' => [
                            'Faça login no Tenant para abrir o Dashboard.',
                            'Revise os cards, indicadores e atalhos visíveis para o seu perfil.',
                            'Use o menu lateral para navegar até Atendimento, Agendamentos, Pacientes, Relatórios e demais áreas liberadas.',
                        ],
                    ],
                    [
                        'title' => 'Valide a visibilidade dos módulos',
                        'items' => [
                            'Se Campanhas, Consultas Online, Financeiro ou outro módulo não aparecer, confirme se ele está habilitado nas Configurações.',
                            'Se o módulo estiver habilitado, revise em Usuários se o perfil atual recebeu acesso a esse módulo.',
                        ],
                    ],
                    [
                        'title' => 'Use o Dashboard como ponto de partida da rotina',
                        'items' => [
                            'Abra Atendimento quando quiser iniciar a sessão do dia.',
                            'Abra Agendamentos para criar ou revisar consultas.',
                            'Abra Relatórios quando precisar analisar dados do período.',
                        ],
                    ],
                ],
                'notes' => [
                    'O Dashboard não substitui os cadastros. Ele ajuda a localizar o que precisa ser feito primeiro.',
                ],
            ],
            'atendimento' => [
                'title' => '3. Atendimento',
                'bg' => 'bg-white',
                'intro' => 'Use Atendimento para abrir a sessão do dia, acompanhar a fila e atualizar o andamento de cada consulta.',
                'steps' => [
                    [
                        'title' => 'Abra a sessão do dia',
                        'items' => [
                            'No menu lateral, clique em Atendimento.',
                            'Escolha a data desejada e abra a sessão do dia.',
                            'O sistema listará os agendamentos do período respeitando as permissões do usuário logado.',
                        ],
                    ],
                    [
                        'title' => 'Acompanhe a fila de atendimentos',
                        'items' => [
                            'Abra os detalhes do agendamento quando precisar consultar informações do paciente e da consulta.',
                            'Use as ações da tela para reorganizar a fila conforme a rotina da clínica.',
                            'Abra a resposta de formulário vinculada quando precisar consultar dados preenchidos antes do atendimento.',
                        ],
                    ],
                    [
                        'title' => 'Atualize o status ao longo da consulta',
                        'items' => [
                            'Use a ação de status para marcar o andamento do paciente.',
                            'Atualize para estados como Agendado, Chegou, Em Atendimento e Concluído conforme o fluxo da equipe.',
                            'Depois de concluir, volte para a lista do dia e siga para o próximo atendimento.',
                        ],
                    ],
                ],
                'notes' => [
                    'Se a sessão aparecer vazia, confirme se a data está correta e se existem agendamentos criados para o profissional e agenda selecionados.',
                ],
            ],
            'agendamentos' => [
                'title' => '4. Agendamentos',
                'bg' => 'bg-gray-50',
                'intro' => 'Use Agendamentos para registrar consultas avulsas, presenciais ou online, sempre com base na disponibilidade da agenda do profissional.',
                'steps' => [
                    [
                        'title' => 'Abra a tela de criação',
                        'items' => [
                            'No menu lateral, clique em Agendamentos.',
                            'Clique em Novo Agendamento.',
                        ],
                    ],
                    [
                        'title' => 'Preencha os dados principais',
                        'items' => [
                            'Selecione ou busque o paciente.',
                            'Selecione o profissional.',
                            'Escolha a especialidade, a agenda e o tipo de consulta conforme os campos exibidos.',
                            'Defina o modo de atendimento quando o Tenant permitir escolher entre presencial e online.',
                        ],
                    ],
                    [
                        'title' => 'Escolha data e horário disponíveis',
                        'items' => [
                            'Informe a data da consulta.',
                            'Escolha um horário disponível com base na agenda do profissional.',
                            'Revise observações, status e demais campos exibidos na tela.',
                        ],
                    ],
                    [
                        'title' => 'Salve e acompanhe o agendamento',
                        'items' => [
                            'Clique em Salvar para registrar o agendamento.',
                            'Abra a tela de detalhes para revisar paciente, profissional, horário e histórico.',
                            'Quando for consulta online, siga depois para Consultas Online para configurar as instruções.',
                        ],
                    ],
                ],
                'notes' => [
                    'Se não houver horários disponíveis, revise a Agenda do Profissional, os horários cadastrados e os tipos vinculados.',
                ],
            ],
            'agendamentos-recorrentes' => [
                'title' => '5. Agendamentos Recorrentes',
                'bg' => 'bg-white',
                'intro' => 'Use este módulo para criar séries de consultas repetidas, com regras fixas de frequência e encerramento.',
                'steps' => [
                    [
                        'title' => 'Abra a criação da recorrência',
                        'items' => [
                            'No menu lateral, clique em Agendamentos Recorrentes.',
                            'Clique em Criar.',
                        ],
                    ],
                    [
                        'title' => 'Selecione paciente e profissional',
                        'items' => [
                            'Na seção Informações Básicas, clique em Buscar para localizar o paciente.',
                            'Clique em Buscar novamente para localizar o profissional.',
                            'Depois da seleção, o sistema carregará especialidades e regras de agenda compatíveis.',
                        ],
                    ],
                    [
                        'title' => 'Preencha os dados principais da série',
                        'items' => [
                            'Escolha a especialidade quando o campo estiver disponível.',
                            'Defina a Data Inicial.',
                            'No campo Tipo de Término, escolha se a recorrência ficará Sem limite ou se terá Data final.',
                            'Se escolher Data final, preencha o campo exibido antes de continuar.',
                        ],
                    ],
                    [
                        'title' => 'Adicione as regras de recorrência',
                        'items' => [
                            'Na seção Regras de Recorrência, escolha o Dia da Semana.',
                            'Selecione o Horário Disponível correspondente.',
                            'Clique em Adicionar Regra para incluir outros dias e horários na mesma série.',
                        ],
                    ],
                    [
                        'title' => 'Salve a recorrência',
                        'items' => [
                            'Revise os dados preenchidos.',
                            'Clique em Criar Agendamento Recorrente.',
                        ],
                    ],
                    [
                        'title' => 'Acompanhe a série criada',
                        'items' => [
                            'Abra a tela de detalhes da recorrência para revisar regras, status e sessões geradas.',
                            'Use Editar quando precisar ajustar a recorrência.',
                            'Use Cancelar quando precisar encerrar a série.',
                        ],
                    ],
                ],
                'notes' => [
                    'A recorrência depende da agenda do profissional. Sem agenda ativa e horários disponíveis, a criação não conseguirá montar os horários.',
                ],
            ],
            'consultas-online' => [
                'title' => '6. Consultas Online',
                'bg' => 'bg-gray-50',
                'intro' => 'Use Consultas Online para cadastrar o link da reunião, escrever as instruções e enviar essas orientações ao paciente.',
                'steps' => [
                    [
                        'title' => 'Abra a consulta online',
                        'items' => [
                            'No menu lateral, clique em Consultas Online.',
                            'Abra o agendamento online desejado para entrar na tela Instruções de Consulta Online.',
                        ],
                    ],
                    [
                        'title' => 'Configure as instruções da consulta',
                        'items' => [
                            'Preencha Link da Reunião.',
                            'Preencha Aplicativo com o nome da plataforma usada.',
                            'Escreva as Instruções Gerais e as Observações para o Paciente.',
                            'Clique em Salvar Instruções.',
                        ],
                    ],
                    [
                        'title' => 'Envie as instruções ao paciente',
                        'items' => [
                            'Na área Enviar Instruções ao Paciente, clique em Enviar por Email ou Enviar por WhatsApp conforme o canal disponível.',
                            'Se o botão estiver desabilitado, verifique se o paciente tem e-mail ou telefone cadastrados.',
                            'Se nenhum canal estiver disponível, revise Configurações e as notificações do Tenant.',
                        ],
                    ],
                    [
                        'title' => 'Acompanhe respostas vinculadas',
                        'items' => [
                            'Depois do envio, abra Respostas para verificar formulários recebidos antes da consulta.',
                            'Você também pode consultar as respostas a partir da rotina de Atendimento quando a consulta chegar à sessão do dia.',
                        ],
                    ],
                ],
                'notes' => [
                    'Consultas Online aparecem de forma consistente quando o agendamento foi criado nesse modo e o Tenant mantém o fluxo habilitado.',
                ],
            ],
            'campanhas' => [
                'title' => '7. Campanhas',
                'bg' => 'bg-white',
                'intro' => 'Use Campanhas para montar comunicações por email e WhatsApp, testar o envio e iniciar ou agendar disparos.',
                'steps' => [
                    [
                        'title' => 'Prepare os canais antes de criar',
                        'items' => [
                            'Acesse Configurações e abra a aba Campanhas para revisar os canais disponíveis.',
                            'Se o Tenant reutilizar os canais de Notificações, valide também a aba Notificações.',
                            'Se a tela de Campanhas exibir aviso de canal indisponível, resolva essa configuração antes de seguir.',
                        ],
                    ],
                    [
                        'title' => 'Crie a campanha',
                        'items' => [
                            'No menu lateral, clique em Campanhas.',
                            'Clique em Nova Campanha.',
                            'Preencha o Nome da campanha.',
                            'Escolha o Tipo entre Manual e Automatizada.',
                            'Marque os Canais disponíveis, como Email e WhatsApp.',
                        ],
                    ],
                    [
                        'title' => 'Monte o conteúdo e o público',
                        'items' => [
                            'Na área Conteúdo da Campanha, preencha os campos de email quando esse canal estiver marcado.',
                            'Na área de WhatsApp, escolha o modo permitido pelo provedor e selecione o template quando a campanha exigir modelo aprovado.',
                            'Revise as variáveis disponíveis e use apenas as que fazem sentido para o conteúdo.',
                            'Na parte de público e regras, ajuste os filtros e condições opcionais antes do disparo.',
                        ],
                    ],
                    [
                        'title' => 'Envie teste e publique',
                        'items' => [
                            'Depois de salvar a campanha, abra a tela de detalhes.',
                            'Na área Enviar teste, escolha o canal, informe o destino manualmente ou busque um paciente.',
                            'Clique em Enviar teste para validar o conteúdo.',
                            'Use Iniciar agora para disparo imediato ou Agendar para definir o envio posterior.',
                        ],
                    ],
                    [
                        'title' => 'Acompanhe a execução',
                        'items' => [
                            'Na tela da campanha, use Pausar campanha e Retomar campanha quando precisar controlar a execução.',
                            'Abra Execuções para acompanhar os disparos realizados.',
                            'Abra Destinatários para revisar quem entrou na campanha e o resultado do envio.',
                        ],
                    ],
                ],
                'notes' => [
                    'Campanhas automatizadas usam a programação cadastrada na própria campanha. Campanhas manuais dependem de Iniciar agora ou Agendar envio.',
                ],
            ],
            'templates-campanhas' => [
                'title' => '8. Templates de Campanha',
                'bg' => 'bg-gray-50',
                'intro' => 'Use os templates para padronizar mensagens de campanha. O comportamento muda conforme o modo de WhatsApp configurado no Tenant.',
                'steps' => [
                    [
                        'title' => 'Verifique o modo atual do Tenant',
                        'items' => [
                            'Se o Tenant estiver em modo oficial, a gestão de templates fica no catálogo oficial sincronizado com a Meta.',
                            'Se o Tenant estiver em modo não oficial, a tela local de Templates de Campanha fica liberada para criação e edição.',
                        ],
                    ],
                    [
                        'title' => 'Crie um template local quando o modo não oficial estiver ativo',
                        'items' => [
                            'No menu Campanhas, abra Templates.',
                            'Clique em Novo Template de Campanha.',
                            'Preencha Nome.',
                            'Preencha Conteúdo com a mensagem base.',
                            'Se necessário, marque ou desmarque Template ativo.',
                        ],
                    ],
                    [
                        'title' => 'Cadastre e revise as variáveis',
                        'items' => [
                            'No campo Variáveis adicionais, informe uma variável por linha quando precisar complementar o catálogo padrão.',
                            'Consulte a área Variáveis suportadas antes de salvar para usar placeholders válidos.',
                            'Clique em Salvar template ao finalizar.',
                        ],
                    ],
                    [
                        'title' => 'Use o template dentro da campanha',
                        'items' => [
                            'Abra ou crie uma Campanha.',
                            'Na parte de conteúdo do WhatsApp, selecione o template disponível quando o fluxo da campanha pedir essa escolha.',
                            'Envie um teste pela tela da campanha para validar o resultado.',
                        ],
                    ],
                ],
                'notes' => [
                    'No fluxo atual do Tenant, o template local de campanha é voltado ao WhatsApp. A tela não expõe um seletor de canal para o template local.',
                ],
            ],
            'pacientes' => [
                'title' => '9. Pacientes',
                'bg' => 'bg-white',
                'intro' => 'O cadastro de Pacientes é base para Agendamentos, Consultas Online, Formulários, Respostas e Portal do Paciente.',
                'steps' => [
                    [
                        'title' => 'Abra a criação do paciente',
                        'items' => [
                            'No menu lateral, clique em Pacientes.',
                            'Clique em Novo Paciente.',
                        ],
                    ],
                    [
                        'title' => 'Preencha os dados principais',
                        'items' => [
                            'Informe nome, CPF, telefone e e-mail.',
                            'Complete os demais campos cadastrais exibidos na tela.',
                            'Mantenha telefone e e-mail atualizados para envio de notificações e instruções online.',
                        ],
                    ],
                    [
                        'title' => 'Salve e revise o cadastro',
                        'items' => [
                            'Clique em Salvar.',
                            'Abra a tela de detalhes para conferir os dados salvos.',
                            'Use Editar quando precisar ajustar contatos ou dados de identificação.',
                        ],
                    ],
                    [
                        'title' => 'Volte para a operação',
                        'items' => [
                            'Depois do cadastro, volte para Agendamentos para marcar a consulta.',
                            'Quando o paciente usar portal ou fluxo público, confira se os dados de contato estão corretos antes de enviar acessos ou mensagens.',
                        ],
                    ],
                ],
                'notes' => [
                    'Pacientes sem telefone ou e-mail podem impedir partes do fluxo de notificações e das Consultas Online.',
                ],
            ],
            'especialidades' => [
                'title' => '10. Profissionais e Especialidades',
                'bg' => 'bg-gray-50',
                'intro' => 'Cadastre especialidades e profissionais antes de abrir a agenda. Esses vínculos aparecem em filtros, agendamentos, formulários e relatórios.',
                'steps' => [
                    [
                        'title' => 'Cadastre as especialidades primeiro',
                        'items' => [
                            'No menu lateral, clique em Especialidades.',
                            'Clique em Novo ou Criar Especialidade.',
                            'Informe o nome da especialidade e os demais campos exibidos.',
                            'Clique em Salvar.',
                        ],
                    ],
                    [
                        'title' => 'Cadastre o profissional',
                        'items' => [
                            'No menu lateral, clique em Profissionais.',
                            'Clique em Novo Profissional.',
                            'Vincule o usuário correspondente quando a tela solicitar.',
                            'Preencha registro profissional, estado do registro e demais campos clínicos do cadastro.',
                            'Associe uma ou mais especialidades antes de salvar.',
                        ],
                    ],
                    [
                        'title' => 'Revise o vínculo com a agenda',
                        'items' => [
                            'Depois de salvar o profissional, siga para Agenda do Profissional para montar a disponibilidade.',
                            'Somente depois disso o profissional ficará pronto para receber agendamentos.',
                        ],
                    ],
                ],
                'notes' => [
                    'Padronize os nomes das especialidades para evitar duplicidade em filtros e relatórios.',
                ],
            ],
            'usuarios-e-permissoes' => [
                'title' => '11. Usuários e Permissões',
                'bg' => 'bg-white',
                'intro' => 'Use Usuários para controlar quem entra no Tenant, quais módulos cada pessoa acessa e quais profissionais um usuário comum pode operar.',
                'steps' => [
                    [
                        'title' => 'Crie o usuário',
                        'items' => [
                            'No menu lateral, clique em Usuários.',
                            'Clique em Novo Usuário.',
                            'Preencha nome, e-mail e senha.',
                        ],
                    ],
                    [
                        'title' => 'Defina papel e módulos',
                        'items' => [
                            'Na seção Configurações do cadastro, escolha o papel do usuário, como Admin, Usuário Comum ou Usuário Profissional.',
                            'Marque os módulos aos quais essa pessoa terá acesso.',
                            'Quando o papel for Usuário Comum, a tela pode trazer módulos pré-selecionados com base em Configurações e Usuários & Permissões.',
                        ],
                    ],
                    [
                        'title' => 'Salve e ajuste permissões por profissional',
                        'items' => [
                            'Clique em Salvar Usuário.',
                            'Na listagem, abra o usuário criado e entre em Permissões de Médicos ou Permissões de Profissionais quando esse vínculo for necessário.',
                            'Selecione os profissionais permitidos e clique em Salvar Permissões.',
                        ],
                    ],
                    [
                        'title' => 'Faça manutenção quando necessário',
                        'items' => [
                            'Use Editar Usuário para atualizar dados cadastrais.',
                            'Use Alterar Senha quando precisar redefinir acesso.',
                            'Revise os módulos sempre que um usuário disser que não encontra uma área do sistema.',
                        ],
                    ],
                ],
                'notes' => [
                    'Administradores veem o ambiente completo. Usuários comuns só enxergam os módulos e profissionais liberados para eles.',
                ],
            ],
            'agenda-profissional' => [
                'title' => '12. Agenda do Profissional',
                'bg' => 'bg-gray-50',
                'intro' => 'A Agenda do Profissional concentra a disponibilidade, os horários e os tipos que sustentam os Agendamentos e as Consultas Online.',
                'steps' => [
                    [
                        'title' => 'Crie a agenda do profissional',
                        'items' => [
                            'Acesse Configuração de Atendimento e depois Agenda do Profissional.',
                            'Clique em Nova Agenda do Profissional.',
                            'Preencha Profissional, Nome da agenda, Identificador externo quando precisar e Status da agenda.',
                        ],
                    ],
                    [
                        'title' => 'Cadastre os horários de atendimento',
                        'items' => [
                            'Na seção Horários de atendimento, clique em Adicionar horário.',
                            'Escolha o Dia da semana.',
                            'Preencha Hora inicial e Hora final.',
                            'Se houver pausa, marque Possui intervalo e preencha o início e o fim do intervalo.',
                            'Clique em Salvar horário e repita o processo para os demais dias.',
                        ],
                    ],
                    [
                        'title' => 'Vincule os tipos de consulta',
                        'items' => [
                            'Na seção Tipos vinculados, clique em Adicionar tipo.',
                            'Preencha Nome, Duração e Status do tipo.',
                            'Repita a ação para todos os tipos que a agenda precisa oferecer.',
                        ],
                    ],
                    [
                        'title' => 'Salve a agenda e revise',
                        'items' => [
                            'Clique em Salvar Agenda ou Atualizar Agenda conforme a tela.',
                            'Abra Visualizar Agenda do Profissional para conferir nome, status, horários e tipos salvos.',
                        ],
                    ],
                    [
                        'title' => 'Use as telas auxiliares quando necessário',
                        'items' => [
                            'Abra Calendários quando quiser consultar visualmente os eventos e a agenda associada.',
                            'Abra Horários Comerciais se sua operação também usa esse cadastro separado para organizar disponibilidade.',
                            'Abra Tipos de Consulta quando precisar revisar os tipos cadastrados em tela própria.',
                        ],
                    ],
                ],
                'notes' => [
                    'Sem agenda ativa, horários válidos e tipos vinculados, o sistema não conseguirá oferecer disponibilidade correta para os agendamentos.',
                ],
            ],
            'formularios' => [
                'title' => '13. Formulários',
                'bg' => 'bg-white',
                'intro' => 'Use Formulários para criar questionários ligados ao profissional e, quando necessário, à especialidade.',
                'steps' => [
                    [
                        'title' => 'Crie o formulário base',
                        'items' => [
                            'No menu lateral, clique em Formulários.',
                            'Clique em Novo Formulário.',
                            'Selecione o profissional.',
                            'Selecione a especialidade quando o campo estiver disponível.',
                            'Preencha os demais campos básicos e clique em Salvar Formulário.',
                        ],
                    ],
                    [
                        'title' => 'Abra o construtor',
                        'items' => [
                            'Na tela do formulário, clique em Construir.',
                            'Use Adicionar Seção para organizar blocos do formulário.',
                            'Use Adicionar Pergunta para criar os campos de cada seção ou perguntas gerais.',
                        ],
                    ],
                    [
                        'title' => 'Monte as perguntas',
                        'items' => [
                            'Preencha o texto da pergunta.',
                            'Adicione Texto de Ajuda quando precisar orientar o paciente.',
                            'Escolha o Tipo de Resposta, como Texto, Número, Data, Sim ou Não, Escolha Única ou Escolha Múltipla.',
                            'Marque Campo obrigatório quando a resposta não puder ficar vazia.',
                            'Para perguntas de escolha, adicione e edite as opções necessárias.',
                        ],
                    ],
                    [
                        'title' => 'Revise e publique o uso',
                        'items' => [
                            'Use a visualização do formulário para conferir seções e perguntas.',
                            'Depois acompanhe as respostas em Respostas ou dentro do contexto do Atendimento.',
                        ],
                    ],
                ],
                'notes' => [
                    'Criar o formulário base e construir o conteúdo são etapas separadas. Primeiro salve o cadastro, depois abra o construtor.',
                ],
            ],
            'respostas' => [
                'title' => '14. Respostas',
                'bg' => 'bg-gray-50',
                'intro' => 'Use Respostas para acompanhar o que já foi preenchido, conferir respostas pendentes e consultar dados no contexto do paciente e do agendamento.',
                'steps' => [
                    [
                        'title' => 'Abra a listagem de respostas',
                        'items' => [
                            'No menu lateral, clique em Respostas.',
                            'Use a listagem para localizar o formulário, o paciente e o status da resposta.',
                        ],
                    ],
                    [
                        'title' => 'Consulte ou edite a resposta',
                        'items' => [
                            'Clique em uma resposta para abrir Detalhes da Resposta.',
                            'Na tela de detalhes, confira formulário, paciente, agendamento e data de envio.',
                            'Percorra as seções e perguntas para revisar tudo o que foi respondido.',
                            'Quando o fluxo permitir, use Editar para ajustar a resposta.',
                        ],
                    ],
                    [
                        'title' => 'Leve a resposta para a rotina clínica',
                        'items' => [
                            'Use os dados da resposta antes de iniciar o atendimento.',
                            'Se preferir, abra a mesma resposta a partir da rotina de Atendimento quando o paciente estiver na sessão do dia.',
                        ],
                    ],
                ],
                'notes' => [
                    'As respostas ajudam a recepção e o profissional a chegar ao atendimento com as informações já preenchidas pelo paciente.',
                ],
            ],
            'integracoes-e-sincronizacao' => [
                'title' => '15. Integrações e Sincronização',
                'bg' => 'bg-white',
                'intro' => 'A sincronização da agenda do profissional e as integrações gerais do Tenant ficam divididas entre Agenda do Profissional e Configurações.',
                'steps' => [
                    [
                        'title' => 'Abra a agenda correta',
                        'items' => [
                            'Acesse Configuração de Atendimento e depois Agenda do Profissional.',
                            'Abra a agenda já criada do profissional que deseja conectar.',
                        ],
                    ],
                    [
                        'title' => 'Entre na tela de Sincronização',
                        'items' => [
                            'Na agenda do profissional, clique na ação de sincronização.',
                            'A tela Sincronização de Calendário exibirá o profissional, a agenda e a Última sincronização.',
                        ],
                    ],
                    [
                        'title' => 'Conecte Google Calendar ou Apple Calendar',
                        'items' => [
                            'Na área Google Calendar, clique em Conectar Google Calendar ou Trocar conta conforme o caso.',
                            'Na área Apple Calendar, clique em Conectar Apple Calendar quando precisar usar esse fluxo.',
                            'Se já existir vínculo e sua permissão permitir, use Desconectar para revogar a conta conectada.',
                        ],
                    ],
                    [
                        'title' => 'Revise integrações gerais do Tenant',
                        'items' => [
                            'Acesse Configurações e abra a aba Integrações para revisar as políticas de sincronização automática.',
                            'Use essa área para governança administrativa e status geral das integrações do Tenant.',
                        ],
                    ],
                ],
                'notes' => [
                    'Em alguns cenários a autenticação da conta precisa ser feita pelo próprio profissional. A tela informa quando o perfil atual só pode governar o vínculo sem conectar diretamente.',
                ],
            ],
            'relatorios' => [
                'title' => '16. Relatórios',
                'bg' => 'bg-gray-50',
                'intro' => 'Use Relatórios para consultar dados operacionais por módulo e exportar o resultado do período em formatos do dia a dia.',
                'steps' => [
                    [
                        'title' => 'Abra o painel de relatórios',
                        'items' => [
                            'No menu lateral, clique em Relatórios.',
                            'Escolha o card da área desejada, como Agendamentos, Pacientes, Médicos, Recorrências, Formulários, Portal do Paciente ou Notificações.',
                        ],
                    ],
                    [
                        'title' => 'Aplique os filtros do relatório',
                        'items' => [
                            'Na tela escolhida, defina período, profissional, status e outros filtros disponíveis.',
                            'Atualize a grade ou a consulta para carregar os dados filtrados.',
                        ],
                    ],
                    [
                        'title' => 'Exporte quando necessário',
                        'items' => [
                            'Use os botões de exportação da tela para gerar o relatório.',
                            'Os relatórios atuais contam com exportação em Excel e PDF.',
                        ],
                    ],
                    [
                        'title' => 'Use o relatório certo para cada decisão',
                        'items' => [
                            'Agendamentos ajuda a acompanhar volume e status de consultas.',
                            'Pacientes e Médicos ajudam na análise cadastral e operacional.',
                            'Recorrências, Formulários, Portal do Paciente e Notificações ajudam a revisar fluxos específicos.',
                        ],
                    ],
                ],
                'notes' => [
                    'Se um relatório não aparecer, confirme se o usuário recebeu acesso ao módulo Relatórios.',
                ],
            ],
            'configuracoes' => [
                'title' => '17. Configurações',
                'bg' => 'bg-white',
                'intro' => 'Configurações reúne os ajustes gerais do Tenant. É a área mais importante para liberar módulos e padronizar o comportamento do sistema.',
                'steps' => [
                    [
                        'title' => 'Revise as abas principais',
                        'items' => [
                            'Acesse Configurações.',
                            'Abra as abas Clínica, Geral, Agendamentos, Calendário e Profissionais para revisar parâmetros básicos do ambiente.',
                            'Clique em Salvar Alterações sempre que editar uma dessas abas.',
                        ],
                    ],
                    [
                        'title' => 'Ajuste usuários, notificações e campanhas',
                        'items' => [
                            'Abra a aba Usuários para configurar o comportamento padrão de módulos e permissões do Usuário Comum.',
                            'Abra a aba Notificações para revisar envio por email, WhatsApp e notificações ligadas a formulários.',
                            'Abra a aba Campanhas para revisar canais, herança de notificações e testes dos canais usados pelas campanhas.',
                        ],
                    ],
                    [
                        'title' => 'Revise integrações e link público',
                        'items' => [
                            'Abra a aba Integrações para revisar a governança das integrações e a sincronização automática.',
                            'Abra a aba Link Público para revisar o fluxo público de agendamento do Tenant.',
                        ],
                    ],
                    [
                        'title' => 'Use as abas condicionais quando o Tenant possuir esses recursos',
                        'items' => [
                            'Se o Tenant tiver Templates Oficiais, abra a aba Templates Oficiais para gerenciar esse catálogo.',
                            'Se o Tenant tiver Financeiro, abra a aba Financeiro ou Configurações Financeiras para revisar o módulo.',
                            'Se o Tenant tiver WAHA, Evolution ou Bot de WhatsApp, revise também essas abas específicas.',
                        ],
                    ],
                ],
                'notes' => [
                    'Módulos como Campanhas, Consultas Online, Financeiro e integrações dependem diretamente do que for habilitado ou configurado nesta área.',
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

                <div class="space-y-6">
                    @foreach ($section['steps'] as $index => $step)
                        <div class="rounded-lg {{ $section['bg'] === 'bg-white' ? 'bg-gray-50' : 'bg-white' }} border-l-4 border-blue-600 p-6 shadow-md">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                                    {{ $index + 1 }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="mb-3 text-xl font-semibold text-gray-900">{{ $step['title'] }}</h3>
                                    <ol class="ml-5 list-decimal space-y-2 text-gray-700">
                                        @foreach ($step['items'] as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if (!empty($section['notes']))
                        <div class="rounded-lg border-l-4 border-blue-600 bg-blue-50 p-6">
                            <h3 class="mb-3 text-lg font-semibold text-gray-900">Observações e Dicas</h3>
                            <ul class="ml-5 list-disc space-y-2 text-gray-700">
                                @foreach ($section['notes'] as $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
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
