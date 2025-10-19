@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Catálogo de Especialidades Médicas
                </h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Especialidades</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.medical_specialties_catalog.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Nova Especialidade
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Lista de Especialidades</h4>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table id="specialties_table"
                                class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Código CBO</th>
                                        <th>Tipo</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($specialties as $specialty)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $specialty->name }}</td>
                                            <td>{{ $specialty->code ?? '-' }}</td>
                                            <td>
                                                @if ($specialty->type === 'medical_specialty')
                                                    <span class="badge bg-primary">Especialidade Médica</span>
                                                @elseif ($specialty->type === 'health_profession')
                                                    <span class="badge bg-success">Profissão da Saúde</span>
                                                @else
                                                    <span class="badge bg-secondary">Outro</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('Platform.medical_specialties_catalog.show', $specialty->id) }}"
                                                    class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('Platform.medical_specialties_catalog.edit', $specialty->id) }}"
                                                    class="btn btn-sm btn-warning text-white"><i class="fa fa-edit"></i></a>
                                                <form
                                                    action="{{ route('Platform.medical_specialties_catalog.destroy', $specialty->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Deseja realmente excluir esta especialidade?')">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
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
            $('#specialties_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
