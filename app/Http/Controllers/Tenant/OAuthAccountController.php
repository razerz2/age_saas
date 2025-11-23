<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Integrations as Integration;
use App\Models\Tenant\OauthAccount;

use App\Http\Requests\Tenant\Integrations\StoreOAuthAccountRequest;
use App\Http\Requests\Tenant\Integrations\UpdateOAuthAccountRequest;

use Illuminate\Support\Str;

class OAuthAccountController extends Controller
{
    public function index()
    {
        $accounts = OauthAccount::with(['integration', 'user'])
            ->orderBy('expires_at', 'desc')
            ->paginate(20);

        return view('tenant.oauth_accounts.index', compact('accounts'));
    }

    public function create()
    {
        $integrations = Integration::where('is_enabled', true)->get();

        return view('tenant.oauth_accounts.create', compact('integrations'));
    }

    public function store(StoreOAuthAccountRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        OauthAccount::create($data);

        return redirect()->route('tenant.oauth-accounts.index')
            ->with('success', 'Conta OAuth criada com sucesso.');
    }

    public function edit(OauthAccount $oauthAccount)
    {
        $integrations = Integration::where('is_enabled', true)->get();

        return view('tenant.oauth_accounts.edit', compact('oauthAccount', 'integrations'));
    }

    public function update(UpdateOAuthAccountRequest $request, OauthAccount $oauthAccount)
    {
        $oauthAccount->update($request->validated());

        return redirect()->route('tenant.oauth-accounts.index')
            ->with('success', 'Conta OAuth atualizada com sucesso.');
    }

    public function destroy(OauthAccount $oauthAccount)
    {
        $oauthAccount->delete();

        return redirect()->route('tenant.oauth-accounts.index')
            ->with('success', 'Conta OAuth removida.');
    }
}
