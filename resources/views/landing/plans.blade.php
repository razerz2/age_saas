@extends('landing.layout')

@section('title', 'Planos e Preços - Sistema de Agendamentos')
@section('description', 'Escolha o plano ideal para sua clínica. Planos flexíveis para profissionais de saúde de todos os tamanhos.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-blue-50 via-white to-blue-50 py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Planos e <span class="text-blue-600">Preços</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Escolha o plano ideal para sua clínica. Planos flexíveis para profissionais de saúde de todos os tamanhos.
                </p>
            </div>
        </div>
    </section>

    <!-- Planos Disponíveis -->
    @if($plans && $plans->count() > 0)
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Escolha o Plano Ideal</h2>
                <p class="text-lg text-gray-600">Todos os nossos planos incluem suporte completo e atualizações regulares</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($plans as $plan)
                <div class="bg-white rounded-xl shadow-lg border-2 {{ $loop->index === 1 && $plans->count() >= 3 ? 'border-blue-600 transform scale-105' : 'border-gray-200' }} hover:shadow-2xl transition-all duration-300">
                    @if($loop->index === 1 && $plans->count() >= 3)
                    <div class="bg-blue-600 text-white text-center py-2 rounded-t-xl">
                        <span class="text-sm font-semibold">MAIS POPULAR</span>
                    </div>
                    @endif
                    
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                            <div class="mb-2">
                                <span class="text-5xl font-bold text-blue-600">{{ $plan->formatted_price }}</span>
                                <span class="text-gray-600">/mês</span>
                            </div>
                            @if($plan->periodicity === 'yearly')
                            <p class="text-sm text-gray-500">Faturamento anual</p>
                            @else
                            <p class="text-sm text-gray-500">Faturamento mensal</p>
                            @endif
                        </div>
                        
                        @if($plan->description)
                        <div class="mb-4 text-center">
                            <p class="text-gray-600 text-sm">{{ $plan->description }}</p>
                        </div>
                        @endif
                        
                        <ul class="space-y-4 mb-8">
                            @if(!empty($plan->features) && is_array($plan->features) && count($plan->features) > 0)
                                @foreach(array_slice($plan->features, 0, 6) as $feature)
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">{{ $feature }}</span>
                                </li>
                                @endforeach
                                @if(count($plan->features) > 6)
                                <li class="text-center pt-2">
                                    <button onclick="openPlanModal('{{ $plan->id }}')" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                                        Ver todos os recursos ({{ count($plan->features) }})
                                    </button>
                                </li>
                                @endif
                            @else
                                <li class="text-gray-500 text-center py-4">Sem recursos cadastrados</li>
                            @endif
                        </ul>
                        
                        <div class="mt-8">
                            <button onclick="openPreRegisterModal('{{ $plan->id }}', '{{ addslashes($plan->name) }}', '{{ $plan->formatted_price }}')" 
                                class="w-full {{ $loop->index === 1 && $plans->count() >= 3 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-900 hover:bg-gray-800' }} text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Escolher Plano
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @else
    <section class="py-16 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center py-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Planos Sugeridos</h2>
                <p class="text-gray-600 mb-8">Abaixo estão nossos planos sugeridos:</p>
                
                <!-- Planos Sugeridos (quando não há planos no banco) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <!-- Plano Essencial -->
                    <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:shadow-2xl transition-all duration-300">
                        <div class="p-8">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Essencial</h3>
                                <div class="mb-2">
                                    <span class="text-5xl font-bold text-blue-600">R$ 99</span>
                                    <span class="text-gray-600">/mês</span>
                                </div>
                                <p class="text-sm text-gray-500">Ideal para profissionais individuais</p>
                            </div>
                            
                            <ul class="space-y-4 mb-8">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">1 médico</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Agenda simples</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Formulários básicos</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Portal do paciente</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Notificações básicas</span>
                                </li>
                            </ul>
                            
                            <a href="{{ route('landing.contact') }}" 
                                class="block w-full text-center bg-gray-900 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Falar com Comercial
                            </a>
                        </div>
                    </div>
                    
                    <!-- Plano Profissional -->
                    <div class="bg-white rounded-xl shadow-lg border-2 border-blue-600 transform scale-105 hover:shadow-2xl transition-all duration-300">
                        <div class="bg-blue-600 text-white text-center py-2 rounded-t-xl">
                            <span class="text-sm font-semibold">MAIS POPULAR</span>
                        </div>
                        <div class="p-8">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Profissional</h3>
                                <div class="mb-2">
                                    <span class="text-5xl font-bold text-blue-600">R$ 299</span>
                                    <span class="text-gray-600">/mês</span>
                                </div>
                                <p class="text-sm text-gray-500">Ideal para clínicas pequenas</p>
                            </div>
                            
                            <ul class="space-y-4 mb-8">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Vários médicos</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Agendamentos online</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Agendamentos recorrentes</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Formulários avançados</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Google Calendar</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Atendimento Médico</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Relatórios</span>
                                </li>
                            </ul>
                            
                            <a href="{{ route('landing.contact') }}" 
                                class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Falar com Comercial
                            </a>
                        </div>
                    </div>
                    
                    <!-- Plano Enterprise -->
                    <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 hover:shadow-2xl transition-all duration-300">
                        <div class="p-8">
                            <div class="text-center mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Enterprise</h3>
                                <div class="mb-2">
                                    <span class="text-5xl font-bold text-blue-600">Sob Consulta</span>
                                </div>
                                <p class="text-sm text-gray-500">Ideal para grandes clínicas</p>
                            </div>
                            
                            <ul class="space-y-4 mb-8">
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Tudo ilimitado</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Suporte prioritário</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">Personalização</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">SLA empresarial</span>
                                </li>
                            </ul>
                            
                            <a href="{{ route('landing.contact') }}" 
                                class="block w-full text-center bg-gray-900 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Falar com Comercial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Modal de Pré-Cadastro -->
    <div id="preRegisterModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-gray-900">Pré-Cadastro</h3>
                    <button onclick="closePreRegisterModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="preRegisterForm" class="p-6">
                @csrf
                <input type="hidden" name="plan_id" id="selected_plan_id">
                
                <div id="planSummary" class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-gray-700"><strong>Plano selecionado:</strong> <span id="selected_plan_name"></span></p>
                    <p class="text-gray-700"><strong>Valor:</strong> <span id="selected_plan_price"></span>/mês</p>
                </div>
                
                <div id="noPlanWarning" class="hidden mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-800">
                    <p class="font-semibold">Atenção:</p>
                    <p class="text-sm">Para realizar o pré-cadastro, é necessário selecionar um plano cadastrado no sistema. Entre em contato com nossa equipe comercial para mais informações.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Legal *</label>
                        <input type="text" id="name" name="name" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="fantasy_name" class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                        <input type="text" id="fantasy_name" name="fantasy_name" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="email" name="email" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="text" id="phone" name="phone" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="document" class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                    <input type="text" id="document" name="document" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="mb-4 hidden">
                    <!-- Campo oculto - subdomínio será gerado automaticamente -->
                    <input type="hidden" id="subdomain_suggested" name="subdomain_suggested" value="">
                </div>
                
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>ℹ️ Informação:</strong> O subdomínio será gerado automaticamente a partir do nome fantasia (ou nome legal). 
                        Seu sistema estará disponível em: <code class="bg-blue-100 px-2 py-1 rounded">/workspace/seu-subdominio</code>
                    </p>
                </div>
                
                <div id="formError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"></div>
                <div id="formSuccess" class="hidden mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700"></div>
                
                <div class="mb-4">
                    <label class="flex items-start">
                        <input type="checkbox" name="accept_terms" required 
                            class="mt-1 mr-2 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm text-gray-700">
                            Eu aceito os 
                            <a href="https://www.allsync.com.br/termos-de-servico" target="_blank" 
                                class="text-blue-600 hover:text-blue-700 underline">Termos de Uso</a> 
                            e a 
                            <a href="https://www.allsync.com.br/politica-de-privacidade" target="_blank" 
                                class="text-blue-600 hover:text-blue-700 underline">Política de Privacidade</a>
                        </span>
                    </label>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button type="button" onclick="closePreRegisterModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                        Continuar para Pagamento
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Plano Completo -->
    <div id="planModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-2xl font-bold text-gray-900" id="planModalTitle">Detalhes do Plano</h3>
                    <button onclick="closePlanModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="mb-2">
                        <span class="text-4xl font-bold text-blue-600" id="planModalPrice"></span>
                        <span class="text-gray-600">/mês</span>
                    </div>
                    <p class="text-sm text-gray-500" id="planModalPeriodicity"></p>
                </div>
                
                <div id="planModalDescription" class="mb-6 text-center text-gray-600"></div>
                
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Recursos Inclusos:</h4>
                    <ul class="space-y-3" id="planModalFeatures">
                        <!-- Preenchido via JavaScript -->
                    </ul>
                </div>
                
                <div class="flex justify-end gap-4">
                    <button onclick="closePlanModal()" 
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Fechar
                    </button>
                    <button onclick="selectPlanFromModal()" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                        Escolher Este Plano
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <section class="py-16 lg:py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Dúvidas sobre os planos?</h2>
            <p class="text-xl text-gray-600 mb-8">
                Entre em contato com nossa equipe para esclarecer suas dúvidas
            </p>
            <a href="{{ route('landing.contact') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-colors shadow-lg">
                Falar com Comercial
            </a>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    let currentPlanId = null;
    let currentPlanName = null;
    let currentPlanPrice = null;

    async function openPlanModal(planId) {
        try {
            const response = await fetch('{{ url("/planos/json") }}/' + planId);
            const plan = await response.json();
            
            if (!response.ok) {
                throw new Error('Erro ao carregar dados do plano');
            }
            
            // Preencher dados do modal
            document.getElementById('planModalTitle').textContent = plan.name;
            document.getElementById('planModalPrice').textContent = plan.formatted_price;
            document.getElementById('planModalPeriodicity').textContent = plan.periodicity;
            
            // Descrição
            const descriptionDiv = document.getElementById('planModalDescription');
            if (plan.description) {
                descriptionDiv.textContent = plan.description;
                descriptionDiv.classList.remove('hidden');
            } else {
                descriptionDiv.classList.add('hidden');
            }
            
            // Features
            const featuresList = document.getElementById('planModalFeatures');
            featuresList.innerHTML = '';
            if (plan.features && plan.features.length > 0) {
                plan.features.forEach(feature => {
                    const li = document.createElement('li');
                    li.className = 'flex items-start';
                    li.innerHTML = `
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-gray-700">${feature}</span>
                    `;
                    featuresList.appendChild(li);
                });
            } else {
                featuresList.innerHTML = '<li class="text-gray-500 text-center py-4">Sem recursos cadastrados</li>';
            }
            
            // Armazenar dados para o botão "Escolher Este Plano"
            currentPlanId = plan.id;
            currentPlanName = plan.name;
            currentPlanPrice = plan.formatted_price;
            
            // Mostrar modal
            document.getElementById('planModal').classList.remove('hidden');
            document.getElementById('planModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        } catch (error) {
            console.error('Erro ao carregar plano:', error);
            alert('Erro ao carregar informações do plano. Tente novamente.');
        }
    }
    
    function closePlanModal() {
        document.getElementById('planModal').classList.add('hidden');
        document.getElementById('planModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        currentPlanId = null;
        currentPlanName = null;
        currentPlanPrice = null;
    }
    
    function selectPlanFromModal() {
        if (currentPlanId) {
            closePlanModal();
            openPreRegisterModal(currentPlanId, currentPlanName, currentPlanPrice);
        }
    }
    
    function openPreRegisterModal(planId, planName, planPrice) {
        // Validar se há plan_id antes de abrir o modal
        if (!planId) {
            alert('Por favor, selecione um plano cadastrado. Entre em contato com nossa equipe comercial para mais informações.');
            window.location.href = '{{ route("landing.contact") }}';
            return;
        }
        
        document.getElementById('selected_plan_id').value = planId || '';
        document.getElementById('selected_plan_name').textContent = planName;
        document.getElementById('selected_plan_price').textContent = planPrice;
        
        // Mostrar/ocultar aviso baseado na presença de plan_id
        const noPlanWarning = document.getElementById('noPlanWarning');
        if (!planId) {
            noPlanWarning.classList.remove('hidden');
        } else {
            noPlanWarning.classList.add('hidden');
        }
        
        document.getElementById('preRegisterModal').classList.remove('hidden');
        document.getElementById('preRegisterModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    
    function closePreRegisterModal() {
        document.getElementById('preRegisterModal').classList.add('hidden');
        document.getElementById('preRegisterModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
        document.getElementById('preRegisterForm').reset();
        document.getElementById('formError').classList.add('hidden');
        document.getElementById('formSuccess').classList.add('hidden');
    }
    
    document.getElementById('preRegisterForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const planId = document.getElementById('selected_plan_id').value;
        const acceptTerms = document.querySelector('input[name="accept_terms"]').checked;
        
        // Validar se há plan_id antes de enviar
        if (!planId) {
            const errorDiv = document.getElementById('formError');
            errorDiv.textContent = 'Por favor, selecione um plano cadastrado. Entre em contato com nossa equipe comercial para mais informações.';
            errorDiv.classList.remove('hidden');
            return;
        }
        
        // Validar aceite dos termos
        if (!acceptTerms) {
            const errorDiv = document.getElementById('formError');
            errorDiv.textContent = 'Você deve aceitar os Termos de Uso e a Política de Privacidade para continuar.';
            errorDiv.classList.remove('hidden');
            return;
        }
        
        const formData = new FormData(this);
        const errorDiv = document.getElementById('formError');
        const successDiv = document.getElementById('formSuccess');
        
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        
        try {
            const response = await fetch('{{ route("landing.pre-register") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            const data = await response.json();
            
            if (response.ok && data.success) {
                successDiv.textContent = 'Pré-cadastro realizado com sucesso! Redirecionando para pagamento...';
                successDiv.classList.remove('hidden');
                
                if (data.payment_url) {
                    // Redireciona imediatamente para a página de pagamento do Asaas
                    window.location.href = data.payment_url;
                } else {
                    errorDiv.textContent = 'Erro: Link de pagamento não foi gerado. Entre em contato com o suporte.';
                    errorDiv.classList.remove('hidden');
                    console.error('Link de pagamento não retornado', data);
                }
            } else {
                // Tratar erros de validação
                let errorMessage = 'Erro ao processar pré-cadastro. Tente novamente.';
                if (data.errors) {
                    // Se houver erros de validação do Laravel
                    const errors = Object.values(data.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (data.error) {
                    errorMessage = data.error;
                }
                errorDiv.textContent = errorMessage;
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = 'Erro ao processar pré-cadastro. Verifique sua conexão e tente novamente.';
            errorDiv.classList.remove('hidden');
        }
    });
</script>
@endpush
