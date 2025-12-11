@extends('layouts.public')

@section('title', 'Política de Privacidade')
@section('description', 'Política de Privacidade do AllSync - Sistema de agendamentos para clínicas e profissionais de saúde')

@section('content')
<div class="prose prose-lg max-w-none">
    <h1 class="text-4xl font-bold text-gray-900 mb-6">Política de Privacidade</h1>
    
    <p class="text-gray-600 mb-6">
        <strong>Última atualização:</strong> {{ date('d/m/Y') }}
    </p>
    
    <div class="space-y-6 text-gray-700">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">1. Sobre o AllSync</h2>
            <p>
                O AllSync é uma plataforma SaaS (Software como Serviço) especializada em agendamentos médicos, 
                desenvolvida para clínicas, consultórios, psicólogos, odontologias e demais profissionais de saúde. 
                Nossa missão é fornecer uma solução completa e segura para gestão de agendamentos, pacientes, 
                médicos e integrações com calendários.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">2. Dados Coletados</h2>
            <p class="mb-4">
                Para o funcionamento adequado do sistema, coletamos e processamos os seguintes tipos de dados:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li><strong>Dados de Pacientes:</strong> Nome, CPF, data de nascimento, telefone, e-mail, endereço e outras informações necessárias para o agendamento e atendimento médico.</li>
                <li><strong>Dados de Usuários da Clínica:</strong> Informações dos profissionais e funcionários que utilizam o sistema, incluindo nome, e-mail, função e permissões de acesso.</li>
                <li><strong>Dados de Médicos/Profissionais:</strong> Informações profissionais como CRM, CRP, CRO ou outros registros profissionais, além de dados de contato e especialidades.</li>
                <li><strong>Registros de Login:</strong> Informações sobre acessos ao sistema, incluindo data, hora e endereço IP, para fins de segurança e auditoria.</li>
                <li><strong>Dados de Integrações:</strong> Informações necessárias para integração com serviços externos, como Google Calendar, Apple Calendar e WhatsApp.</li>
                <li><strong>Dados de Agendamentos:</strong> Histórico completo de agendamentos, incluindo datas, horários, status e observações.</li>
            </ul>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">3. Uso dos Dados</h2>
            <p class="mb-4">
                Todos os dados coletados são utilizados exclusivamente para:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Fornecer e melhorar os serviços de agendamento e gestão de clínicas</li>
                <li>Facilitar a comunicação entre clínicas, profissionais e pacientes</li>
                <li>Garantir a segurança e integridade do sistema</li>
                <li>Cumprir obrigações legais e regulatórias</li>
                <li>Enviar notificações automáticas sobre agendamentos (e-mail, SMS, WhatsApp)</li>
            </ul>
            <p class="font-semibold text-gray-900">
                <strong>Importante:</strong> Não vendemos, alugamos ou compartilhamos seus dados com terceiros para fins comerciais. 
                Os dados são utilizados apenas para o funcionamento do sistema e prestação dos serviços contratados.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">4. Integração com Google Calendar</h2>
            <p class="mb-4">
                O AllSync oferece integração com o Google Calendar para sincronização automática de agendamentos. 
                Esta integração utiliza o protocolo OAuth 2.0 do Google, que é um padrão de segurança amplamente reconhecido.
            </p>
            <p class="mb-4">
                <strong>Como funciona:</strong>
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Quando você autoriza a integração, o Google solicita permissão para acessar seu calendário</li>
                <li>O AllSync utiliza apenas as permissões necessárias para criar, editar e excluir eventos no seu calendário</li>
                <li>Os dados de acesso (tokens OAuth) são armazenados de forma segura e criptografada</li>
                <li>A sincronização ocorre automaticamente quando há alterações nos agendamentos</li>
            </ul>
            <p class="mb-4">
                <strong>Como revogar o acesso:</strong>
            </p>
            <ol class="list-decimal pl-6 space-y-2 mb-4">
                <li>Acesse sua conta do Google em <a href="https://myaccount.google.com" target="_blank" class="text-blue-600 hover:underline">myaccount.google.com</a></li>
                <li>Navegue até "Segurança" → "Acesso de terceiros" ou "Apps conectados"</li>
                <li>Localize o AllSync na lista de aplicativos</li>
                <li>Clique em "Remover acesso" ou "Desconectar"</li>
            </ol>
            <p>
                Após revogar o acesso, a sincronização automática será interrompida. Você pode reconectar a qualquer momento através das configurações do sistema.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">5. Remoção de Dados</h2>
            <p class="mb-4">
                Você tem o direito de solicitar a remoção de seus dados pessoais a qualquer momento, conforme previsto na LGPD (Lei Geral de Proteção de Dados).
            </p>
            <p class="mb-4">
                Para solicitar a remoção de dados, entre em contato conosco através do e-mail de suporte:
            </p>
            <p class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4">
                <strong>E-mail de Suporte:</strong> <a href="mailto:suporte@allsync.com.br" class="text-blue-600 hover:underline">suporte@allsync.com.br</a>
            </p>
            <p>
                Ao receber sua solicitação, analisaremos o pedido e procederemos com a remoção dos dados, respeitando os prazos legais e as obrigações de retenção de dados quando aplicável (por exemplo, dados contábeis ou fiscais que devem ser mantidos por determinado período).
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">6. Conformidade com a LGPD</h2>
            <p class="mb-4">
                O AllSync está comprometido com a conformidade da Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018). 
                Implementamos medidas técnicas e organizacionais adequadas para proteger os dados pessoais, incluindo:
            </p>
            <ul class="list-disc pl-6 space-y-2 mb-4">
                <li>Criptografia de dados sensíveis</li>
                <li>Ambientes isolados por clínica (multitenancy seguro)</li>
                <li>Controle de acesso baseado em permissões</li>
                <li>Registros de auditoria para rastreabilidade</li>
                <li>Backups seguros e recuperação de dados</li>
                <li>Treinamento regular da equipe sobre proteção de dados</li>
            </ul>
            <p>
                Todos os dados são armazenados em servidores localizados no Brasil, garantindo que as informações permaneçam sob a jurisdição brasileira.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">7. Alterações nesta Política</h2>
            <p>
                Reservamo-nos o direito de atualizar esta Política de Privacidade periodicamente. 
                Quando houver alterações significativas, notificaremos os usuários através do e-mail cadastrado ou por meio de aviso no sistema. 
                A data da última atualização sempre estará indicada no topo desta página.
            </p>
        </section>
        
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mt-8 mb-4">8. Contato</h2>
            <p class="mb-4">
                Para questões relacionadas a esta Política de Privacidade ou ao tratamento de dados pessoais, entre em contato:
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

