@extends('layouts.network-admin')

@section('title', 'Médicos')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-doctor"></i>
        </span> Médicos da Rede
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Médicos</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12 grid-margin stretch-card">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-filter-outline me-2 text-primary"></i>Filtros de Pesquisa</h4>
                <form method="GET" action="{{ route('network.doctors.index') }}" class="mb-4">
                    <div class="row g-3">
                        @if($specialties->count() > 0)
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Especialidade</label>
                            <select name="specialty" class="form-select border-primary-light">
                                <option value="">Todas as Especialidades</option>
                                @foreach($specialties as $specialty)
                                <option value="{{ $specialty['id'] }}" {{ request('specialty') == $specialty['id'] ? 'selected' : '' }}>
                                    {{ $specialty['name'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($states->count() > 0)
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Estado</label>
                            <select name="state" class="form-select border-primary-light">
                                <option value="">Todos</option>
                                @foreach($states as $state)
                                <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($cities->count() > 0)
                        <div class="col-md-2">
                            <label class="form-label font-weight-bold">Cidade</label>
                            <select name="city" class="form-select border-primary-light">
                                <option value="">Todas</option>
                                @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if($tenants->count() > 0)
                        <div class="col-md-3">
                            <label class="form-label font-weight-bold">Clínica</label>
                            <select name="tenant_slug" class="form-select border-primary-light">
                                <option value="">Todas as Clínicas</option>
                                @foreach($tenants as $tenant)
                                <option value="{{ $tenant->subdomain }}" {{ request('tenant_slug') == $tenant->subdomain ? 'selected' : '' }}>
                                    {{ $tenant->trade_name ?? $tenant->legal_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-gradient-primary w-100 btn-icon-text">
                                <i class="mdi mdi-magnify btn-icon-prepend"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="font-weight-bold">#</th>
                                <th class="font-weight-bold">Nome do Profissional</th>
                                <th class="font-weight-bold">CRM / Registro</th>
                                <th class="font-weight-bold">Especialidades</th>
                                <th class="font-weight-bold">Unidade Atuante</th>
                                <th class="font-weight-bold">Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctors as $doctor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-bold text-dark">
                                    <i class="mdi mdi-account-circle text-primary me-2"></i>
                                    {{ $doctor['doctor_name'] }}
                                </td>
                                <td>
                                    <span class="badge badge-outline-secondary">
                                        {{ $doctor['crm_number'] ?? '-' }}
                                        @if($doctor['crm_state'])/{{ $doctor['crm_state'] }}@endif
                                    </span>
                                </td>
                                <td>
                                    @foreach($doctor['specialties'] as $specialty)
                                        <span class="badge bg-gradient-info me-1">{{ $specialty['name'] }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <i class="mdi mdi-hospital-building text-muted me-1"></i>
                                    {{ $doctor['tenant_name'] }}
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="mdi mdi-map-marker text-danger me-1"></i>
                                        {{ $doctor['city'] ?? '' }}@if($doctor['city'] && $doctor['state']), @endif{{ $doctor['state'] ?? '' }}
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="mdi mdi-account-off-outline mdi-36px d-block mb-3"></i>
                                    Nenhum médico encontrado com os filtros selecionados.
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
@endsection

@push('styles')
<style>
    .form-select {
        height: 45px;
        border-radius: 5px;
        font-size: 0.875rem;
    }
    .border-primary-light {
        border-color: #ebedf2;
    }
    .border-primary-light:focus {
        border-color: #b66dff;
        box-shadow: 0 0 0 0.2rem rgba(182, 109, 255, 0.25);
    }
    .btn-gradient-primary {
        background: linear-gradient(to right, #da8cff, #9a55ff);
        border: 0;
        color: white;
        height: 45px;
    }
    .badge-outline-secondary {
        border: 1px solid #c3c3c3;
        color: #6c757d;
        background: transparent;
    }
    .table thead th {
        border-top: 0;
        border-bottom-width: 1px;
    }
</style>
@endpush

