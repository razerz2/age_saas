@extends('layouts.public')

@section('title', 'Médicos — ' . $network->name)

@section('description', 'Encontre médicos da rede ' . $network->name . ' por especialidade e localização')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Nossos Médicos</h1>
        <p class="text-lg text-gray-600">Encontre o profissional ideal para você</p>
    </div>

    <!-- Filters -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
        <form method="GET" action="{{ route('network.doctors') }}" class="grid md:grid-cols-3 gap-4">
            @if($specialties->count() > 0)
            <div>
                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-2">Especialidade</label>
                <select name="specialty" id="specialty" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todas as especialidades</option>
                    @foreach($specialties as $specialty)
                    <option value="{{ $specialty['id'] }}" {{ request('specialty') == $specialty['id'] ? 'selected' : '' }}>
                        {{ $specialty['name'] }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($states->count() > 0)
            <div>
                <label for="state" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select name="state" id="state" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos os estados</option>
                    @foreach($states as $state)
                    <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                        {{ $state }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($cities->count() > 0)
            <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                <select name="city" id="city" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todas as cidades</option>
                    @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                        {{ $city }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="md:col-span-3 flex gap-4">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('network.doctors') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Doctors List -->
    @if($doctors->count() > 0)
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($doctors as $doctor)
        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $doctor['doctor_name'] }}</h3>
            
            @if($doctor['crm_number'])
            <p class="text-sm text-gray-600 mb-2">
                CRM: {{ $doctor['crm_number'] }}
                @if($doctor['crm_state'])/{{ $doctor['crm_state'] }}@endif
            </p>
            @endif

            @if(!empty($doctor['specialties']))
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-1">Especialidades:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($doctor['specialties'] as $specialty)
                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                        {{ $specialty['name'] }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($doctor['city'] || $doctor['state'])
            <p class="text-sm text-gray-600 mb-4">
                📍 {{ $doctor['city'] ?? '' }}@if($doctor['city'] && $doctor['state']), @endif{{ $doctor['state'] ?? '' }}
            </p>
            @endif

            <a href="{{ route('public.appointment.create', ['slug' => $doctor['tenant_slug'], 'doctor' => $doctor['doctor_id']]) }}" 
               class="inline-block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                Agendar Consulta
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-gray-50 border border-gray-200 rounded-lg">
        <p class="text-gray-600 text-lg">Nenhum médico encontrado com os filtros selecionados.</p>
    </div>
    @endif
</div>
@endsection

