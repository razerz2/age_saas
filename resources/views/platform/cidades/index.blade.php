@extends('layouts.freedash.app')
@section('title', 'Listar Cidades')

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
                        <i class="fas fa-city text-primary me-2"></i> Catálogo de Cidades
                    </h4>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                        <i class="fas fa-plus me-1"></i> Nova Cidade
                    </button>
                </div>

                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Estado</label>
                            <select id="estado" class="form-select">
                                <option value="">Selecione o estado...</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="dt-cidades" class="table table-striped table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>UF</th>
                                    <th>Código IBGE</th>
                                    <th>Estado</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-plus me-2"></i> Nova Cidade
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="{{ route('Platform.cidades.store') }}">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_cidade" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF</label>
                                    <input type="text" name="uf" maxlength="2" class="form-control text-uppercase">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Código IBGE</label>
                                    <input type="number" name="ibge_id" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado *</label>
                                    <select name="estado_id" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id_estado }}">{{ $estado->nome_estado }} ({{ $estado->uf }})</option>
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

            <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Cidade</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="formEdit" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Nome *</label>
                                    <input type="text" name="nome_cidade" id="edit-nome" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">UF</label>
                                    <input type="text" name="uf" id="edit-uf" maxlength="2" class="form-control text-uppercase">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Código IBGE</label>
                                    <input type="number" name="ibge_id" id="edit-ibge-id" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Estado *</label>
                                    <select name="estado_id" id="edit-estado" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        @foreach ($estados as $estado)
                                            <option value="{{ $estado->id_estado }}">{{ $estado->nome_estado }} ({{ $estado->uf }})</option>
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
    const routeEstados = "{{ route('Platform.api.estados') }}";
    const routeCidades = "{{ route('Platform.api.cidades', ['estado' => '__ESTADO__']) }}";
    const estadoSelect = $('#estado');
    let table = null;

    function loadStates() {
        $.getJSON(routeEstados, function(estados) {
            let options = '<option value="">Selecione o estado...</option>';
            $.each(estados, function(index, estado) {
                options += `<option value="${estado.id_estado}">${estado.nome_estado} (${estado.uf})</option>`;
            });
            estadoSelect.html(options);
        }).fail(function() {
            estadoSelect.html('<option value="">Erro ao carregar estados</option>');
        });
    }

    function mountCitiesTable(stateId) {
        const urlCidades = routeCidades.replace('__ESTADO__', stateId);

        if ($.fn.DataTable.isDataTable('#dt-cidades')) {
            table.destroy();
            $('#dt-cidades tbody').empty();
        }

        table = $('#dt-cidades').DataTable({
            ajax: {
                url: urlCidades,
                dataSrc: ''
            },
            columns: [
                { data: 'id_cidade' },
                { data: 'nome_cidade' },
                { data: 'uf' },
                { data: 'ibge_id', render: (data) => data ?? '-' },
                { data: 'nome_estado' },
                {
                    data: null,
                    className: 'text-end',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `
                            <a title="Visualizar" href="/Platform/cidades/${row.id_cidade}" class="btn btn-sm btn-outline-info me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button title="Editar" class="btn btn-sm btn-outline-warning me-1 btn-edit"
                                    data-id="${row.id_cidade}"
                                    data-nome="${row.nome_cidade}"
                                    data-uf="${row.uf ?? ''}"
                                    data-ibge-id="${row.ibge_id ?? ''}"
                                    data-estado="${row.estado_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="/Platform/cidades/${row.id_cidade}" method="POST" class="d-inline" onsubmit="event.preventDefault(); showConfirm('Deseja realmente excluir esta cidade? Esta ação não pode ser desfeita.', 'Confirmar Exclusão').then(confirmed => { if(confirmed) event.target.submit(); }); return false;">
                                @csrf
                                @method('DELETE')
                                <button title="Excluir" class="btn btn-sm btn-outline-danger">
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
    }

    estadoSelect.on('change', function() {
        const stateId = $(this).val();
        if (!stateId) {
            if (table) {
                table.clear().draw();
            }
            return;
        }

        mountCitiesTable(stateId);
    });

    $(document).on('click', '.btn-edit', function () {
        $('#edit-nome').val($(this).data('nome'));
        $('#edit-uf').val($(this).data('uf'));
        $('#edit-ibge-id').val($(this).data('ibge-id'));
        $('#edit-estado').val($(this).data('estado'));
        $('#formEdit').attr('action', `/Platform/cidades/${$(this).data('id')}`);
        $('#modalEdit').modal('show');
    });

    loadStates();
});
</script>
@endpush
