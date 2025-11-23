@extends('layouts.connect_plus.app')

@section('title', 'Dashboard')

@section('content')

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Consultas Hoje</h6>
                    <h3>14</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Pacientes Ativos</h6>
                    <h3>85</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-muted">Faturamento</h6>
                    <h3>R$ 8.240,00</h3>
                </div>
            </div>
        </div>
    </div>

@endsection
