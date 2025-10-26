@php
    $hora = now()->format('H');
    if ($hora < 12) {
        $saudacao = 'Bom dia';
    } elseif ($hora < 18) {
        $saudacao = 'Boa tarde';
    } else {
        $saudacao = 'Boa noite';
    }
@endphp

@extends('layouts.freedash.app')

@section('content')
     @if (session('error'))
            <div class="text-center alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fa fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
    @endif
    <!-- ============================================================== -->
    <!-- Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">{{ $saudacao }},
                    {{ Auth::user()->name }}!</h3>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="index.html">Dashboard</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-5 align-self-center">
                <div class="customize-input float-end">
                    <select
                        class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                        <option selected="">Aug 23</option>
                        <option value="1">July 23</option>
                        <option value="2">Jun 23</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- End Bread crumb and right sidebar toggle -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- *************************************************************** -->
        <!-- Start First Cards -->
        <!-- *************************************************************** -->
        <div class="row">
            {{-- Tenants ativos --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">{{ number_format($activeTenants) }}</h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Tenants Ativos
                                </h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <i class="fas fa-industry"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Assinaturas ativas --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">{{ number_format($activeSubscriptions) }}
                                    </h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Assinaturas Ativas
                                </h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <i class="fas fa-pencil-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Faturamento do mês --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">R$
                                        {{ number_format($monthlyRevenue, 2, ',', '.') }}</h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Faturamento
                                    ({{ $now->translatedFormat('M/Y') }})
                                </h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Canceladas no mês --}}
            <div class="col-sm-6 col-lg-3">
                <div class="card border-end">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div>
                                <div class="d-inline-flex align-items-center">
                                    <h2 class="text-dark mb-1 font-weight-medium">
                                        {{ number_format($cancelledSubscriptions) }}</h2>
                                </div>
                                <h6 class="text-muted font-weight-normal mb-0 w-100 text-truncate">Assinaturas Canceladas
                                    ({{ $now->translatedFormat('M/Y') }})</h6>
                            </div>
                            <div class="ms-auto mt-md-3 mt-lg-0">
                                <i class="fas fa-window-close"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- *************************************************************** -->
        <!-- End First Cards -->
        <!-- *************************************************************** -->
        <!-- *************************************************************** -->
        <!-- Start Sales Charts Section -->
        <!-- *************************************************************** -->
        <div class="row mt-4">
            {{-- Gráfico Receita Total --}}
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h6 class="text-muted mb-3">Receita Total</h6>
                        <canvas id="revenueDoughnut" style="max-height: 260px;"></canvas>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="d-flex align-items-center">
                                    <span class="badge" style="background-color:#4F8DF9;">&nbsp;</span>
                                    <span class="ms-2">Recebido</span>
                                </div>
                                <strong>R$ {{ number_format($totalReceived, 2, ',', '.') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge" style="background-color:#5A6A85;">&nbsp;</span>
                                    <span class="ms-2">Vencido</span>
                                </div>
                                <strong>R$ {{ number_format($totalOverdue, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctxDoughnut = document.getElementById('revenueDoughnut');
                    new Chart(ctxDoughnut, {
                        type: 'doughnut',
                        data: {
                            labels: ['Recebido', 'Vencido'],
                            datasets: [{
                                data: [{{ $totalReceived }}, {{ $totalOverdue }}],
                                backgroundColor: ['#4F8DF9', '#5A6A85'], // azul e cinza escuro
                                borderWidth: 0,
                                cutout: '75%',
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#ffffff',
                                    titleColor: '#333333',
                                    bodyColor: '#333333',
                                    borderColor: '#e0e0e0',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': R$ ' + context.formattedValue.replace('.', ',');
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            @endpush

            {{-- Gráfico Crescimento de Clientes --}}
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="text-muted mb-3">Crescimento de Clientes ({{ $now->year }})</h6>
                        <canvas id="clientsGrowthChart" style="max-height: 260px;"></canvas>
                    </div>
                </div>
            </div>
            @push('scripts')
                <script>
                    const ctxClients = document.getElementById('clientsGrowthChart');
                    new Chart(ctxClients, {
                        type: 'line',
                        data: {
                            labels: [
                                'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'
                            ],
                            datasets: [{
                                label: 'Novos Tenants',
                                data: @json(array_values($months->toArray())),
                                fill: true,
                                borderColor: '#4F8DF9',
                                backgroundColor: 'rgba(79,141,249,0.2)',
                                tension: 0.4,
                                borderWidth: 2,
                                pointRadius: 4,
                                pointBackgroundColor: '#4F8DF9',
                                pointBorderWidth: 1
                            }]
                        },
                        options: {
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                </script>
            @endpush

        </div>
        <!-- *************************************************************** -->
        <!-- End Sales Charts Section -->
        <!-- *************************************************************** -->
        <!-- *************************************************************** -->
        <!-- Start Top Leader Table -->
        <!-- *************************************************************** -->
        <div class="row">
            <div class="col-12 mt-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <h4 class="card-title mb-0">Top 5 Tenants Mais Antigos</h4>
                            <div class="ms-auto">
                                <div class="dropdown sub-dropdown">
                                    <button class="btn btn-link text-muted dropdown-toggle" type="button" id="tenantMenu"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather feather-more-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="tenantMenu">
                                        <a class="dropdown-item" href="#">Inserir</a>
                                        <a class="dropdown-item" href="#">Atualizar</a>
                                        <a class="dropdown-item" href="#">Excluir</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table no-wrap v-middle mb-0">
                                <thead>
                                    <tr class="border-0">
                                        <th class="border-0 text-muted font-14">Tenant</th>
                                        <th class="border-0 text-muted font-14 px-2">Contato</th>
                                        <th class="border-0 text-muted font-14 text-center">Status</th>
                                        <th class="border-0 text-muted font-14 text-center">Criação</th>
                                        <th class="border-0 text-muted font-14 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($oldestTenants as $tenant)
                                        <tr>
                                            <td class="border-top-0 px-2 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                            style="width:45px;height:45px;">
                                                            <span class="text-primary fw-bold">
                                                                {{ strtoupper(substr($tenant->trade_name, 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h5 class="text-dark mb-0 font-16 fw-medium">
                                                            {{ $tenant->trade_name }}</h5>
                                                        <span class="text-muted font-14">{{ $tenant->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-top-0 text-muted px-2 py-3 font-14">
                                                {{ $tenant->phone ?? '-' }}</td>

                                            {{-- Status colorido --}}
                                            <td class="border-top-0 text-center px-2 py-3">
                                                @php
                                                    $color =
                                                        $tenant->status === 'active'
                                                            ? 'success'
                                                            : ($tenant->status === 'blocked'
                                                                ? 'danger'
                                                                : 'secondary');
                                                @endphp
                                                <i class="fa fa-circle text-{{ $color }}"
                                                    title="{{ ucfirst($tenant->status) }}"></i>
                                            </td>

                                            <td class="border-top-0 text-center text-muted font-14 px-2 py-3">
                                                {{ $tenant->created_at?->format('d/m/Y') }}
                                            </td>

                                            <td class="border-top-0 text-center text-muted font-14 px-2 py-3">
                                                {{ $tenant->localizacao?->estado?->uf ?? '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Nenhum tenant encontrado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- *************************************************************** -->
        <!-- End Top Leader Table -->
        <!-- *************************************************************** -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    @include('layouts.freedash.footer')
@endsection
