<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Integrations\StoreIntegrationRequest;
use App\Http\Requests\Tenant\Integrations\UpdateIntegrationRequest;
use App\Models\Tenant\Integrations;
use Illuminate\Support\Str;

class IntegrationController extends Controller
{
    public function index()
    {
        $integrations = Integrations::orderBy('key')->paginate(20);

        return view('tenant.integrations.index', compact('integrations'));
    }

    public function create()
    {
        return view('tenant.integrations.create');
    }

    public function store(StoreIntegrationRequest $request)
    {
        $data = $request->payload();
        $data['id'] = Str::uuid();

        Integrations::create($data);

        return redirect()->route('tenant.integrations.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Integracao criada com sucesso.');
    }

    public function show(string $slug, string $integration)
    {
        $integration = Integrations::findOrFail($integration);

        return view('tenant.integrations.show', compact('integration'));
    }

    public function edit(string $slug, string $integration)
    {
        $integration = Integrations::findOrFail($integration);

        return view('tenant.integrations.edit', compact('integration'));
    }

    public function update(UpdateIntegrationRequest $request, string $slug, string $integration)
    {
        $integration = Integrations::findOrFail($integration);
        $integration->update($request->payload());

        return redirect()->route('tenant.integrations.index', ['slug' => $slug])
            ->with('success', 'Integracao atualizada com sucesso.');
    }

    public function destroy(string $slug, string $integration)
    {
        $integration = Integrations::findOrFail($integration);
        $integration->delete();

        return redirect()->route('tenant.integrations.index', ['slug' => $slug])
            ->with('success', 'Integracao removida.');
    }
}
