@extends('layouts.public')

@section('title', 'Termos de Serviço')
@section('description', 'Termos de Serviço do AllSync - Sistema de agendamentos para clínicas e profissionais de saúde')

@section('content')
<div class="prose prose-lg max-w-none">
    <h1 class="text-4xl font-bold text-gray-900 mb-6">Termos de Serviço</h1>
    
    <p class="text-gray-600 mb-6">
        <strong>Última atualização:</strong> {{ date('d/m/Y') }}
    </p>
    
    <div class="space-y-6 text-gray-700">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">1. Sobre o Serviço</h2>
            <p class="mb-4">
                O AllSync é uma plataforma SaaS (Software como Serviço) de gestão de agendamentos médicos, 
                desenvolvida para clínicas, consultórios, psicólogos, odontologias e demais profissionais de saúde.
            </p>
            <p class="mb-4">
                <strong>Área Tenant:</strong> Cada clínica contratante possui uma área isolada e exclusiva (tenant), 
                onde todos os dados, configurações e funcionalidades são gerenciados de forma independente. 
                Este isolamento garante total privacidade e segurança dos dados de cada cliente.
            </p>
            <p>
                O serviço inclui funcionalidades como agendamentos presenciais e online, gestão de pacientes, 
                médicos, formulários personalizados, integração com calendários (Google Calendar, Apple Calendar), 
                notificações automáticas (e-mail, SMS, WhatsApp), relatórios e muito mais.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">2. Responsabilidades do Usuário (Clínica)</h2>
            <p class="mb-4">
                Ao utilizar o AllSync, a clínica contratante assume as seguintes responsabilidades:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li><strong>Gerenciamento de Dados:</strong> A clínica é responsável por gerenciar corretamente todos os dados inseridos no sistema, incluindo dados de pacientes, médicos, agendamentos e configurações.</li>
                <li><strong>Conformidade com LGPD:</strong> A clínica deve garantir que possui autorização legal para coletar, armazenar e processar os dados pessoais de pacientes e usuários, em conformidade com a Lei Geral de Proteção de Dados (LGPD).</li>
                <li><strong>Uso Adequado:</strong> O sistema deve ser utilizado apenas para fins legítimos e profissionais relacionados à gestão de agendamentos médicos. É proibido o uso para atividades ilegais, fraudulentas ou que violem direitos de terceiros.</li>
                <li><strong>Segurança de Acesso:</strong> A clínica é responsável por manter a segurança das credenciais de acesso (usuário e senha), não compartilhando-as com pessoas não autorizadas.</li>
                <li><strong>Backup e Dados:</strong> Embora o AllSync realize backups automáticos, recomenda-se que a clínica mantenha cópias de segurança de dados críticos quando necessário.</li>
                <li><strong>Atualização de Informações:</strong> Manter informações cadastrais atualizadas, incluindo dados de contato e pagamento.</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">3. Responsabilidades do Provedor (AllSync)</h2>
            <p class="mb-4">
                O AllSync compromete-se a:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li><strong>Disponibilidade do Sistema:</strong> Manter o sistema operacional e acessível, garantindo alta disponibilidade e minimizando interrupções. Em caso de manutenção programada, os usuários serão notificados com antecedência.</li>
                <li><strong>Proteção de Dados:</strong> Implementar medidas técnicas e organizacionais adequadas para proteger os dados pessoais contra acesso não autorizado, perda, destruição ou alteração indevida, em conformidade com a LGPD.</li>
                <li><strong>Notificação de Incidentes:</strong> Em caso de incidentes de segurança que possam afetar os dados dos usuários, o AllSync notificará as clínicas afetadas e as autoridades competentes, quando aplicável, dentro dos prazos legais.</li>
                <li><strong>Suporte Técnico:</strong> Fornecer suporte técnico adequado para resolução de problemas e dúvidas relacionadas ao uso do sistema.</li>
                <li><strong>Melhorias Contínuas:</strong> Desenvolver e implementar melhorias no sistema, sempre priorizando a segurança e a experiência do usuário.</li>
                <li><strong>Ambiente Isolado:</strong> Garantir que cada tenant (clínica) possua um ambiente completamente isolado, sem compartilhamento de dados entre diferentes clientes.</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">4. Regras de Uso e Conduta</h2>
            <p class="mb-4">
                É expressamente proibido:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Utilizar o sistema para atividades ilegais ou fraudulentas</li>
                <li>Tentar acessar áreas do sistema não autorizadas ou de outros tenants</li>
                <li>Realizar engenharia reversa, descompilar ou desmontar o software</li>
                <li>Interferir no funcionamento do sistema ou tentar comprometer sua segurança</li>
                <li>Compartilhar credenciais de acesso com pessoas não autorizadas</li>
                <li>Utilizar o sistema para enviar spam, mensagens maliciosas ou conteúdo inadequado</li>
                <li>Violar direitos de propriedade intelectual ou outros direitos de terceiros</li>
            </ul>
            <p>
                O descumprimento destas regras pode resultar em suspensão ou encerramento imediato da conta, sem direito a reembolso.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">5. Cancelamento e Encerramento da Conta</h2>
            <p class="mb-4">
                <strong>Cancelamento pelo Usuário:</strong>
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>A clínica pode solicitar o cancelamento da assinatura a qualquer momento através do e-mail de suporte ou pela área administrativa do sistema.</li>
                <li>O cancelamento será efetivado ao final do período já pago, sem direito a reembolso proporcional.</li>
                <li>Após o cancelamento, a clínica terá um período de carência (geralmente 30 dias) para exportar seus dados antes do encerramento definitivo da conta.</li>
            </ul>
            <p class="mb-4">
                <strong>Encerramento pelo Provedor:</strong>
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>O AllSync reserva-se o direito de encerrar contas que violem estes Termos de Serviço, realizem atividades ilegais ou não efetuem o pagamento das mensalidades.</li>
                <li>Em caso de encerramento por violação dos termos, não haverá direito a reembolso.</li>
                <li>O AllSync também pode encerrar o serviço com aviso prévio de 90 dias em caso de descontinuação da plataforma, oferecendo alternativas de migração de dados.</li>
            </ul>
            <p>
                <strong>Retenção de Dados:</strong> Após o encerramento da conta, os dados serão mantidos por um período determinado pela legislação aplicável (ex: dados contábeis por 5 anos) e posteriormente excluídos de forma segura.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">6. Política de Comunicações Automáticas</h2>
            <p class="mb-4">
                O AllSync envia comunicações automáticas para facilitar o uso do sistema e melhorar a experiência:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li><strong>E-mails:</strong> Notificações de agendamentos, lembretes, confirmações, alterações e cancelamentos. Também enviamos e-mails administrativos sobre a conta, atualizações do sistema e informações importantes.</li>
                <li><strong>SMS:</strong> Lembretes e notificações de agendamentos quando configurado pela clínica.</li>
                <li><strong>WhatsApp:</strong> Notificações e lembretes de agendamentos quando a integração estiver ativa e autorizada.</li>
            </ul>
            <p class="mb-4">
                <strong>Controle de Comunicações:</strong>
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>As clínicas podem configurar quais tipos de notificações deseja enviar através das configurações do sistema.</li>
                <li>Pacientes podem optar por não receber determinadas comunicações, respeitando as configurações da clínica.</li>
                <li>E-mails administrativos essenciais (como notificações de pagamento e segurança) não podem ser desativados.</li>
            </ul>
            <p>
                Todas as comunicações são enviadas em conformidade com a LGPD e as preferências configuradas pelos usuários.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">7. Suporte Técnico e Contato</h2>
            <p class="mb-4">
                O AllSync oferece suporte técnico para resolução de problemas e esclarecimento de dúvidas:
            </p>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                <p class="mb-2"><strong>E-mail de Suporte:</strong> <a href="mailto:suporte@allsync.com.br" class="text-blue-600 hover:underline">suporte@allsync.com.br</a></p>
                <p class="mb-2"><strong>Horário de Atendimento:</strong> Segunda a sexta, das 9h às 18h (horário de Brasília)</p>
                <p><strong>Prazo de Resposta:</strong> Até 48 horas úteis para questões gerais. Questões críticas são priorizadas.</p>
            </div>
            <p class="mb-4">
                <strong>Canais de Suporte:</strong>
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>E-mail: Para questões técnicas, dúvidas sobre funcionalidades e problemas no sistema</li>
                <li>Documentação: Manual e guias disponíveis na área administrativa</li>
                <li>Notificações no Sistema: Avisos importantes são exibidos diretamente no painel administrativo</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">8. Limitação de Responsabilidade</h2>
            <p class="mb-4">
                O AllSync não se responsabiliza por:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Perda de dados decorrente de uso inadequado do sistema pela clínica</li>
                <li>Interrupções temporárias do serviço devido a manutenções programadas, falhas de infraestrutura de terceiros ou eventos de força maior</li>
                <li>Decisões clínicas ou médicas tomadas com base nas informações do sistema</li>
                <li>Problemas decorrentes de integrações com serviços de terceiros (Google Calendar, WhatsApp, etc.) quando estes serviços estiverem indisponíveis</li>
            </ul>
            <p>
                O AllSync se compromete a manter backups regulares e implementar medidas de segurança adequadas, mas recomenda que as clínicas mantenham cópias de segurança de dados críticos quando necessário.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">9. Propriedade Intelectual</h2>
            <p>
                Todo o conteúdo do AllSync, incluindo software, design, textos, gráficos, logos e funcionalidades, é de propriedade exclusiva do AllSync ou de seus licenciadores. 
                O uso do sistema não concede qualquer direito de propriedade sobre estes elementos. 
                A clínica possui apenas o direito de uso do serviço durante o período de assinatura ativa.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">10. Alterações nos Termos</h2>
            <p class="mb-4">
                O AllSync reserva-se o direito de modificar estes Termos de Serviço periodicamente. 
                Alterações significativas serão comunicadas aos usuários com pelo menos 30 dias de antecedência através de:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>E-mail enviado ao endereço cadastrado</li>
                <li>Notificação no painel administrativo do sistema</li>
            </ul>
            <p>
                O uso continuado do serviço após as alterações constitui aceitação dos novos termos. 
                Se a clínica não concordar com as alterações, poderá solicitar o cancelamento da assinatura.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">11. Lei Aplicável e Foro</h2>
            <p>
                Estes Termos de Serviço são regidos pela legislação brasileira. 
                Qualquer disputa relacionada a estes termos será resolvida no foro da comarca onde está sediada a empresa AllSync, 
                renunciando as partes a qualquer outro foro, por mais privilegiado que seja.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">12. Contato</h2>
            <p class="mb-4">
                Para questões relacionadas a estes Termos de Serviço, entre em contato:
            </p>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <p class="mb-2"><strong>AllSync</strong></p>
                <p class="mb-2">E-mail: <a href="mailto:suporte@allsync.com.br" class="text-blue-600 hover:underline">suporte@allsync.com.br</a></p>
                <p>Atendimento: De segunda a sexta, das 9h às 18h</p>
            </div>
        </section>
    </div>
</div>
@endsection

