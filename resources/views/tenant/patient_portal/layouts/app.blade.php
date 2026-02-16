@extends('layouts.tailadmin.public')

@section('title', ($title ?? 'Portal do Paciente') . ' — Sistema')

@section('content')
    <div class="container-scroller">

        {{-- NAVBAR --}}
        @include('tenant.patient_portal.layouts.navbar')

        <div class="container-fluid page-body-wrapper">

            {{-- MENU LATERAL --}}
            @include('tenant.patient_portal.layouts.navigation')

            <div class="main-panel">
                <div class="content-wrapper">

                    {{-- CONTEÚDO DAS PÁGINAS --}}
                    @yield('content')

                </div>
            </div>
        </div>
    </div>
@endsection
