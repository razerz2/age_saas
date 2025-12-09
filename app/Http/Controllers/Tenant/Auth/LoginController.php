<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Platform\Tenant;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $slug = $request->route('slug') ?? $request->route('tenant');

        \Log::info("📌 Exibindo login form para slug", ['slug' => $slug]);

        // Verifica se o usuário já está autenticado no guard tenant
        if (Auth::guard('tenant')->check()) {
            // Se estiver autenticado, redireciona para o dashboard
            $tenantSlug = tenant()->subdomain ?? $slug;
            return redirect()->route('tenant.dashboard', ['slug' => $tenantSlug]);
        }

        $tenant = Tenant::where('subdomain', $slug)->first();

        return view('tenant.auth.login', compact('tenant'));
    }

    public function login(Request $request)
    {
        \Log::info('===== INÍCIO LOGIN =====');

        $tenantSlug = $request->route('slug') ?? $request->route('tenant');

        \Log::info('Slug recebido na rota', ['slug' => $tenantSlug]);

        $tenant = Tenant::where('subdomain', $tenantSlug)->first();

        \Log::info('Tenant encontrado?', [
            'exists' => (bool)$tenant,
            'tenant' => $tenant?->toArray()
        ]);

        if (!$tenant) {
            return back()->withErrors(['email' => 'Tenant inválido.']);
        }

        // 🔧 IMPORTANTE: Ativar o tenant ANTES de buscar o usuário
        // Isso garante que a conexão do banco de dados do tenant esteja configurada
        $tenant->makeCurrent();
        \Log::info('Tenant ativado antes da busca do usuário', ['tenant_id' => $tenant->id]);

        // Verificar se a conexão do tenant está funcionando
        try {
            DB::connection('tenant')->getPdo();
            \Log::info('✅ Conexão com banco do tenant verificada', [
                'db_name' => config('database.connections.tenant.database'),
                'db_host' => config('database.connections.tenant.host')
            ]);
            
            // Verificar se a tabela users existe
            $tableExists = DB::connection('tenant')->selectOne(
                "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users')"
            );
            
            if (!$tableExists || !$tableExists->exists) {
                \Log::error('❌ Tabela users não existe no banco do tenant', [
                    'tenant_id' => $tenant->id,
                    'db_name' => $tenant->db_name
                ]);
                return back()->withErrors(['email' => 'Banco de dados do tenant não está configurado corretamente. As migrações precisam ser executadas.']);
            }
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao conectar no banco do tenant', [
                'erro' => $e->getMessage(),
                'tenant_id' => $tenant->id,
                'db_name' => $tenant->db_name,
                'config' => config('database.connections.tenant')
            ]);
            return back()->withErrors(['email' => 'Erro ao conectar no banco de dados do tenant.']);
        }

        Auth::shouldUse('tenant');
        \Log::info('Guard forçado para tenant');

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        \Log::info("Credenciais", ['email' => $credentials['email']]);

        // Agora que o tenant está ativo, podemos buscar o usuário na conexão correta
        // Usar explicitamente a conexão 'tenant' para garantir
        try {
            $user = \App\Models\Tenant\User::on('tenant')->where('email', $credentials['email'])->first();
        } catch (\Exception $e) {
            \Log::error('❌ Erro ao buscar usuário no banco do tenant', [
                'erro' => $e->getMessage(),
                'email' => $credentials['email'],
                'connection' => config('database.connections.tenant')
            ]);
            return back()->withErrors(['email' => 'Erro ao acessar banco de dados. Verifique se as migrações foram executadas.']);
        }

        \Log::info("Usuário encontrado?", [
            'exists' => (bool)$user,
            'user' => $user?->toArray()
        ]);

        if (!$user) {
            return back()->withErrors(['email' => 'Usuário não encontrado nesta clínica.']);
        }

        if ($user->status !== 'active') {
            return back()->withErrors(['email' => 'Usuário desativado.']);
        }

        $attempt = Auth::guard('tenant')->attempt($credentials, $request->boolean('remember'));

        \Log::info('Tentativa de login', ['success' => $attempt]);

        if ($attempt) {

            \Log::info('Login OK — tenant já está ativo', [
                'tenant_id' => $tenant->id
            ]);

            // Tenant já foi ativado anteriormente, apenas garantimos a sessão
            session(['tenant_slug' => $tenant->subdomain]);

            $request->session()->regenerate();

            \Log::info('Redirecionando ao dashboard');

            return redirect()->route('tenant.dashboard', ['slug' => $tenant->subdomain]);
        }

        return back()->withErrors(['email' => 'Senha incorreta.']);
    }

    public function logout(Request $request)
    {
        $tenant = tenant();

        \Log::info("Logout requisitado", [
            'tenant_current' => $tenant?->id
        ]);

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('tenant.login', [
            'slug' => $tenant->subdomain ?? ''
        ]);
    }
}
