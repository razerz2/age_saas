@extends('tenant.patient_portal.layouts.auth')

@section('title', 'Login - Portal do Paciente')

@section('content')
<h4>Bem-vindo ao Portal do Paciente!</h4>
<h6 class="font-weight-light mb-4">Entre para continuar</h6>

{{-- FORM LOGIN --}}
<form method="POST" action="{{ route('patient.login.submit', ['tenant' => $tenant]) }}" class="pt-3">
    @csrf

    {{-- EMAIL --}}
    <div class="form-group">
        <input type="email" name="email"
            class="form-control form-control-lg @error('email') is-invalid @enderror"
            placeholder="E-mail" value="{{ old('email') }}" required autofocus>
        @error('email')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    {{-- SENHA --}}
    <div class="form-group">
        <input type="password" name="password"
            class="form-control form-control-lg @error('password') is-invalid @enderror"
            placeholder="Senha" required>
        @error('password')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    {{-- BOTÃO LOGIN --}}
    <div class="mt-3">
        <button type="submit"
            class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
            Entrar
        </button>
    </div>

    {{-- MANTER CONECTADO + ESQUECEU A SENHA --}}
    <div class="my-2 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <label class="form-check-label text-muted">
                <input type="checkbox" name="remember" class="form-check-input">
                Manter conectado
            </label>
        </div>

        <a href="{{ route('patient.forgot-password', ['tenant' => $tenant]) }}" class="auth-link text-black">
            Esqueceu a senha?
        </a>
    </div>
</form>
@endsection

