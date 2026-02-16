@extends('layouts.freedash.app')
@section('title', 'Listar Layouts de Email')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Layouts de Email</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Layouts de Email</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                {{-- ✅ Alertas de sucesso --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Lista de Layouts</h4>
                    @if($layouts->isNotEmpty())
                        <a href="{{ route('Platform.email-layouts.preview', $layouts->first()->id) }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-eye me-2"></i>Visualizar Preview
                        </a>
                    @endif
                </div>

                <div class="table-responsive">
                    <table id="layouts_table" class="table table-striped table-bordered text-nowrap align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Layout</th>
                                <th>Cores</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($layouts as $layout)
                                <tr>
                                    <td>
                                        <strong>{{ $layout->display_name }}</strong><br>
                                        <small class="text-muted">{{ $layout->name }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge" style="background-color: {{ $layout->primary_color }}; color: white;">
                                                Primária
                                            </span>
                                            <span class="badge" style="background-color: {{ $layout->secondary_color }}; color: white;">
                                                Secundária
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if ($layout->is_active)
                                            <span class="badge bg-success">Ativo</span>
                                        @else
                                            <span class="badge bg-secondary">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a title="Editar" href="{{ route('Platform.email-layouts.edit', $layout->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a title="Preview" href="{{ route('Platform.email-layouts.preview', $layout->id) }}"
                                            class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Nenhum layout encontrado. Um layout padrão será criado automaticamente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#layouts_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush

