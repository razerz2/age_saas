@extends('layouts.network-admin')

@section('title', 'Clínicas')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-hospital-building"></i>
        </span> Clínicas da Rede
    </h3>
    <nav aria-label="breadcrumb">
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('network.dashboard', ['network' => app('currentNetwork')->slug]) }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Clínicas</li>
        </ul>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="card-title"><i class="mdi mdi-format-list-bulleted me-2 text-primary"></i>Lista de Unidades</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th class="font-weight-bold">#</th>
                                <th class="font-weight-bold">Nome Fantasia</th>
                                <th class="font-weight-bold">Razão Social</th>
                                <th class="font-weight-bold">Subdomínio</th>
                                <th class="font-weight-bold">Localização</th>
                                <th class="font-weight-bold">Assinatura</th>
                                <th class="font-weight-bold text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clinics as $clinic)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-weight-bold">{{ $clinic->trade_name ?? '-' }}</td>
                                <td class="text-muted">{{ $clinic->legal_name }}</td>
                                <td><span class="badge badge-outline-primary">{{ $clinic->subdomain }}</span></td>
                                <td>
                                    @if($clinic->localizacao)
                                        <i class="mdi mdi-map-marker text-danger me-1"></i>
                                        {{ $clinic->localizacao->cidade->nome ?? '' }}
                                        @if($clinic->localizacao->estado), {{ $clinic->localizacao->estado->sigla ?? '' }}@endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($clinic->activeSubscription)
                                        <span class="badge bg-gradient-success">{{ $clinic->activeSubscription->plan->name ?? 'Ativa' }}</span>
                                    @else
                                        <span class="badge bg-gradient-secondary">Sem assinatura</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('Platform.tenants.show', $clinic->id) }}" 
                                       target="_blank"
                                       class="btn btn-gradient-info btn-sm btn-icon-text">
                                        <i class="mdi mdi-eye btn-icon-prepend"></i> Detalhes
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="mdi mdi-alert-circle-outline mdi-36px d-block mb-3"></i>
                                    Nenhuma clínica cadastrada nesta rede.
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
    .table thead th {
        border-top: 0;
        border-bottom-width: 1px;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    .badge-outline-primary {
        color: #b66dff;
        border: 1px solid #b66dff;
        background: transparent;
    }
    .btn-gradient-info {
        background: linear-gradient(to right, #36d1dc, #5b86e5);
        border: 0;
        color: white;
    }
    .btn-gradient-info:hover {
        opacity: 0.9;
        color: white;
    }
</style>
@endpush

