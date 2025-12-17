<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FinancialAccount;
use App\Http\Requests\Tenant\Finance\StoreAccountRequest;
use App\Http\Requests\Tenant\Finance\UpdateAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Lista todas as contas
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Apenas admin pode ver contas
        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem gerenciar contas.');
        }

        $accounts = FinancialAccount::orderBy('name')->paginate(20);

        return view('tenant.finance.accounts.index', compact('accounts'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        return view('tenant.finance.accounts.create');
    }

    /**
     * Cria nova conta
     */
    public function store(StoreAccountRequest $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        FinancialAccount::create($request->validated());

        return redirect()->route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Conta criada com sucesso.');
    }

    /**
     * Exibe detalhes da conta
     */
    public function show(FinancialAccount $account)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        $account->load('transactions');

        return view('tenant.finance.accounts.show', compact('account'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(FinancialAccount $account)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        return view('tenant.finance.accounts.edit', compact('account'));
    }

    /**
     * Atualiza conta
     */
    public function update(UpdateAccountRequest $request, FinancialAccount $account)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        $account->update($request->validated());

        return redirect()->route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Conta atualizada com sucesso.');
    }

    /**
     * Remove conta
     */
    public function destroy(FinancialAccount $account)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        // Verificar se há transações vinculadas
        if ($account->transactions()->count() > 0) {
            return redirect()->route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain])
                ->with('error', 'Não é possível excluir uma conta que possui transações vinculadas.');
        }

        $account->delete();

        return redirect()->route('tenant.finance.accounts.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Conta excluída com sucesso.');
    }
}

