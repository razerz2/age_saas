@extends('layouts.network-admin')

@section('title', 'Configurações')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-settings"></i>
        </span> Configurações da Rede
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('network.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nome da Rede <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $network->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Slug (Subdomínio) <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="slug" 
                                       class="form-control @error('slug') is-invalid @enderror" 
                                       value="{{ old('slug', $network->slug) }}" 
                                       required
                                       pattern="[a-z0-9-]+">
                                <small class="form-text text-muted">Usado no subdomínio (ex: {{ $network->slug }}.allsync.com.br)</small>
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $network->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Rede ativa
                            </label>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Identidade Pública</h5>

                    <div class="mb-3">
                        <label class="form-label">Descrição Pública</label>
                        <textarea name="public_description" 
                                  class="form-control" 
                                  rows="3"
                                  maxlength="2000">{{ old('public_description', $settings['public_description'] ?? '') }}</textarea>
                        <small class="form-text text-muted">Descrição que aparece na página pública da rede</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Texto da Página Pública</label>
                        <textarea name="public_text" 
                                  class="form-control" 
                                  rows="5">{{ old('public_text', $settings['public_text'] ?? '') }}</textarea>
                        <small class="form-text text-muted">Texto adicional da página pública</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Cor Primária</label>
                            <input type="color" 
                                   name="primary_color" 
                                   class="form-control form-control-color" 
                                   value="{{ old('primary_color', $settings['primary_color'] ?? '#667eea') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cor Secundária</label>
                            <input type="color" 
                                   name="secondary_color" 
                                   class="form-control form-control-color" 
                                   value="{{ old('secondary_color', $settings['secondary_color'] ?? '#764ba2') }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Logo</label>
                        <input type="file" 
                               name="logo" 
                               class="form-control @error('logo') is-invalid @enderror" 
                               accept="image/*">
                        @if(isset($settings['logo_path']))
                            <small class="form-text text-muted d-block mt-2">
                                Logo atual: 
                                <a href="{{ asset('storage/' . $settings['logo_path']) }}" target="_blank">Ver logo</a>
                            </small>
                        @endif
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('network.dashboard') }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i> Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

