<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Integrations;

use App\Http\Requests\Tenant\Integrations\StoreIntegrationRequest;
use App\Http\Requests\Tenant\Integrations\UpdateIntegrationRequest;

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
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Integrations::create($data);

        return redirect()->route('tenant.integrations.index')
            ->with('success', 'Integração criada com sucesso.');
    }

    public function show($slug, $id)
    {
        $integration = Integrations::findOrFail($id);
        return view('tenant.integrations.show', compact('integration'));
    }

    public function edit($slug, $id)
    {
        $integration = Integrations::findOrFail($id);
        return view('tenant.integrations.edit', compact('integration'));
    }

    public function update(UpdateIntegrationRequest $request, $slug, $id)
    {
        $integration = Integrations::findOrFail($id);
        $integration->update($request->validated());

        return redirect()->route('tenant.integrations.index')
            ->with('success', 'Integração atualizada com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $integration = Integrations::findOrFail($id);
        $integration->delete();

        return redirect()->route('tenant.integrations.index')
            ->with('success', 'Integração removida.');
    }
}
