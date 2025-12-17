<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use App\Models\Tenant\FinancialCategory;
use App\Http\Requests\Tenant\Finance\StoreCategoryRequest;
use App\Http\Requests\Tenant\Finance\UpdateCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Lista todas as categorias
     */
    public function index()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Apenas admin pode ver categorias
        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado. Apenas administradores podem gerenciar categorias.');
        }

        $categories = FinancialCategory::orderBy('type')->orderBy('name')->paginate(20);

        return view('tenant.finance.categories.index', compact('categories'));
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

        return view('tenant.finance.categories.create');
    }

    /**
     * Cria nova categoria
     */
    public function store(StoreCategoryRequest $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        FinancialCategory::create($request->validated());

        return redirect()->route('tenant.finance.categories.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Categoria criada com sucesso.');
    }

    /**
     * Exibe detalhes da categoria
     */
    public function show(FinancialCategory $category)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        $category->load('transactions');

        return view('tenant.finance.categories.show', compact('category'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(FinancialCategory $category)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        return view('tenant.finance.categories.edit', compact('category'));
    }

    /**
     * Atualiza categoria
     */
    public function update(UpdateCategoryRequest $request, FinancialCategory $category)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        $category->update($request->validated());

        return redirect()->route('tenant.finance.categories.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Categoria atualizada com sucesso.');
    }

    /**
     * Remove categoria
     */
    public function destroy(FinancialCategory $category)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        if ($user->role !== 'admin') {
            abort(403, 'Acesso negado.');
        }

        // Verificar se há transações vinculadas
        if ($category->transactions()->count() > 0) {
            return redirect()->route('tenant.finance.categories.index', ['slug' => tenant()->subdomain])
                ->with('error', 'Não é possível excluir uma categoria que possui transações vinculadas.');
        }

        $category->delete();

        return redirect()->route('tenant.finance.categories.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Categoria excluída com sucesso.');
    }
}

