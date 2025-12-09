<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Platform\ApiTenantToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class ApiTenantTokenController extends Controller
{
    /**
     * Lista tokens de um tenant
     */
    public function index(Tenant $tenant)
    {
        $tokens = ApiTenantToken::where('tenant_id', $tenant->id)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('platform.api_tokens.index', compact('tenant', 'tokens'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create(Tenant $tenant)
    {
        return view('platform.api_tokens.create', compact('tenant'));
    }

    /**
     * Cria novo token
     */
    public function store(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Gerar token seguro
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);
        $tokenEncrypted = Crypt::encryptString($plainToken);

        // Criar registro
        $token = ApiTenantToken::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'token_hash' => $tokenHash,
            'token_encrypted' => $tokenEncrypted,
            'expires_at' => $validated['expires_at'] ?? null,
            'active' => true,
            'created_by' => auth()->id(),
        ]);

        Log::info('Token de API criado', [
            'token_id' => $token->id,
            'tenant_id' => $tenant->id,
            'created_by' => auth()->id(),
        ]);

        // Redirecionar para a página de visualização do token
        return redirect()
            ->route('Platform.tenants.api-tokens.show', [$tenant, $token])
            ->with('success', 'Token criado com sucesso!');
    }

    /**
     * Exibe detalhes do token (com o token descriptografado)
     */
    public function show(Tenant $tenant, ApiTenantToken $token)
    {
        // Verificar se o token pertence ao tenant
        if ($token->tenant_id !== $tenant->id) {
            abort(404);
        }

        $decryptedToken = $token->getDecryptedToken();

        return view('platform.api_tokens.show', [
            'tenant' => $tenant,
            'token' => $token,
            'decryptedToken' => $decryptedToken,
        ]);
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(Tenant $tenant, ApiTenantToken $token)
    {
        // Verificar se o token pertence ao tenant
        if ($token->tenant_id !== $tenant->id) {
            abort(404);
        }

        return view('platform.api_tokens.edit', compact('tenant', 'token'));
    }

    /**
     * Atualiza token
     */
    public function update(Request $request, Tenant $tenant, ApiTenantToken $token)
    {
        // Verificar se o token pertence ao tenant
        if ($token->tenant_id !== $tenant->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $token->update([
            'name' => $validated['name'],
            'active' => $request->has('active') ? (bool)$validated['active'] : $token->active,
            'expires_at' => $validated['expires_at'] ?? $token->expires_at,
        ]);

        Log::info('Token de API atualizado', [
            'token_id' => $token->id,
            'tenant_id' => $tenant->id,
        ]);

        return redirect()
            ->route('Platform.tenants.api-tokens.index', $tenant)
            ->with('success', 'Token atualizado com sucesso!');
    }

    /**
     * Remove token
     */
    public function destroy(Tenant $tenant, ApiTenantToken $token)
    {
        // Verificar se o token pertence ao tenant
        if ($token->tenant_id !== $tenant->id) {
            abort(404);
        }

        $token->delete();

        Log::info('Token de API removido', [
            'token_id' => $token->id,
            'tenant_id' => $tenant->id,
        ]);

        return redirect()
            ->route('Platform.tenants.api-tokens.index', $tenant)
            ->with('success', 'Token removido com sucesso!');
    }
}
