<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
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

        // Se o tenant não existir, retornar a view com mensagem de erro
        if (!$tenant) {
            \Log::warning("⚠️ Tenant não encontrado", ['slug' => $slug]);
            return view('tenant.auth.login', [
                'tenant' => null,
                'error_message' => 'A clínica informada não existe ou não está disponível. Verifique o endereço e tente novamente.'
            ]);
        }

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
        
        // Garantir que a conexão do tenant está configurada com as credenciais corretas
        $this->configureTenantDatabaseConnection($tenant);

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
            $authenticatedUser = Auth::guard('tenant')->user();

            // Verifica se o usuário tem 2FA habilitado
            if ($authenticatedUser && $authenticatedUser->hasTwoFactorEnabled()) {
                // Salva informações temporárias na sessão
                session([
                    'login.id' => $authenticatedUser->id,
                    'login.remember' => $request->boolean('remember'),
                    'login.tenant_id' => $tenant->id
                ]);
                
                // Faz logout temporário
                Auth::guard('tenant')->logout();
                
                \Log::info('2FA habilitado, redirecionando para verificação', [
                    'user_id' => $authenticatedUser->id
                ]);
                
                // Redireciona para verificação do código 2FA
                return redirect()->route('tenant.two-factor.challenge', ['slug' => $tenant->subdomain]);
            }

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

    /**
     * Configura a conexão com o banco de dados do tenant
     */
    protected function configureTenantDatabaseConnection(Tenant $tenant)
    {
        \Log::info("🔧 Configurando conexão de banco de dados do tenant no LoginController", [
            'host' => $tenant->db_host,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username,
            'password_set' => !empty($tenant->db_password)
        ]);

        // Usar host e porta do tenant, com fallback para .env se não estiver definido
        $dbHost = $tenant->db_host ?: env('DB_TENANT_HOST', '127.0.0.1');
        $dbPort = $tenant->db_port ?: env('DB_TENANT_PORT', '5432');

        // Configura dinamicamente os detalhes do banco de dados
        Config::set('database.connections.tenant.host', $dbHost);
        Config::set('database.connections.tenant.port', $dbPort);
        Config::set('database.connections.tenant.database', $tenant->db_name);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password ?? '');

        // Recarrega a conexão do banco de dados com as novas configurações
        DB::purge('tenant');  // Limpa a conexão existente
        DB::reconnect('tenant'); // Reconnecta com as novas configurações
    }
}
