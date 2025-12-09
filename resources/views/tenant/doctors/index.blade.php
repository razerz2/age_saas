@extends('layouts.connect_plus.app')

@section('title', 'Médicos')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Médicos </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Médicos</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Médicos</h4>

                    {{-- ✅ Alertas de sucesso --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ❌ Alertas de erro --}}
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <a href="{{ route('tenant.doctors.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuário</th>
                                    <th>CRM</th>
                                    <th>Estado CRM</th>
                                    <th style="width: 240px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($doctors as $doctor)
                                    <tr>
                                        <td>{{ $doctor->id }}</td>
                                        <td>{{ $doctor->user->name ?? 'N/A' }}</td>
                                        <td>{{ $doctor->crm_number ?? 'N/A' }}</td>
                                        <td>{{ $doctor->crm_state ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <a href="{{ route('tenant.doctors.show', $doctor->id) }}" class="btn btn-info btn-sm">
                                                    <i class="mdi mdi-eye"></i> Ver
                                                </a>
                                                <a href="{{ route('tenant.doctors.edit', $doctor->id) }}" class="btn btn-warning btn-sm">
                                                    <i class="mdi mdi-pencil"></i> Editar
                                                </a>
                                                @if(!$doctor->hasAppointments())
                                                    <form action="{{ route('tenant.doctors.destroy', $doctor->id) }}" method="POST" class="d-inline" 
                                                          onsubmit="return confirm('Tem certeza que deseja excluir este médico?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="mdi mdi-delete"></i> Excluir
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-danger btn-sm" disabled 
                                                            title="Não é possível excluir médico com atendimentos cadastrados">
                                                        <i class="mdi mdi-delete"></i> Excluir
                                                    </button>
                                                @endif
                                            </div>
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

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable-list').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json"
            }
        });
    });
</script>
@endpush
