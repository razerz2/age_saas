@extends('layouts.connect_plus.app')

@section('title', 'Formulários')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Formulários </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ workspace_route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Formulários</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Formulários</h4>

                    <a href="{{ workspace_route('tenant.forms.create') }}" class="btn btn-primary mb-3">
                        + Novo
                    </a>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Especialidade</th>
                                    <th>Médico</th>
                                    <th>Status</th>
                                    <th style="width: 300px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($forms as $form)
                                    <tr>
                                        <td>{{ $form->id }}</td>
                                        <td>{{ $form->name }}</td>
                                        <td>{{ $form->description ?? 'N/A' }}</td>
                                        <td>{{ $form->specialty->name ?? 'N/A' }}</td>
                                        <td>{{ $form->doctor->user->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($form->is_active)
                                                <span class="badge bg-success">Ativo</span>
                                            @else
                                                <span class="badge bg-danger">Inativo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ workspace_route('tenant.forms.show', $form->id) }}" class="btn btn-info btn-sm mb-1" title="Ver Detalhes">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.forms.builder', $form->id) }}" class="btn btn-primary btn-sm mb-1" title="Construir Formulário">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.responses.create', $form->id) }}" class="btn btn-success btn-sm mb-1" title="Preencher Formulário">
                                                <i class="mdi mdi-file-document-edit"></i>
                                            </a>
                                            <a href="{{ workspace_route('tenant.forms.edit', $form->id) }}" class="btn btn-warning btn-sm mb-1" title="Editar">
                                                <i class="mdi mdi-pencil-outline"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm mb-1 clear-content-btn" 
                                                    data-form-id="{{ $form->id }}" 
                                                    data-form-name="{{ $form->name }}"
                                                    data-form-action="{{ workspace_route('tenant.forms.clear-content', $form->id) }}"
                                                    title="Excluir Apenas Conteúdo">
                                                <i class="mdi mdi-delete-sweep"></i>
                                            </button>
                                            <form action="{{ workspace_route('tenant.forms.destroy', $form->id) }}" method="POST" class="d-inline delete-form" id="delete-form-{{ $form->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-sm mb-1 delete-form-btn" 
                                                        data-form-id="{{ $form->id }}" 
                                                        data-form-name="{{ $form->name }}"
                                                        title="Excluir Formulário Completo">
                                                    <i class="mdi mdi-delete"></i>
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

    // Excluir apenas conteúdo do formulário
    $(document).on('click', '.clear-content-btn', function() {
        const formId = $(this).data('form-id');
        const formName = $(this).data('form-name');
        const formAction = $(this).data('form-action');
        
        if (confirm('Tem certeza que deseja excluir APENAS o conteúdo do formulário "' + formName + '"?\n\nIsso irá remover:\n- Todas as seções\n- Todas as perguntas\n- Todas as opções\n\nO formulário será mantido, mas ficará vazio.')) {
            const form = $('<form>', {
                'method': 'POST',
                'action': formAction
            });
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));
            
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_method',
                'value': 'DELETE'
            }));
            
            $('body').append(form);
            form.submit();
        }
    });

    // Excluir formulário completo
    $(document).on('click', '.delete-form-btn', function() {
        const formId = $(this).data('form-id');
        const formName = $(this).data('form-name');
        
        if (confirm('ATENÇÃO: Tem certeza que deseja excluir o formulário "' + formName + '" COMPLETAMENTE?\n\nIsso irá remover:\n- O formulário\n- Todas as seções\n- Todas as perguntas\n- Todas as opções\n- Todas as respostas relacionadas\n\nEsta ação NÃO pode ser desfeita!')) {
            $('#delete-form-' + formId).submit();
        }
    });
</script>
@endpush

