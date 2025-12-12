<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Integrations;
use App\Models\Tenant\OauthAccount;
use App\Models\Tenant\User;

use App\Http\Requests\Tenant\Integrations\StoreOAuthAccountRequest;
use App\Http\Requests\Tenant\Integrations\UpdateOAuthAccountRequest;

use Illuminate\Support\Str;

class OAuthAccountController extends Controller
{
    public function index()
    {
        $oauthAccounts = OauthAccount::with(['integration', 'user'])
            ->orderBy('expires_at', 'desc')
            ->paginate(20);

        return view('tenant.oauth-accounts.index', compact('oauthAccounts'));
    }

    public function create()
    {
        $integrations = Integrations::where('is_enabled', true)->get();
        $users = User::orderBy('name')->get();

        return view('tenant.oauth-accounts.create', compact('integrations', 'users'));
    }

    public function store(StoreOAuthAccountRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        OauthAccount::create($data);

        return redirect()->route('tenant.oauth-accounts.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Conta OAuth criada com sucesso.');
    }

    public function show($slug, $id)
    {
        $oauthAccount = OauthAccount::with(['integration', 'user'])->findOrFail($id);

        return view('tenant.oauth-accounts.show', compact('oauthAccount'));
    }

    public function edit($slug, $id)
    {
        $oauthAccount = OauthAccount::findOrFail($id);
        $integrations = Integrations::where('is_enabled', true)->get();
        $users = User::orderBy('name')->get();

        return view('tenant.oauth-accounts.edit', compact('oauthAccount', 'integrations', 'users'));
    }

    public function update(UpdateOAuthAccountRequest $request, $slug, $id)
    {
        $oauthAccount = OauthAccount::findOrFail($id);
        $oauthAccount->update($request->validated());

        return redirect()->route('tenant.oauth-accounts.index', ['slug' => $slug])
            ->with('success', 'Conta OAuth atualizada com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $oauthAccount = OauthAccount::findOrFail($id);
        $oauthAccount->delete();

        return redirect()->route('tenant.oauth-accounts.index', ['slug' => $slug])
            ->with('success', 'Conta OAuth removida.');
    }
}
