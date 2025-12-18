@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Regras de Acesso de Assinaturas</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Regras de Acesso</li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <a href="{{ route('Platform.subscription-access.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fa fa-plus me-1"></i> Nova Regra
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Lista de Regras -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Lista de Regras de Acesso</h4>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->has('general'))
                            <div class="alert alert-danger alert-dismissible fade show">{{ $errors->first('general') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="rules_table" class="table table-striped table-bordered text-nowrap align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Plano</th>
                                        <th>Categoria</th>
                                        <th>Max Admins</th>
                                        <th>Max Usuários</th>
                                        <th>Max Médicos</th>
                                        <th>Funcionalidades</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rules as $rule)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $rule->plan->name ?? '-' }}</td>
                                            <td>
                                                @if($rule->plan)
                                                    @php
                                                        $categoryLabel = match($rule->plan->category) {
                                                            'commercial' => 'Comercial',
                                                            'contractual' => 'Contratual',
                                                            'sandbox' => 'Sandbox',
                                                            default => $rule->plan->category
                                                        };
                                                        $categoryClass = match($rule->plan->category) {
                                                            'commercial' => 'bg-info',
                                                            'contractual' => 'bg-primary',
                                                            'sandbox' => 'bg-warning text-dark',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $categoryClass }}">{{ $categoryLabel }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $rule->max_admin_users }}</td>
                                            <td>{{ $rule->max_common_users }}</td>
                                            <td>{{ $rule->max_doctors }}</td>
                                            <td>
                                                @php
                                                    $allowedCount = $rule->features->where('pivot.allowed', true)->count();
                                                    $totalCount = $rule->features->count();
                                                @endphp
                                                <span class="badge bg-info">{{ $allowedCount }}/{{ $totalCount }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a title="Visualizar" href="{{ route('Platform.subscription-access.show', $rule->id) }}"
                                                    class="btn btn-sm btn-info text-white">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a title="Editar" href="{{ route('Platform.subscription-access.edit', $rule->id) }}"
                                                    class="btn btn-sm btn-warning text-white">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="{{ route('Platform.subscription-access.destroy', $rule->id) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirmSubmit(event, 'Deseja realmente excluir esta regra? Esta ação não pode ser desfeita.', 'Confirmar Exclusão')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Excluir" class="btn btn-sm btn-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Nenhuma regra cadastrada</td>
                                        </tr>
                                    @endforelse
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
            $('#rules_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });
        });
    </script>
@endpush

