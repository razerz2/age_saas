@extends('layouts.freedash.app')

@section('content')

    <div class="container-fluid">
        
        <!-- Título e breadcrumb -->
        <div class="page-breadcrumb">
            <div class="row">
                <div class="col-7 align-self-center">
                    <h4 class="page-title text-dark font-weight-medium mb-1">Perfil do Usuário</h4>
                    <div class="d-flex align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                        class="text-muted">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item text-muted active" aria-current="page">Perfil</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulários -->
        <div class="row">
            <div class="col-lg-8 col-md-10">

                {{-- Atualizar informações do perfil --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Informações Pessoais</h4>
                        <p class="card-subtitle mb-4">
                            Atualize seu nome, e-mail e outras informações básicas da conta.
                        </p>
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Atualizar senha --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Atualizar Senha</h4>
                        <p class="card-subtitle mb-4">
                            Mantenha sua conta segura alterando sua senha regularmente.
                        </p>
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                {{-- Excluir conta --}}
                <div class="card border-danger">
                    <div class="card-body">
                        <h4 class="card-title text-danger">Excluir Conta</h4>
                        <p class="card-subtitle mb-4 text-muted">
                            A exclusão da conta é permanente. Todos os seus dados serão removidos.
                        </p>
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection
