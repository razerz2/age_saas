@extends('layouts.freedash.app')
@section('title', 'Visualizar Cidades')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-city text-primary me-2"></i> Detalhes da Cidade
                    </h4>
                    <a href="{{ route('Platform.cidades.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Nome:</label>
                            <p class="mb-0">{{ $cidade->nome_cidade }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">UF:</label>
                            <p class="mb-0">{{ $cidade->uf ?? $cidade->estado->uf ?? '-' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">Estado:</label>
                            <p class="mb-0">{{ $cidade->estado->nome_estado ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">País:</label>
                            <p class="mb-0">{{ $cidade->estado->pais->nome ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Criado em:</label>
                            <p class="mb-0">{{ $cidade->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEdit">
                            <i class="fas fa-edit me-2"></i> Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Modal Editar --}}
            <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i> Editar Cidade
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('Platform.cidades.update', $cidade->id_cidade) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_cidade" class="form-control" value="{{ $cidade->nome_cidade }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF</label>
                                    <input type="text" name="uf" maxlength="2" class="form-control text-uppercase" value="{{ $cidade->uf }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado *</label>
                                    <select name="estado_id" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id_estado }}" @selected($cidade->estado_id == $estado->id_estado)>
                                                {{ $estado->nome_estado }} ({{ $estado->uf }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Salvar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('layouts.freedash.footer')
@endsection
