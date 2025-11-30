@extends('layouts.connect_plus.app')

@section('title', 'Atendimento Médico')

@section('content')

    <div class="page-header">
        <h3 class="page-title">
            <i class="mdi mdi-account-heart text-primary me-2"></i>
            Atendimento Médico
        </h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('tenant.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Atendimento</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Selecione o dia para iniciar o atendimento</h4>

                    <form action="{{ route('tenant.medical-appointments.start') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="date" class="form-label">Data do Atendimento</label>
                            <input type="date" 
                                   class="form-control @error('date') is-invalid @enderror" 
                                   id="date" 
                                   name="date" 
                                   value="{{ old('date', date('Y-m-d')) }}"
                                   required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="mdi mdi-play-circle me-2"></i>
                                Iniciar Atendimento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

