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
                        {{-- Exibe erros de validação --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <strong>Ops!</strong> Verifique os campos abaixo:
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
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
                                    <label class="form-label">Documento</label>
                                    <input type="text" name="document" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Subdomínio *</label>
                                    <input type="text" name="subdomain" class="form-control" required>
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
                                    <select name="status" class="form-select">
                                        <option value="trial">Trial</option>
                                        <option value="active">Ativo</option>
                                        <option value="suspended">Suspenso</option>
                                        <option value="cancelled">Cancelado</option>
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

                                <div class="col-md-4">
                                    <label class="form-label">País</label>
                                    <select id="pais" name="pais_id" class="form-select">
                                        @foreach ($paises as $pais)
                                            <option value="{{ $pais->id_pais }}"
                                                {{ $defaultCountryId == $pais->id_pais ? 'selected' : '' }}>
                                                {{ $pais->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select id="estado" name="estado_id" class="form-select">
                                        <option value="">Selecione o país primeiro</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
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

                                {{-- Banco de Dados --}}
                                <div class="col-12 mt-4">
                                    <h5 class="text-primary fw-bold mb-2">
                                        <i class="fas fa-database me-2"></i> Configuração do Banco de Dados
                                    </h5>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">DB Host *</label>
                                    <input type="text" name="db_host" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">DB Name *</label>
                                    <input type="text" name="db_name" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">DB User *</label>
                                    <input type="text" name="db_username" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">DB Password *</label>
                                    <input type="password" name="db_password" class="form-control" required>
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
        </script>
    @endpush

    @include('layouts.freedash.footer')
@endsection
