@extends('layouts.network-admin')

@section('title', 'Médicos')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-doctor"></i>
        </span> Médicos da Rede
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{-- Filtros --}}
                <form method="GET" action="{{ route('network.doctors.index') }}" class="mb-4">
                    <div class="row g-3">
                        @if($specialties->count() > 0)
                        <div class="col-md-3">
                            <label class="form-label">Especialidade</label>
                            <select name="specialty" class="form-control">
                                <option value="">Todas</option>
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
                            <label class="form-label">Estado</label>
                            <select name="state" class="form-control">
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
                            <label class="form-label">Cidade</label>
                            <select name="city" class="form-control">
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
                            <label class="form-label">Clínica</label>
                            <select name="tenant_slug" class="form-control">
                                <option value="">Todas</option>
                                @foreach($tenants as $tenant)
                                <option value="{{ $tenant->subdomain }}" {{ request('tenant_slug') == $tenant->subdomain ? 'selected' : '' }}>
                                    {{ $tenant->trade_name ?? $tenant->legal_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome</th>
                                <th>CRM</th>
                                <th>Especialidades</th>
                                <th>Clínica</th>
                                <th>Localização</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctors as $doctor)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $doctor['doctor_name'] }}</td>
                                <td>
                                    {{ $doctor['crm_number'] ?? '-' }}
                                    @if($doctor['crm_state'])/{{ $doctor['crm_state'] }}@endif
                                </td>
                                <td>
                                    @foreach($doctor['specialties'] as $specialty)
                                        <span class="badge bg-info me-1">{{ $specialty['name'] }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $doctor['tenant_name'] }}</td>
                                <td>
                                    {{ $doctor['city'] ?? '' }}@if($doctor['city'] && $doctor['state']), @endif{{ $doctor['state'] ?? '' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
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

