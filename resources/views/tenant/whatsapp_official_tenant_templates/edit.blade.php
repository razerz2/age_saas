@extends('layouts.tailadmin.app')

@section('title', 'Editar Template Oficial')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Editar Template Oficial</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Atualize o template oficial deste evento clínico.</p>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form method="POST" action="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.update', $template->id) }}">
            @csrf
            @method('PUT')
            @include('tenant.whatsapp_official_tenant_templates._form', ['template' => $template])
        </form>
    </div>
</div>
@endsection

