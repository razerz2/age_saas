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
                            <i class="fas fa-city text-primary me-2"></i> Catálogo de Cidades
                        </h4>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
                            <i class="fas fa-plus me-1"></i> Nova Cidade
                        </button>
                    </div>

                    <div class="card-body">
                        {{-- 🔹 Filtros --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">País</label>
                                <select id="pais" class="form-select">
                                    <option value="">Selecione o país...</option>
                                    @foreach ($paises as $pais)
                                        <option value="{{ $pais->id_pais }}">{{ $pais->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Estado</label>
                                <select id="estado" class="form-select" disabled>
                                    <option value="">Selecione o estado...</option>
                                </select>
                            </div>
                        </div>

                        {{-- 🔹 DataTable --}}
                        <div class="table-responsive">
                            <table id="dt-cidades" class="table table-striped table-hover align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>UF</th>
                                        <th>Estado</th>
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
                                        <input type="text" name="uf" maxlength="2"
                                            class="form-control text-uppercase">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Estado *</label>
                                        <select name="estado_id" class="form-select" required>
                                            <option value="">Selecione...</option>
                                            @foreach ($estados as $estado)
                                                <option value="{{ $estado->id_estado }}">{{ $estado->nome_estado }}
                                                    ({{ $estado->uf }})
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

@push('scripts')
    <script>
        $(function() {
            const langUrl = "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}";

            // Rotas nomeadas Laravel (substituímos IDs depois)
            const routeEstados = "{{ route('Platform.api.estados', ['pais' => '__PAIS__']) }}";
            const routeCidades = "{{ route('Platform.api.cidades', ['estado' => '__ESTADO__']) }}";

            let table; // armazenar instância do datatable

            // 🔹 Carrega estados ao selecionar país
            $('#pais').on('change', function() {
                const paisId = $(this).val();
                const estadoSelect = $('#estado');
                estadoSelect.prop('disabled', true).html('<option>Carregando...</option>');

                if (!paisId) {
                    estadoSelect.html('<option>Selecione o país primeiro</option>');
                    return;
                }

                const urlEstados = routeEstados.replace('__PAIS__', paisId);

                $.getJSON(urlEstados, function(estados) {
                    let options = '<option value="">Selecione o estado...</option>';
                    $.each(estados, function(i, e) {
                        options +=
                            `<option value="${e.id_estado}">${e.nome_estado} (${e.uf})</option>`;
                    });
                    estadoSelect.html(options).prop('disabled', false);
                }).fail(function() {
                    estadoSelect.html('<option>Erro ao carregar estados</option>');
                });
            });

            // 🔹 Carrega cidades ao selecionar estado
            $('#estado').on('change', function() {
                const estadoId = $(this).val();
                if (!estadoId) return;

                const urlCidades = routeCidades.replace('__ESTADO__', estadoId);

                // se já existe datatable, destrói e recria
                if ($.fn.DataTable.isDataTable('#dt-cidades')) {
                    table.destroy();
                    $('#dt-cidades tbody').empty();
                }

                table = $('#dt-cidades').DataTable({
                    ajax: {
                        url: urlCidades,
                        dataSrc: ''
                    },
                    columns: [{
                            data: 'id_cidade'
                        },
                        {
                            data: 'nome_cidade'
                        },
                        {
                            data: 'uf'
                        },
                        {
                            data: 'nome_estado'
                        }, // 👈 corrigido aqui
                        {
                            data: null,
                            className: 'text-end',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
            <a title="Visualizar" href="/Platform/cidades/${row.id_cidade}" class="btn btn-sm btn-outline-info me-1">
                <i class="fas fa-eye"></i>
            </a>
            <button title="Editar" class="btn btn-sm btn-outline-warning me-1 btn-edit" 
                    data-id="${row.id_cidade}" 
                    data-nome="${row.nome_cidade}"
                    data-uf="${row.uf ?? ''}"
                    data-estado="${row.estado_id}">
                <i class="fas fa-edit"></i>
            </button>
            <form action="/Platform/cidades/${row.id_cidade}" method="POST" class="d-inline" onsubmit="return confirm('Excluir cidade?')">
                @csrf
                @method('DELETE')
                <button title="Exclusão" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </form>`;
                            }
                        }

                    ],
                    responsive: true,
                    pageLength: 10,
                    language: {
                        url: langUrl
                    }
                });
            });
        });
        // --- Editar cidade ---
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const nome = $(this).data('nome');
            const uf = $(this).data('uf');
            const estado = $(this).data('estado');

            $('#edit-nome').val(nome);
            $('#edit-uf').val(uf);
            $('#edit-estado').val(estado);
            $('#formEdit').attr('action', `/Platform/cidades/${id}`);

            $('#modalEdit').modal('show');
        });
    </script>
@endpush
