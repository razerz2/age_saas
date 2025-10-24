@extends('layouts.freedash.app')
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Nova Fatura</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                    class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.invoices.index') }}"
                                    class="text-muted">Faturas</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Nova</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($errors->has('general'))
                    <div class="alert alert-danger">{{ $errors->first('general') }}</div>
                @endif

                <form method="POST" action="{{ route('Platform.invoices.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tenant</label>
                            <select id="tenant_id" name="tenant_id" class="form-control" required>
                                <option value="">Selecione o tenant</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}"
                                        {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                        {{ $tenant->trade_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assinatura</label>
                            <select id="subscription_id" name="subscription_id" class="form-control" required>
                                <option value="">Selecione a assinatura</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Valor (R$)</label>
                            <input type="text" name="amount_cents_display" id="amount_display" class="form-control"
                                required>
                            <input type="hidden" name="amount_cents" id="amount_cents">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Vencimento</label>
                            <input type="date" name="due_date" id="due_date"
                                value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}" class="form-control"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                @foreach (['pending' => 'Pendente', 'paid' => 'Pago', 'overdue' => 'Vencido', 'canceled' => 'Cancelado'] as $k => $v)
                                    <option value="{{ $k }}" {{ old('status') == $k ? 'selected' : '' }}>
                                        {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Link de Pagamento</label>
                            <input type="url" name="payment_link" value="{{ old('payment_link') }}"
                                class="form-control" placeholder="https://...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Provedor</label>
                            <input type="text" name="provider" value="{{ old('provider') }}" class="form-control"
                                placeholder="Asaas, Pagar.me...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ID no Gateway</label>
                            <input type="text" name="provider_id" value="{{ old('provider_id') }}" class="form-control">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-4">Salvar Fatura</button>
                        <a href="{{ route('Platform.invoices.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

@push('scripts')
    <script>
        $(function() {

            console.log('💡 Script carregado.');

            const routeTemplate =
                "{{ route('Platform.subscriptions.getByTenant', ['tenant' => 'TENANT_ID_PLACEHOLDER']) }}";

            const $tenantSelect = $('#tenant_id');
            const $subscriptionSelect = $('#subscription_id');
            const $amountDisplay = $('#amount_display');
            const $amountCents = $('#amount_cents');

            // Quando o tenant mudar
            $tenantSelect.on('change', function() {
                const tenantId = $(this).val();

                console.log('➡️ Tenant selecionado:', tenantId);

                $subscriptionSelect.html('<option value="">Carregando assinaturas...</option>');
                $amountDisplay.val('');
                $amountCents.val('');

                if (!tenantId) {
                    $subscriptionSelect.html('<option value="">Selecione a assinatura</option>');
                    return;
                }

                const routeUrl = routeTemplate.replace('TENANT_ID_PLACEHOLDER', tenantId);

                $.ajax({
                    url: routeUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('✅ Dados recebidos:', data);

                        $subscriptionSelect.empty();

                        if (!Array.isArray(data) || data.length === 0) {
                            $subscriptionSelect.append(
                                '<option value="">Nenhuma assinatura encontrada</option>');
                            return;
                        }

                        $subscriptionSelect.append(
                            '<option value="">Selecione a assinatura</option>');

                        // Preenche o select com todas
                        data.forEach(sub => {
                            $subscriptionSelect.append(
                                $('<option>', {
                                    value: sub.id,
                                    text: `${sub.name} (${sub.status})`,
                                    'data-value': sub.value * 100
                                })
                            );
                        });

                        // ✅ Se só existir 1 assinatura, seleciona e preenche automaticamente
                        if (data.length === 1) {
                            const sub = data[0];
                            console.log('🎯 Selecionando assinatura automaticamente:', sub
                            .name);

                            $subscriptionSelect.val(sub.id); // seleciona a assinatura
                            preencherValor(sub.value * 100); // preenche o valor
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Erro AJAX:', xhr.status, error, xhr.responseText);
                        $subscriptionSelect.html(
                            '<option value="">Erro ao carregar assinaturas</option>');
                    }
                });
            });

            // Função reutilizável para preencher os campos de valor
            function preencherValor(valueCents) {
                if (valueCents) {
                    const valueReais = valueCents / 100;
                    const formatted = new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    }).format(valueReais);

                    $amountDisplay.val(formatted);
                    $amountCents.val(valueCents);
                } else {
                    $amountDisplay.val('');
                    $amountCents.val('');
                }
            }

            // Quando o usuário mudar a assinatura manualmente
            $subscriptionSelect.on('change', function() {
                const valueCents = $(this).find(':selected').data('value');
                preencherValor(valueCents);
            });

        });
    </script>
@endpush
