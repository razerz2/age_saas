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
                            <i class="fas fa-building text-primary me-2"></i> Editar Tenant
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

                        @php
                            $activeSubscription = $tenant->activeSubscriptionRelation;
                            $regularizationQuery = array_filter([
                                'tenant_id' => $tenant->id,
                                'plan_id' => $tenant->preferredRegularizationPlanId(),
                            ]);
                        @endphp

                        <div class="alert {{ $tenant->isEligibleForAccess() ? 'alert-success' : 'alert-warning' }}">
                            <div class="d-flex flex-column">
                                <strong>SituaÃ§Ã£o comercial: {{ $tenant->commercialAccessSummaryLabel() }}</strong>
                                <span class="small mt-1">
                                    Motivo atual: {{ $tenant->commercialAccessStatusLabel() }}
                                </span>
                                <span class="small mt-1">
                                    Plano por assinatura: {{ $activeSubscription?->plan?->name ?? '-' }}
                                    @if ($activeSubscription?->plan)
                                        ({{ $activeSubscription->plan->planTypeLabel() }} /
                                        {{ $activeSubscription->plan->landingVisibilityLabel() }})
                                    @endif
                                </span>
                                @if (! $tenant->isEligibleForAccess() && in_array('subscriptions', auth()->user()->modules ?? []))
                                    <div class="mt-2">
                                        <a href="{{ route('Platform.subscriptions.create', $regularizationQuery) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-file-signature me-1"></i> Regularizar agora
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('Platform.tenants.update', $tenant->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Razão Social *</label>
                                    <input type="text" name="legal_name" class="form-control"
                                        value="{{ old('legal_name', $tenant->legal_name) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nome Fantasia</label>
                                    <input type="text" name="trade_name" class="form-control"
                                        value="{{ old('trade_name', $tenant->trade_name) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="document" class="form-control"
                                        value="{{ old('document', $tenant->document) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Subdomínio *</label>
                                    <input type="text" name="subdomain" class="form-control"
                                        value="{{ old('subdomain', $tenant->subdomain) }}" required>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $tenant->email) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Telefone</label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $tenant->phone) }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select">
                                        <option value="trial" @selected($tenant->status == 'trial')>Trial</option>
                                        <option value="active" @selected($tenant->status == 'active')>Ativo</option>
                                        <option value="suspended" @selected($tenant->status == 'suspended')>Suspenso</option>
                                        <option value="cancelled" @selected($tenant->status == 'cancelled')>Cancelado</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Trial até</label>
                                    <input type="date" name="trial_ends_at" class="form-control"
                                        value="{{ old('trial_ends_at', $tenant->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d') : '') }}">
                                </div>

                                {{-- Localização --}}
                                <div class="col-12 mt-4">
                                    <h5 class="text-primary fw-bold mb-2">
                                        <i class="fas fa-map-marker-alt me-2"></i> Localização da Empresa
                                    </h5>
                                </div>

                                @php
                                    $loc = $localizacao ?? null;
                                @endphp

                                <div class="col-md-6">
                                    <label class="form-label">Estado</label>
                                    <select id="estado" name="estado_id" class="form-select">
                                        @if ($estados->isNotEmpty())
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id_estado }}" @selected($loc && $loc->estado_id == $estado->id_estado)>
                                                    {{ $estado->nome_estado }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">Selecione o estado</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Cidade</label>
                                    <select id="cidade" name="cidade_id" class="form-select">
                                        @if ($cidades->isNotEmpty())
                                            @foreach ($cidades as $cidade)
                                                <option value="{{ $cidade->id_cidade }}" @selected($loc && $loc->cidade_id == $cidade->id_cidade)>
                                                    {{ $cidade->nome_cidade }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="">Selecione o estado primeiro</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" id="tenant_endereco" name="endereco" class="form-control"
                                        value="{{ old('endereco', $loc->endereco ?? '') }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Número</label>
                                    <input type="text" name="n_endereco" class="form-control"
                                        value="{{ old('n_endereco', $loc->n_endereco ?? '') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Complemento</label>
                                    <input type="text" name="complemento" class="form-control"
                                        value="{{ old('complemento', $loc->complemento ?? '') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" id="tenant_bairro" name="bairro" class="form-control"
                                        value="{{ old('bairro', $loc->bairro ?? '') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">CEP</label>
                                    <input type="text" id="tenant_cep" name="cep" class="form-control"
                                        value="{{ old('cep', $loc->cep ?? '') }}">
                                    <small id="tenantCepFeedback" class="form-text text-muted d-block mt-1"></small>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Atualizar Tenant
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
                loadStatesOnInit: false
            });
        </script>
    @endpush

    @include('layouts.freedash.footer')
@endsection
