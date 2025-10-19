@extends('layouts.freedash.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-12">

            {{-- Mensagens --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-map text-primary me-2"></i> Catálogo de Estados
                    </h4>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                        <i class="fas fa-plus me-1"></i> Novo Estado
                    </button>
                </div>

                <div class="card-body">
                    {{-- 🔹 Filtro de País --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">País</label>
                            <select id="pais" class="form-select">
                                <option value="">Selecione o país...</option>
                                @foreach ($paises as $pais)
                                    <option value="{{ $pais->id_pais }}">{{ $pais->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 🔹 Tabela --}}
                    <div class="table-responsive">
                        <table id="dt-estados" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>UF</th>
                                    <th>País</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 🔹 Modal Criar --}}
            <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-plus me-2"></i> Novo Estado</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('Platform.estados.store') }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_estado" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF *</label>
                                    <input type="text" name="uf" maxlength="2" class="form-control text-uppercase" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">País *</label>
                                    <select name="pais_id" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($paises as $pais)
                                            <option value="{{ $pais->id_pais }}">{{ $pais->nome }}</option>
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

            {{-- 🔹 Modal Editar --}}
            <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Estado</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formEdit" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_estado" id="edit-nome" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF *</label>
                                    <input type="text" name="uf" id="edit-uf" maxlength="2" class="form-control text-uppercase" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">País *</label>
                                    <select name="pais_id" id="edit-pais" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($paises as $pais)
                                            <option value="{{ $pais->id_pais }}">{{ $pais->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i> Atualizar
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
$(function() {
    const langUrl = "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}";
    const routeEstados = "{{ route('Platform.api.estados', ['pais' => '__PAIS__']) }}";

    let table;

    // 🔹 Quando mudar o país
    $('#pais').on('change', function () {
        const paisId = $(this).val();
        if (!paisId) {
            if (table) {
                table.clear().draw();
            }
            return;
        }

        const url = routeEstados.replace('__PAIS__', paisId);

        // Destroi e recria a tabela
        if ($.fn.DataTable.isDataTable('#dt-estados')) {
            table.destroy();
            $('#dt-estados tbody').empty();
        }

        table = $('#dt-estados').DataTable({
            ajax: {
                url: url,
                dataSrc: ''
            },
            columns: [
                { data: 'id_estado' },
                { data: 'nome_estado' },
                { data: 'uf' },
                { data: 'pais_nome' },
                {
                    data: null,
                    className: 'text-end',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <a href="/Platform/estados/${row.id_estado}" class="btn btn-sm btn-outline-info me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-warning me-1 btn-edit"
                                    data-id="${row.id_estado}"
                                    data-nome="${row.nome_estado}"
                                    data-uf="${row.uf}"
                                    data-pais="${row.pais_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="/Platform/estados/${row.id_estado}" method="POST" class="d-inline" onsubmit="return confirm('Excluir estado?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>`;
                    }
                }
            ],
            responsive: true,
            pageLength: 10,
            language: { url: langUrl }
        });
    });

    // 🔹 Preenche modal editar
    $(document).on('click', '.btn-edit', function () {
        $('#edit-nome').val($(this).data('nome'));
        $('#edit-uf').val($(this).data('uf'));
        $('#edit-pais').val($(this).data('pais'));
        $('#formEdit').attr('action', `/Platform/estados/${$(this).data('id')}`);
        $('#modalEdit').modal('show');
    });
});
</script>
@endpush
