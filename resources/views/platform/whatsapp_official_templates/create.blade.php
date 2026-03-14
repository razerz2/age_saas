@extends('layouts.freedash.app')
@section('title', 'Novo Template WhatsApp Oficial')

@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title text-truncate text-dark font-weight-medium mb-1">Novo Template WhatsApp Oficial</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb m-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}" class="text-muted">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('Platform.whatsapp-official-templates.index') }}" class="text-muted">Templates Oficiais</a></li>
                            <li class="breadcrumb-item text-muted active" aria-current="page">Novo</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('Platform.whatsapp-official-templates.store') }}">
                    @csrf
                    @include('platform.whatsapp_official_templates._form', ['template' => $template])
                </form>
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

