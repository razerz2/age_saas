@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Templates de Notificação</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Templates de Notificação</li>
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

                {{-- ⚠️ Alertas de aviso --}}
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- 🔹 Exibição de erros de validação --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <strong>Ops!</strong> Verifique os erros abaixo:
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <h4 class="card-title mb-3">Lista de Templates</h4>
                <div class="table-responsive">
                    <table id="templates_table" class="table table-striped table-bordered text-nowrap align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Template</th>
                                <th>Canal</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($templates as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->display_name }}</strong><br>
                                        <small class="text-muted">{{ $template->name }}</small>
                                    </td>
                                    <td>
                                        @if ($template->channel === 'email')
                                            <span class="badge bg-primary">Email</span>
                                        @else
                                            <span class="badge bg-success">WhatsApp</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input toggle-template" type="checkbox"
                                                data-template-id="{{ $template->id }}"
                                                {{ $template->enabled ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <a title="Editar" href="{{ route('Platform.notification-templates.edit', $template->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <form action="{{ route('Platform.notification-templates.restore', $template->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info" title="Restaurar Padrão"
                                                onclick="return confirm('Deseja restaurar este template para os valores padrão?')">
                                                <i class="fas fa-arrow-path"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-primary btn-test-template"
                                            data-template-id="{{ $template->id }}"
                                            data-channel="{{ $template->channel }}"
                                            data-template-name="{{ $template->display_name }}"
                                            title="Enviar Teste">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Teste --}}
    @include('platform.notification_templates.test')

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#templates_table').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: "{{ asset('freedash/assets/js/datatables-lang/pt-BR.json') }}"
                }
            });

            // Toggle enabled/disabled
            $('.toggle-template').on('change', function() {
                const templateId = $(this).data('template-id');
                const enabled = $(this).is(':checked');

                $.ajax({
                    url: `/Platform/notification-templates/${templateId}/toggle`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Opcional: mostrar notificação
                        }
                    },
                    error: function() {
                        alert('Erro ao alterar status do template');
                        location.reload();
                    }
                });
            });

            // Abrir modal de teste
            $('.btn-test-template').on('click', function() {
                const templateId = $(this).data('template-id');
                const channel = $(this).data('channel');
                const templateName = $(this).data('template-name');

                $('#testTemplateId').val(templateId);
                $('#testChannel').val(channel);
                $('#testTemplateName').text(templateName);

                if (channel === 'email') {
                    $('#testEmailGroup').show();
                    $('#testPhoneGroup').hide();
                } else {
                    $('#testEmailGroup').hide();
                    $('#testPhoneGroup').show();
                }

                $('#testModal').modal('show');
            });

            // Enviar teste
            $('#btnSendTest').on('click', function() {
                const templateId = $('#testTemplateId').val();
                const channel = $('#testChannel').val();
                const email = $('#testEmail').val();
                const phone = $('#testPhone').val();

                if (channel === 'email' && !email) {
                    alert('Por favor, informe o email');
                    return;
                }

                if (channel === 'whatsapp' && !phone) {
                    alert('Por favor, informe o número do WhatsApp');
                    return;
                }

                $.ajax({
                    url: `/Platform/notification-templates/${templateId}/test`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: email,
                        phone: phone,
                        channel: channel
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#testModal').modal('hide');
                            $('#testEmail').val('');
                            $('#testPhone').val('');
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        alert(response?.message || 'Erro ao enviar teste');
                    }
                });
            });
        });
    </script>
@endpush

