@extends('layouts.network-admin')

@section('title', 'Configurações')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-settings"></i>
        </span> Configurações da Rede
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Configurações</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-information-outline me-2 text-primary"></i>Informações Gerais</h4>
                <form action="{{ route('network.settings.update') }}" method="POST" enctype="multipart/form-data" class="forms-sample">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Nome da Rede <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control border-primary-light @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $network->name) }}" 
                                       placeholder="Ex: Rede de Clínicas Saúde Total"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Slug (Subdomínio) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" 
                                           name="slug" 
                                           class="form-control border-primary-light @error('slug') is-invalid @enderror" 
                                           value="{{ old('slug', $network->slug) }}" 
                                           required
                                           pattern="[a-z0-9-]+">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-light border-primary-light">.allsync.com.br</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block">Identificador único da rede na plataforma.</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <div class="form-check form-check-flat form-check-primary">
                            <label class="form-check-label">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $network->is_active) ? 'checked' : '' }}>
                                Rede ativa e visível para as clínicas
                                <i class="input-helper"></i>
                            </label>
                        </div>
                    </div>

                    <hr class="my-5">

                    <h4 class="card-title mb-4"><i class="mdi mdi-palette me-2 text-primary"></i>Identidade Visual e Pública</h4>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Descrição Curta</label>
                        <textarea name="public_description" 
                                  class="form-control border-primary-light" 
                                  rows="3"
                                  placeholder="Uma breve apresentação sobre a rede..."
                                  maxlength="2000">{{ old('public_description', $settings['public_description'] ?? '') }}</textarea>
                        <small class="text-muted d-block mt-2">Aparece nos resultados de busca e resumos da rede.</small>
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-weight-bold">Sobre a Rede (Página Pública)</label>
                        <textarea name="public_text" 
                                  class="form-control border-primary-light" 
                                  placeholder="Descreva detalhadamente os diferenciais da sua rede..."
                                  rows="5">{{ old('public_text', $settings['public_text'] ?? '') }}</textarea>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Cor Primária</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" 
                                           name="primary_color" 
                                           class="form-control form-control-color me-3 border-0" 
                                           style="width: 60px; height: 45px; padding: 2px;"
                                           value="{{ old('primary_color', $settings['primary_color'] ?? '#b66dff') }}">
                                    <span class="text-muted small">Usada em botões e destaques principais.</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Cor Secundária</label>
                                <div class="d-flex align-items-center">
                                    <input type="color" 
                                           name="secondary_color" 
                                           class="form-control form-control-color me-3 border-0" 
                                           style="width: 60px; height: 45px; padding: 2px;"
                                           value="{{ old('secondary_color', $settings['secondary_color'] ?? '#a347ff') }}">
                                    <span class="text-muted small">Usada em elementos complementares.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-5">
                        <label class="font-weight-bold">Logotipo da Rede</label>
                        <div class="dropify-wrapper border-primary-light rounded p-4 text-center bg-light">
                            <input type="file" 
                                   name="logo" 
                                   class="form-control border-primary-light @error('logo') is-invalid @enderror" 
                                   accept="image/*">
                            @if(isset($settings['logo_path']))
                                <div class="mt-3">
                                    <p class="small text-muted mb-2">Logo atual:</p>
                                    <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                        </div>
                        @error('logo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end mt-5 border-top pt-4">
                        <a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}" class="btn btn-light btn-lg me-3 px-5">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-gradient-primary btn-lg px-5 btn-icon-text">
                            <i class="mdi mdi-content-save btn-icon-prepend"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-group label {
        margin-bottom: 0.5rem;
        color: #343a40;
    }
    .border-primary-light {
        border-color: #ebedf2;
    }
    .input-group-text {
        font-size: 0.875rem;
    }
    .btn-gradient-primary {
        background: linear-gradient(to right, #da8cff, #9a55ff);
        border: 0;
        color: white;
    }
</style>
@endpush

