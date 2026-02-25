@extends('layouts.tailadmin.app')

@section('title', 'Editar Campanha')
@section('page', 'campaigns')

@section('content')
    @php
        $formAction = workspace_route('tenant.campaigns.update', ['campaign' => $campaign->id]);
        $httpMethod = 'PUT';
        $submitLabel = 'Atualizar Campanha';
        $cancelUrl = workspace_route('tenant.campaigns.show', ['campaign' => $campaign->id]);
        $breadcrumbCurrent = 'Editar';
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
