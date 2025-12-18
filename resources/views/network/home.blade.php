@extends('layouts.public')

@section('title', $network->name . ' — Rede de Clínicas')

@section('description', 'Encontre médicos e unidades da rede ' . $network->name)

@section('content')
<div class="space-y-8">
    <!-- Hero Section -->
    <div class="text-center py-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $network->name }}</h1>
        <p class="text-xl text-gray-600">Rede de Clínicas</p>
    </div>

    <!-- Navigation Links -->
    <div class="grid md:grid-cols-2 gap-6 mt-12">
        <a href="{{ route('network.doctors') }}" class="block p-6 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors">
            <h2 class="text-2xl font-semibold text-blue-900 mb-2">👨‍⚕️ Nossos Médicos</h2>
            <p class="text-gray-700">Encontre médicos de todas as especialidades</p>
        </a>

        <a href="{{ route('network.units') }}" class="block p-6 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors">
            <h2 class="text-2xl font-semibold text-green-900 mb-2">🏥 Nossas Unidades</h2>
            <p class="text-gray-700">Conheça nossas clínicas e localizações</p>
        </a>
    </div>

    <!-- Units Preview -->
    @if($units->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Unidades</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($units->take(6) as $unit)
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    {{ $unit->trade_name ?? $unit->legal_name }}
                </h3>
                @if($unit->localizacao)
                <p class="text-gray-600">
                    @if($unit->localizacao->cidade)
                        {{ $unit->localizacao->cidade->nome }}
                        @if($unit->localizacao->estado), {{ $unit->localizacao->estado->sigla }}@endif
                    @endif
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

