@extends('layouts.public')

@section('title', 'Unidades — ' . $network->name)

@section('description', 'Conheça as unidades da rede ' . $network->name)

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Nossas Unidades</h1>
        <p class="text-lg text-gray-600">Encontre a unidade mais próxima de você</p>
    </div>

    <!-- Units List -->
    @if($units->count() > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($units as $unit)
        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                {{ $unit->trade_name ?? $unit->legal_name }}
            </h3>

            @if($unit->localizacao)
            <div class="space-y-2 text-gray-600">
                @if($unit->localizacao->endereco)
                <p class="flex items-start">
                    <span class="mr-2">📍</span>
                    <span>
                        {{ $unit->localizacao->endereco }}
                        @if($unit->localizacao->n_endereco), {{ $unit->localizacao->n_endereco }}@endif
                        @if($unit->localizacao->complemento) — {{ $unit->localizacao->complemento }}@endif
                        @if($unit->localizacao->bairro)<br>{{ $unit->localizacao->bairro }}@endif
                        @if($unit->localizacao->cidade)
                            <br>{{ $unit->localizacao->cidade->nome }}
                            @if($unit->localizacao->estado), {{ $unit->localizacao->estado->sigla }}@endif
                            @if($unit->localizacao->cep) — {{ $unit->localizacao->cep }}@endif
                        @endif
                    </span>
                </p>
                @elseif($unit->localizacao->cidade)
                <p class="flex items-start">
                    <span class="mr-2">📍</span>
                    <span>
                        {{ $unit->localizacao->cidade->nome }}
                        @if($unit->localizacao->estado), {{ $unit->localizacao->estado->sigla }}@endif
                    </span>
                </p>
                @endif
            </div>
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="{{ route('public.patient.identify', ['slug' => $unit->subdomain]) }}" 
                   class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Agendar nesta unidade
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 border border-gray-200 rounded-lg">
        <p class="text-gray-600 text-lg">Nenhuma unidade cadastrada.</p>
    </div>
    @endif
</div>
@endsection

