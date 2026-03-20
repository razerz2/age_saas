@extends('layouts.freedash.app')
@section('title', 'Listar Plans')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Planos cadastrados</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Planos</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.plans.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Novo Plano
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
                        <h4 class="card-title mb-3">Lista de Planos</h4>

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="table-responsive">
                            <table id="plans_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th>Tipo</th>
                                        <th>Landing</th>
                                        <th>Trial</th>
                                        <th>Periodicidade</th>
                                        <th>Preco</th>
                                        <th>Status</th>
                                        <th class="text-center">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($plans as $plan)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $plan->name }}</td>
                                            <td>
                                                <span class="badge {{ $plan->categoryBadgeClass() }}">
                                                    {{ $plan->categoryLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $plan->planTypeBadgeClass() }}">
                                                    {{ $plan->planTypeLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $plan->landingVisibilityBadgeClass() }}">
                                                    {{ $plan->landingVisibilityLabel() }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($plan->hasCommercialTrial())
                                                    <span class="badge bg-info">{{ $plan->trialDaysLabel() }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Sem trial</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $plan->periodicity === 'monthly' ? 'Mensal' : 'Anual' }}
                                            </td>
                                            <td>{{ $plan->formatted_price }}</td>
                                            <td>
                                                <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $plan->is_active ? 'Ativo' : 'Inativo' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a title="Visualizar" href="{{ route('Platform.plans.show', $plan->id) }}" class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a title="Editar" href="{{ route('Platform.plans.edit', $plan->id) }}" class="btn btn-sm btn-warning text-white">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('Platform.plans.destroy', $plan->id) }}" method="POST" class="d-inline"
                                                    onsubmit="return confirmSubmit(event, 'Deseja realmente excluir este plano? Esta acao nao pode ser desfeita.', 'Confirmar Exclusao')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Exclusao" class="btn btn-sm btn-danger">
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

@include("layouts.freedash.footer")
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#plans_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush
