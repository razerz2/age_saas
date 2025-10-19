@extends('layouts.freedash.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-flag text-primary me-2"></i> Detalhes do País
                    </h4>
                    <a href="{{ route('Platform.paises.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Nome:</label>
                            <p class="mb-0">{{ $pais->nome }}</p>
                        </div>

                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">Sigla (2):</label>
                            <p class="mb-0">{{ $pais->sigla2 ?? '-' }}</p>
                        </div>

                        <div class="col-md-3">
                            <label class="fw-semibold text-muted">Sigla (3):</label>
                            <p class="mb-0">{{ $pais->sigla3 ?? '-' }}</p>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-semibold text-muted">Código:</label>
                            <p class="mb-0">{{ $pais->codigo ?? '-' }}</p>
                        </div>

                        <div class="col-md-8">
                            <label class="fw-semibold text-muted">Data de Criação:</label>
                            <p class="mb-0">{{ $pais->created_at ? $pais->created_at->format('d/m/Y H:i') : '-' }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('Platform.paises.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-list"></i> Lista de Países
                        </a>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditShow">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                    </div>
                </div>
            </div>

            {{-- Modal Editar (na tela de show) --}}
            <div class="modal fade" id="modalEditShow" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-edit me-2"></i> Editar País
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('Platform.paises.update', $pais->id_pais) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome" class="form-control" value="{{ $pais->nome }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sigla 2</label>
                                    <input type="text" name="sigla2" class="form-control" value="{{ $pais->sigla2 }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sigla 3</label>
                                    <input type="text" name="sigla3" class="form-control" value="{{ $pais->sigla3 }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Código</label>
                                    <input type="text" name="codigo" class="form-control" value="{{ $pais->codigo }}">
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
