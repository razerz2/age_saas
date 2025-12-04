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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($plans as $plan)
                <div class="bg-white rounded-xl shadow-lg border-2 {{ $loop->index === 1 ? 'border-blue-600 transform scale-105' : 'border-gray-200' }} hover:shadow-2xl transition-all duration-300">
                    @if($loop->index === 1)
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
                        
                        <ul class="space-y-4 mb-8">
                            @if($plan->features && count($plan->features) > 0)
                                @foreach($plan->features as $feature)
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-gray-700">{{ $feature }}</span>
                                </li>
                                @endforeach
                            @else
                                <li class="text-gray-500 text-center py-4">Sem recursos cadastrados</li>
                            @endif
                        </ul>
                        
                        <div class="mt-8">
                            <button onclick="openPreRegisterModal('{{ $plan->id }}', '{{ $plan->name }}', '{{ $plan->formatted_price }}')" 
                                class="w-full {{ $loop->index === 1 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-900 hover:bg-gray-800' }} text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Escolher Plano
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @if($plans->count() === 0)
            <div class="text-center py-12">
                <p class="text-gray-600 text-lg">Nenhum plano disponível no momento.</p>
                <p class="text-gray-500 mt-2">Entre em contato para mais informações.</p>
            </div>
            @endif
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
                            
                            <button onclick="openPreRegisterModal(null, 'Essencial', 'R$ 99,00')" 
                                class="w-full bg-gray-900 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Escolher Plano
                            </button>
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
                            
                            <button onclick="openPreRegisterModal(null, 'Profissional', 'R$ 299,00')" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg">
                                Escolher Plano
                            </button>
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
                
                <div class="mb-4">
                    <label for="subdomain_suggested" class="block text-sm font-medium text-gray-700 mb-1">Subdomínio Sugerido</label>
                    <input type="text" id="subdomain_suggested" name="subdomain_suggested" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="minha-clinica">
                    <p class="text-sm text-gray-500 mt-1">Seu sistema estará disponível em: /t/seu-subdominio</p>
                </div>
                
                <div id="formError" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700"></div>
                <div id="formSuccess" class="hidden mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700"></div>
                
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
    function openPreRegisterModal(planId, planName, planPrice) {
        document.getElementById('selected_plan_id').value = planId || '';
        document.getElementById('selected_plan_name').textContent = planName;
        document.getElementById('selected_plan_price').textContent = planPrice;
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
                    setTimeout(() => {
                        window.location.href = data.payment_url;
                    }, 2000);
                }
            } else {
                errorDiv.textContent = data.error || 'Erro ao processar pré-cadastro. Tente novamente.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorDiv.textContent = 'Erro ao processar pré-cadastro. Verifique sua conexão e tente novamente.';
            errorDiv.classList.remove('hidden');
        }
    });
</script>
@endpush
