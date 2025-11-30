@extends('layouts.connect_plus.app')

@section('title', 'Criar Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Criar Médico </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.doctors.index') }}">Médicos</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Criar</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h4 class="card-title mb-1">
                                <i class="mdi mdi-account-plus text-primary me-2"></i>
                                Novo Médico
                            </h4>
                            <p class="card-description mb-0 text-muted">Preencha os dados abaixo para cadastrar um novo médico</p>
                        </div>
                    </div>

                    <form class="forms-sample" action="{{ route('tenant.doctors.store') }}" method="POST">
                        @csrf

                        {{-- Seção: Informações Básicas --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Informações Básicas
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-account me-1"></i>
                                            Usuário <span class="text-danger">*</span>
                                        </label>
                                        <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                            <option value="">Selecione um usuário</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Seção: Dados Profissionais --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-briefcase-outline me-2"></i>
                                Dados Profissionais
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-card-account-details me-1"></i>
                                            Número CRM, CRP ou CRO
                                        </label>
                                        <input type="text" class="form-control @error('crm_number') is-invalid @enderror" 
                                               name="crm_number" value="{{ old('crm_number') }}" 
                                               maxlength="50" placeholder="Ex: 123456">
                                        @error('crm_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-map-marker me-1"></i>
                                            Estado CRM, CRP ou CRO
                                        </label>
                                        <input type="text" class="form-control @error('crm_state') is-invalid @enderror" 
                                               name="crm_state" value="{{ old('crm_state') }}" 
                                               maxlength="2" placeholder="Ex: SP">
                                        <small class="form-text text-muted">Digite a sigla do estado (2 letras)</small>
                                        @error('crm_state')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="fw-semibold">
                                            <i class="mdi mdi-pen me-1"></i>
                                            Assinatura
                                        </label>
                                        <textarea class="form-control @error('signature') is-invalid @enderror" 
                                                  name="signature" rows="4" 
                                                  placeholder="Digite a assinatura do médico (opcional)">{{ old('signature') }}</textarea>
                                        @error('signature')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php
                            $customizationEnabled = tenant_setting('professional.customization_enabled') === 'true';
                        @endphp

                        @if($customizationEnabled)
                            <div class="mt-4 p-3 bg-light border rounded">
                                <h5 class="mb-3">Personalização do Profissional (Opcional)</h5>

                                <div class="mb-3">
                                    <label class="form-label">Tipo do Profissional (Singular)</label>
                                    <input type="text" name="label_singular" class="form-control @error('label_singular') is-invalid @enderror"
                                           placeholder="Ex: Psicólogo, Fisioterapeuta"
                                           value="{{ old('label_singular') }}"
                                           maxlength="60">
                                    @error('label_singular')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo do Profissional (Plural)</label>
                                    <input type="text" name="label_plural" class="form-control @error('label_plural') is-invalid @enderror"
                                           placeholder="Ex: Psicólogos, Fisioterapeutas"
                                           value="{{ old('label_plural') }}"
                                           maxlength="60">
                                    @error('label_plural')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Registro Profissional (Rótulo)</label>
                                    <input type="text" name="registration_label" class="form-control @error('registration_label') is-invalid @enderror"
                                           placeholder="Ex: CRM, CRP, CRO, CREFITO"
                                           value="{{ old('registration_label') }}"
                                           maxlength="40">
                                    @error('registration_label')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Registro Profissional Completo (Valor)</label>
                                    <input type="text" name="registration_value" class="form-control @error('registration_value') is-invalid @enderror"
                                           placeholder="Ex: CRM 55221, CRP 05/19999, CREFITO 123456-F"
                                           value="{{ old('registration_value') }}"
                                           maxlength="100">
                                    @error('registration_value')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Seção: Especialidades --}}
                        <div class="mb-4">
                            <h5 class="mb-3 text-primary">
                                <i class="mdi mdi-stethoscope me-2"></i>
                                Especialidades Médicas
                            </h5>
                            <div class="form-group">
                                <label class="fw-semibold mb-2">
                                    Selecione as especialidades do médico <span class="text-danger">*</span>
                                </label>
                                <div class="row mb-3">
                                    <div class="col-md-8">
                                        <select id="specialty-select" class="form-control @error('specialties') is-invalid @enderror">
                                            <option value="">Selecione uma especialidade</option>
                                            @foreach($specialties as $specialty)
                                                <option value="{{ $specialty->id }}" data-name="{{ $specialty->name }}">{{ $specialty->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex gap-2">
                                            <button type="button" id="add-specialty-btn" class="btn btn-primary flex-fill">
                                                <i class="mdi mdi-plus me-1"></i> Adicionar
                                            </button>
                                            <button type="button" id="clear-specialties-btn" class="btn btn-outline-secondary">
                                                <i class="mdi mdi-delete-sweep"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Área para exibir especialidades selecionadas --}}
                                <div id="selected-specialties" class="border rounded p-3 bg-light" style="min-height: 60px;">
                                    @if(old('specialties'))
                                        @foreach(old('specialties') as $specialtyId)
                                            @php
                                                $specialty = $specialties->firstWhere('id', $specialtyId);
                                            @endphp
                                            @if($specialty)
                                                <span class="badge bg-primary me-2 mb-2 specialty-badge" data-id="{{ $specialty->id }}" 
                                                      style="font-size: 13px; padding: 8px 14px; display: inline-flex; align-items: center; gap: 6px;">
                                                    <i class="mdi mdi-stethoscope"></i>
                                                    {{ $specialty->name }}
                                                    <button type="button" class="btn-close btn-close-white ms-1" 
                                                            style="font-size: 10px; opacity: 0.8;" 
                                                            aria-label="Remover"></button>
                                                </span>
                                            @endif
                                        @endforeach
                                    @else
                                        <p class="text-muted mb-0">
                                            <i class="mdi mdi-information-outline me-1"></i>
                                            Nenhuma especialidade selecionada
                                        </p>
                                    @endif
                                </div>
                                
                                {{-- Campos hidden para enviar os IDs (serão criados dinamicamente pelo JavaScript) --}}
                                <div id="specialties-inputs"></div>
                                
                                @error('specialties')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <a href="{{ route('tenant.doctors.index') }}" class="btn btn-light">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-content-save me-1"></i>
                                Salvar Médico
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@push('styles')
    <link href="{{ asset('css/tenant-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/tenant-doctors.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let selectedSpecialties = [];
        
        // Carregar especialidades já selecionadas (do old ou do banco)
        function loadSelectedSpecialties() {
            @if(old('specialties'))
                selectedSpecialties = @json(old('specialties'));
            @endif
            updateSpecialtiesDisplay();
        }
        
        // Atualizar exibição das especialidades selecionadas
        function updateSpecialtiesDisplay() {
            const container = $('#selected-specialties');
            container.empty();
            
            if (selectedSpecialties.length === 0) {
                container.html('<p class="text-muted mb-0"><i class="mdi mdi-information-outline me-1"></i>Nenhuma especialidade selecionada</p>');
                return;
            }
            
            selectedSpecialties.forEach(function(specialtyId) {
                const option = $('#specialty-select option[value="' + specialtyId + '"]');
                if (option.length) {
                    const name = option.data('name');
                    const badge = $('<span>')
                        .addClass('badge bg-primary me-2 mb-2 specialty-badge')
                        .attr('data-id', specialtyId)
                        .css({
                            'font-size': '13px', 
                            'padding': '8px 14px', 
                            'display': 'inline-flex', 
                            'align-items': 'center', 
                            'gap': '6px'
                        })
                        .html('<i class="mdi mdi-stethoscope"></i>' + name + '<button type="button" class="btn-close btn-close-white ms-1" style="font-size: 10px; opacity: 0.8;" aria-label="Remover"></button>');
                    container.append(badge);
                }
            });
            
            // Atualizar campos hidden
            const inputsContainer = $('#specialties-inputs');
            inputsContainer.empty();
            selectedSpecialties.forEach(function(specialtyId) {
                inputsContainer.append($('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'specialties[]')
                    .val(specialtyId)
                );
            });
        }
        
        // Adicionar especialidade
        $('#add-specialty-btn').on('click', function() {
            const select = $('#specialty-select');
            const specialtyId = select.val();
            
            if (!specialtyId) {
                alert('Por favor, selecione uma especialidade');
                return;
            }
            
            // Verificar se já foi adicionada
            if (selectedSpecialties.includes(specialtyId)) {
                alert('Esta especialidade já foi adicionada');
                return;
            }
            
            selectedSpecialties.push(specialtyId);
            updateSpecialtiesDisplay();
            select.val(''); // Limpar seleção
        });
        
        // Remover especialidade (delegation para elementos dinâmicos)
        $(document).on('click', '.specialty-badge .btn-close', function(e) {
            e.preventDefault();
            const badge = $(this).closest('.specialty-badge');
            const specialtyId = badge.data('id');
            
            selectedSpecialties = selectedSpecialties.filter(function(id) {
                return id !== specialtyId;
            });
            
            updateSpecialtiesDisplay();
        });
        
        // Limpar todas as especialidades
        $('#clear-specialties-btn').on('click', function() {
            if (selectedSpecialties.length === 0) {
                return;
            }
            
            if (confirm('Deseja remover todas as especialidades selecionadas?')) {
                selectedSpecialties = [];
                updateSpecialtiesDisplay();
            }
        });
        
        // Permitir adicionar com Enter no select
        $('#specialty-select').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#add-specialty-btn').click();
            }
        });
        
        // Validação antes de enviar o formulário
        $('form').on('submit', function(e) {
            if (selectedSpecialties.length === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos uma especialidade médica.');
                $('#specialty-select').focus();
                return false;
            }
        });
        
        // Inicializar
        loadSelectedSpecialties();
    });
</script>
@endpush

@endsection
