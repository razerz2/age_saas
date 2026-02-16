@extends('layouts.freedash.app')
@section('title', 'Listar Paises')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-md-12">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-flag text-primary me-2"></i> Catálogo de Países
                        </h4>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                            <i class="fas fa-plus me-1"></i> Novo País
                        </button>
                    </div>

                    <div class="card-body">
                        <table id="dt-paises" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Sigla (2)</th>
                                    <th>Sigla (3)</th>
                                    <th>Código</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paises as $pais)
                                    <tr>
                                        <td>{{ $pais->id_pais }}</td>
                                        <td>{{ $pais->nome }}</td>
                                        <td>{{ $pais->sigla2 ?? '-' }}</td>
                                        <td>{{ $pais->sigla3 ?? '-' }}</td>
                                        <td>{{ $pais->codigo ?? '-' }}</td>
                                        <td class="text-end">
                                            <a title="Visualizar" href="{{ route('Platform.paises.show', $pais->id_pais) }}"
                                                class="btn btn-sm btn-outline-info me-1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button title="Editar" class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal"
                                                data-bs-target="#modalEdit{{ $pais->id_pais }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('Platform.paises.destroy', $pais->id_pais) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirmSubmit(event, 'Tem certeza que deseja excluir este país? Esta ação não pode ser desfeita.', 'Confirmar Exclusão')">
                                                @csrf @method('DELETE')
                                                <button title="Exclusão" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    {{-- Modal Edit --}}
                                    <div class="modal fade" id="modalEdit{{ $pais->id_pais }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar País
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST"
                                                    action="{{ route('Platform.paises.update', $pais->id_pais) }}">
                                                    @csrf @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nome *</label>
                                                            <input type="text" name="nome" class="form-control"
                                                                value="{{ $pais->nome }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Sigla 2</label>
                                                            <input type="text" name="sigla2" class="form-control"
                                                                value="{{ $pais->sigla2 }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Sigla 3</label>
                                                            <input type="text" name="sigla3" class="form-control"
                                                                value="{{ $pais->sigla3 }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Código</label>
                                                            <input type="text" name="codigo" class="form-control"
                                                                value="{{ $pais->codigo }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light"
                                                            data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-save me-1"></i> Salvar
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Modal Create --}}
                <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Novo País</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('Platform.paises.store') }}">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nome *</label>
                                        <input type="text" name="nome" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sigla 2</label>
                                        <input type="text" name="sigla2" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Sigla 3</label>
                                        <input type="text" name="sigla3" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Código</label>
                                        <input type="text" name="codigo" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light"
                                        data-bs-dismiss="modal">Cancelar</button>
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

@push('scripts')
    <script>
        // Inicializa APENAS a sua tabela 
        $(function() {
            $('#dt-paises').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
