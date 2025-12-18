@extends('layouts.network-admin')

@section('title', 'Clínicas')

@section('content')
<div class="page-header">
    <h3 class="page-title">
        <span class="page-title-icon bg-gradient-primary text-white me-2">
            <i class="mdi mdi-hospital-building"></i>
        </span> Clínicas da Rede
    </h3>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome Fantasia</th>
                                <th>Razão Social</th>
                                <th>Subdomínio</th>
                                <th>Localização</th>
                                <th>Assinatura</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($clinics as $clinic)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $clinic->trade_name ?? '-' }}</td>
                                <td>{{ $clinic->legal_name }}</td>
                                <td><code>{{ $clinic->subdomain }}</code></td>
                                <td>
                                    @if($clinic->localizacao)
                                        {{ $clinic->localizacao->cidade->nome ?? '' }}
                                        @if($clinic->localizacao->estado), {{ $clinic->localizacao->estado->sigla ?? '' }}@endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($clinic->activeSubscription)
                                        <span class="badge bg-success">{{ $clinic->activeSubscription->plan->name ?? 'Ativa' }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sem assinatura</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('Platform.tenants.show', $clinic->id) }}" 
                                       target="_blank"
                                       class="btn btn-sm btn-info">
                                        <i class="mdi mdi-eye"></i> Ver na Platform
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
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

