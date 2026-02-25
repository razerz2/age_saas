@extends('layouts.tailadmin.app')

@section('title', 'Nova Campanha')
@section('page', 'campaigns')

@section('content')
    @php
        $campaign = null;
        $formAction = workspace_route('tenant.campaigns.store');
        $httpMethod = 'POST';
        $submitLabel = 'Salvar Campanha';
        $cancelUrl = workspace_route('tenant.campaigns.index');
        $breadcrumbCurrent = 'Criar';
    @endphp

    @include('tenant.campaigns.partials.form', [
        'campaign' => $campaign,
        'availableChannels' => $availableChannels ?? [],
        'formAction' => $formAction,
        'httpMethod' => $httpMethod,
        'submitLabel' => $submitLabel,
        'cancelUrl' => $cancelUrl,
        'breadcrumbCurrent' => $breadcrumbCurrent,
    ])
@endsection
