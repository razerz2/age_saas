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
                        {{-- Alertas de sucesso --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Alertas de aviso --}}
                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Exibição de erros de validação --}}
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

                        <div class="alert alert-info border-start border-info border-4">
                            <i class="fas fa-info-circle me-2"></i>
                            O cadastro técnico pode ser concluído sem plano e assinatura.
                            Nestes casos, a tenant será criada, mas ficará com acesso bloqueado até regularização comercial.
                        </div>

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
                                    <label class="form-label">Plano (opcional)</label>
                                    <select name="plan_id" id="plan_id" class="form-select">
                                        <option value="">Sem plano no momento (ficará bloqueada)</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" data-category="{{ $plan->category }}"
                                                @selected(old('plan_id') == $plan->id)>
                                                {{ $plan->name }} ({{ $plan->category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="plan_help">Opcional: selecione um plano para criar assinatura automaticamente quando aplicável.</small>
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
                                    <input type="text" id="tenant_endereco" name="endereco" class="form-control">
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
                                    <input type="text" id="tenant_bairro" name="bairro" class="form-control">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" id="tenant_cep" name="cep" class="form-control">
                                    <small id="tenantCepFeedback" class="form-text text-muted d-block mt-1"></small>
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
        @include('platform.tenants.partials.address-lookup-script')
        <script>
            initTenantAddressLookup({
                stateSelector: '#estado',
                citySelector: '#cidade',
                addressSelector: '#tenant_endereco',
                neighborhoodSelector: '#tenant_bairro',
                cepSelector: '#tenant_cep',
                feedbackSelector: '#tenantCepFeedback',
                statesUrl: "{{ route('Platform.api.estados') }}",
                citiesUrlTemplate: "{{ route('Platform.api.cidades', ['estado' => '__ID__']) }}",
                zipcodeUrlTemplate: "{{ route('api.zipcode', ['zipcode' => '__CEP__']) }}",
                loadStatesOnInit: true
            });

            // -----------------------------
            // CPF / CNPJ - select e busca
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

            });
        </script>
    @endpush
    @include('layouts.freedash.footer')
@endsection

