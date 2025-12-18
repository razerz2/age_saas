@extends('layouts.freedash.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">

                @if ($errors->has('general'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first('general') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-building text-primary me-2"></i> Novo Tenant
                        </h4>
                        <a href="{{ route('Platform.tenants.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                    </div>

                    <div class="card-body">
                        {{-- ✅ Alertas de sucesso --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- ⚠️ Alertas de aviso --}}
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- 🔹 Exibição de erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                                <strong>Ops!</strong> Verifique os erros abaixo:
                                <ul class="mt-2 mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fechar"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('Platform.tenants.store') }}">
                            @csrf

                            {{-- Dados Principais --}}
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Razão Social *</label>
                                    <input type="text" name="legal_name" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nome Fantasia</label>
                                    <input type="text" name="trade_name" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Documento:</label>
                                    <div class="input-group">
                                        <select id="tipoDocumento" class="form-select" style="max-width: 100px;">
                                            <option value="cpf" selected>CPF</option>
                                            <option value="cnpj">CNPJ</option>
                                        </select>

                                        <input type="text" id="document" name="document" class="form-control"
                                            placeholder="Digite o CPF ou CNPJ">

                                        <button type="button" id="btnBuscarCnpj" class="btn btn-outline-info"
                                            style="display: none;">
                                            <i class="fas fa-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Subdomínio *</label>
                                    <input type="text" name="subdomain" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Rede de Clínicas (opcional)</label>
                                    <select name="network_id" id="network_id" class="form-select">
                                        <option value="">Nenhuma rede</option>
                                        @foreach($networks as $network)
                                            <option value="{{ $network->id }}">{{ $network->name }} ({{ $network->slug }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Plano *</label>
                                    <select name="plan_id" id="plan_id" class="form-select" required>
                                        <option value="">Selecione um plano...</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" data-category="{{ $plan->category }}">
                                                {{ $plan->name }} ({{ $plan->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="plan_help">Selecione uma rede para ver planos contratuais.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="active">Ativo</option>
                                        <option value="inactive">Inativo</option>
                                        <option value="pending">Pendente</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Trial até</label>
                                    <input type="date" name="trial_ends_at" class="form-control">
                                </div>

                                {{-- Localização --}}
                                <div class="col-12 mt-4">
                                    <h5 class="text-primary fw-bold mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i> Localização da Empresa
                                    </h5>
                                </div>

                                <input type="hidden" id="pais" name="pais_id" value="{{ $defaultCountryId ?? 31 }}">

                                <div class="col-md-6">
                                    <label class="form-label">Estado</label>
                                    <select id="estado" name="estado_id" class="form-select">
                                        <option value="">Carregando estados...</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Cidade</label>
                                    <select id="cidade" name="cidade_id" class="form-select">
                                        <option value="">Selecione o estado primeiro</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" name="endereco" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Número</label>
                                    <input type="text" name="n_endereco" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Complemento</label>
                                    <input type="text" name="complemento" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" name="bairro" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" name="cep" class="form-control">
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Salvar Tenant
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // -----------------------------
            //  🌎 País / Estado / Cidade
            // -----------------------------
            const pais = document.getElementById('pais');
            const estado = document.getElementById('estado');
            const cidade = document.getElementById('cidade');

            if (pais && estado && cidade) {
                pais.addEventListener('change', function() {
                    const urlEstados = "{{ route('Platform.api.estados', ['pais' => '__ID__']) }}".replace('__ID__',
                        this.value);
                    fetch(urlEstados)
                        .then(r => r.json())
                        .then(data => {
                            estado.innerHTML = '<option value="">Selecione...</option>';
                            data.forEach(e => estado.innerHTML +=
                                `<option value="${e.id_estado}">${e.nome_estado}</option>`);
                            cidade.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                        });
                });

                estado.addEventListener('change', function() {
                    const urlCidades = "{{ route('Platform.api.cidades', ['estado' => '__ID__']) }}".replace('__ID__',
                        this.value);
                    fetch(urlCidades)
                        .then(r => r.json())
                        .then(data => {
                            cidade.innerHTML = '<option value="">Selecione...</option>';
                            data.forEach(c => cidade.innerHTML +=
                                `<option value="${c.id_cidade}">${c.nome_cidade}</option>`);
                        });
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                // Se houver país selecionado ao carregar a página, dispara a mudança automaticamente
                if (pais && pais.value) {
                    const urlEstados = "{{ route('Platform.api.estados', ['pais' => '__ID__']) }}".replace('__ID__',
                        pais.value);
                    fetch(urlEstados)
                        .then(r => r.json())
                        .then(data => {
                            estado.innerHTML = '<option value="">Selecione...</option>';
                            data.forEach(e => estado.innerHTML +=
                                `<option value="${e.id_estado}">${e.nome_estado}</option>`);
                            cidade.innerHTML = '<option value="">Selecione o estado primeiro</option>';
                        });
                }
            });

            // -----------------------------
            // 🧾 CPF / CNPJ - select e busca
            // -----------------------------
            $(document).ready(function() {
                const $tipo = $('#tipoDocumento');
                const $document = $('#document');
                const $btnBuscar = $('#btnBuscarCnpj');

                function aplicarMascara() {
                    let tipo = $tipo.val();
                    $document.val('');

                    if (tipo === 'cpf') {
                        $btnBuscar.hide();
                        $document.attr('placeholder', 'Digite o CPF');
                        $document.attr('maxlength', 14);
                    } else {
                        $btnBuscar.show();
                        $document.attr('placeholder', 'Digite o CNPJ');
                        $document.attr('maxlength', 18);
                    }
                }

                // Aplica máscara dinamicamente enquanto digita
                $document.on('input', function() {
                    let valor = $(this).val().replace(/\D/g, '');
                    let tipo = $tipo.val();

                    if (tipo === 'cpf') {
                        if (valor.length > 11) valor = valor.substring(0, 11);
                        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    } else {
                        if (valor.length > 14) valor = valor.substring(0, 14);
                        valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
                        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                        valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
                        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                    }

                    $(this).val(valor);
                });

                // Evento ao mudar tipo (cpf/cnpj)
                $tipo.on('change', aplicarMascara);
                aplicarMascara();

                // Botão buscar CNPJ
                $btnBuscar.on('click', function() {
                    // ... (keep existing code)
                });

                // -----------------------------
                // 💎 Planos vs Rede
                // -----------------------------
                const $networkSelect = $('#network_id');
                const $planSelect = $('#plan_id');
                const $planOptions = $planSelect.find('option');

                function filterPlans() {
                    const hasNetwork = $networkSelect.val() !== "";
                    const targetCategory = hasNetwork ? 'contractual' : ['commercial', 'sandbox'];
                    
                    $planSelect.val(""); // Reset selection
                    
                    $planOptions.each(function() {
                        const category = $(this).data('category');
                        if (!category) return; // Skip "Select..." option

                        const isMatch = Array.isArray(targetCategory) 
                            ? targetCategory.includes(category) 
                            : category === targetCategory;

                        $(this).toggle(isMatch);
                    });

                    if (hasNetwork) {
                        $('#plan_help').text('Listando apenas planos contratuais para rede.');
                    } else {
                        $('#plan_help').text('Listando planos comerciais e sandbox.');
                    }
                }

                $networkSelect.on('change', filterPlans);
                filterPlans(); // Initial filter
            });
        </script>
    @endpush
    @include('layouts.freedash.footer')
@endsection
