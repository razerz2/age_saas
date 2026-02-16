@extends('layouts.freedash.app')
@section('title', 'Visualizar Estados')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-map text-primary me-2"></i> Detalhes do Estado
                    </h4>
                    <a href="{{ route('Platform.estados.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Nome:</label>
                            <p class="mb-0">{{ $estado->nome_estado }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">UF:</label>
                            <p class="mb-0">{{ $estado->uf }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">País:</label>
                            <p class="mb-0">{{ $estado->pais->nome ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Criado em:</label>
                            <p class="mb-0">{{ $estado->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
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
                                <i class="fas fa-edit me-2"></i> Editar Estado
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('Platform.estados.update', $estado->id_estado) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_estado" class="form-control" value="{{ $estado->nome_estado }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF *</label>
                                    <input type="text" name="uf" maxlength="2" class="form-control text-uppercase" value="{{ $estado->uf }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">País *</label>
                                    <select name="pais_id" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($paises as $pais)
                                            <option value="{{ $pais->id_pais }}" @selected($estado->pais_id == $pais->id_pais)>
                                                {{ $pais->nome }}
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
