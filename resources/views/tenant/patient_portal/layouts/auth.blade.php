@extends('layouts.tailadmin.public')

@section('title', ($title ?? 'Portal do Paciente') . ' — Sistema')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
@endpush

@section('content')
    <div class="container-scroller">
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper d-flex align-items-center auth">
                <div class="row flex-grow">
                    <div class="col-lg-4 mx-auto">
                        <div class="auth-form-light text-left p-5">

                            {{-- LOGO --}}
                            <div class="brand-logo text-center mb-4">
                                <img src="{{ asset('tailadmin/assets/images/logo/logo.svg') }}" alt="Logo">
                            </div>

                            {{-- CONTEÚDO DAS PÁGINAS --}}
                            @yield('content')

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

