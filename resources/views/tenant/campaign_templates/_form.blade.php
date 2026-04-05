@php
    $campaignTemplate = $campaignTemplate ?? null;
    $availableVariables = is_array($availableVariables ?? null) ? $availableVariables : [];
    $variablesJsonOld = old('variables_json', $campaignTemplate?->variables_json ?? []);
    $variablesJsonOld = is_array($variablesJsonOld) ? $variablesJsonOld : [];
    $variablesTextValue = old('variables_json_text', implode("\n", $variablesJsonOld));
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
        <p class="mb-2 text-sm font-medium text-red-800 dark:text-red-200">Revise os campos abaixo:</p>
        <ul class="list-disc space-y-1 pl-5 text-sm text-red-700 dark:text-red-300">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $formAction }}" method="POST" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    @csrf
    @if (($httpMethod ?? 'POST') !== 'POST')
        @method($httpMethod)
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div>
            <label for="campaign-template-name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nome <span class="text-red-500">*</span>
            </label>
            <input
                id="campaign-template-name"
                type="text"
                name="name"
                required
                maxlength="150"
                value="{{ old('name', $campaignTemplate?->name) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end">
            <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 dark:border-gray-600 dark:text-gray-200">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    @checked(old('is_active', $campaignTemplate?->is_active ?? true))
                >
                Template ativo
            </label>
        </div>
    </div>

    <div>
        <label for="campaign-template-content" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Conteúdo <span class="text-red-500">*</span>
        </label>
        <textarea
            id="campaign-template-content"
            name="content"
            rows="8"
            required
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('content') border-red-500 @enderror"
        >{{ old('content', $campaignTemplate?->content) }}</textarea>
        @error('content')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="campaign-template-variables-json-text" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Variáveis adicionais (opcional)
        </label>
        <textarea
            id="campaign-template-variables-json-text"
            name="variables_json_text"
            rows="4"
            placeholder="{{ '{{patient.name}}' }}&#10;{{ '{{clinic.name}}' }}"
            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white @error('variables_json') border-red-500 @enderror"
        >{{ $variablesTextValue }}</textarea>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Informe uma variável por linha. Exemplo: <code>{{ '{{patient.name}}' }}</code></p>
        @error('variables_json')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
        @error('variables_json.*')
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/30">
        <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Variáveis suportadas</h3>
        @include('tenant.components.variables-list', [
            'variables' => $availableVariables,
            'listContainerClass' => 'max-h-[320px] space-y-4 overflow-y-auto pr-1',
        ])
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ $cancelUrl }}" class="btn btn-outline inline-flex items-center">Cancelar</a>
        <button type="submit" class="btn btn-primary inline-flex items-center">{{ $submitLabel }}</button>
    </div>
</form>

