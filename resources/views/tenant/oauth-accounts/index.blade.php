@extends('layouts.connect_plus.app')

@section('title', 'Contas OAuth')

@section('content')

    <div class="page-header">
        <h3 class="page-title"> Contas OAuth </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Contas OAuth</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Lista de Contas OAuth</h4>

                    <div class="table-responsive">
                        <table class="table table-hover" id="datatable-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Integração</th>
                                    <th>Usuário</th>
                                    <th>Expira em</th>
                                    <th style="width: 140px;">Ações</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($oauthAccounts as $oauthAccount)
                                    <tr>
                                        <td>{{ $oauthAccount->id }}</td>
                                        <td>{{ $oauthAccount->integration->key ?? 'N/A' }}</td>
                                        <td>{{ $oauthAccount->user_id ?? 'N/A' }}</td>
                                        <td>{{ $oauthAccount->expires_at ? $oauthAccount->expires_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('tenant.oauth-accounts.show', $oauthAccount->id) }}" class="btn btn-info btn-sm">Ver</a>
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
</script>
@endpush

