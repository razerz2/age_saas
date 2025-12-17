<?php

namespace App\Http\Controllers\Tenant\Finance;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\FinancialTransaction;
use App\Models\Tenant\FinancialAccount;
use App\Models\Tenant\FinancialCategory;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Doctor;
use App\Models\Tenant\Appointment;
use App\Http\Requests\Tenant\Finance\StoreTransactionRequest;
use App\Http\Requests\Tenant\Finance\UpdateTransactionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialTransactionController extends Controller
{
    use HasDoctorFilter;

    public function __construct()
    {
        $this->middleware(['tenant.auth', 'module.access:finance']);
    }

    /**
     * Lista todas as transações
     */
    public function index(Request $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $query = FinancialTransaction::with(['account', 'category', 'patient', 'doctor', 'appointment']);

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        // Filtros opcionais
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(30);

        $accounts = FinancialAccount::where('active', true)->orderBy('name')->get();
        $categories = FinancialCategory::where('active', true)->orderBy('name')->get();

        return view('tenant.finance.transactions.index', compact('transactions', 'accounts', 'categories'));
    }

    /**
     * Exibe formulário de criação
     */
    public function create()
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $accounts = FinancialAccount::where('active', true)->orderBy('name')->get();
        $categories = FinancialCategory::where('active', true)->orderBy('name')->get();
        $patients = Patient::where('is_active', true)->orderBy('full_name')->get();
        
        // Filtrar médicos conforme role
        $doctorsQuery = Doctor::with('user')->whereHas('user', function($q) {
            $q->where('status', 'active');
        });
        $this->applyDoctorFilter($doctorsQuery);
        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.finance.transactions.create', compact('accounts', 'categories', 'patients', 'doctors'));
    }

    /**
     * Cria nova transação
     */
    public function store(StoreTransactionRequest $request)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        $data = $request->validated();
        $data['created_by'] = $user->id;
        $data['status'] = $data['status'] ?? 'pending';

        FinancialTransaction::create($data);

        return redirect()->route('tenant.finance.transactions.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Transação criada com sucesso.');
    }

    /**
     * Exibe detalhes da transação
     */
    public function show(FinancialTransaction $transaction)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Verificar acesso por role
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor || $transaction->doctor_id !== $doctor->id) {
                abort(403, 'Acesso negado.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!in_array($transaction->doctor_id, $allowedDoctorIds)) {
                abort(403, 'Acesso negado.');
            }
        }

        $transaction->load(['account', 'category', 'patient', 'doctor', 'appointment', 'creator', 'commission']);

        return view('tenant.finance.transactions.show', compact('transaction'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(FinancialTransaction $transaction)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Verificar acesso por role
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor || $transaction->doctor_id !== $doctor->id) {
                abort(403, 'Acesso negado.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!in_array($transaction->doctor_id, $allowedDoctorIds)) {
                abort(403, 'Acesso negado.');
            }
        }

        $accounts = FinancialAccount::where('active', true)->orderBy('name')->get();
        $categories = FinancialCategory::where('active', true)->orderBy('name')->get();
        $patients = Patient::where('is_active', true)->orderBy('full_name')->get();
        
        $doctorsQuery = Doctor::with('user')->whereHas('user', function($q) {
            $q->where('status', 'active');
        });
        $this->applyDoctorFilter($doctorsQuery);
        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.finance.transactions.edit', compact('transaction', 'accounts', 'categories', 'patients', 'doctors'));
    }

    /**
     * Atualiza transação
     */
    public function update(UpdateTransactionRequest $request, FinancialTransaction $transaction)
    {
        if (tenant_setting('finance.enabled') !== 'true') {
            abort(403, 'Módulo financeiro não está habilitado.');
        }

        $user = Auth::guard('tenant')->user();

        // Verificar acesso por role
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor || $transaction->doctor_id !== $doctor->id) {
                abort(403, 'Acesso negado.');
            }
        } elseif ($user->role === 'user') {
            $allowedDoctorIds = $this->getAllowedDoctorIds();
            if (!in_array($transaction->doctor_id, $allowedDoctorIds)) {
                abort(403, 'Acesso negado.');
            }
        }

        $transaction->update($request->validated());

        return redirect()->route('tenant.finance.transactions.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Transação atualizada com sucesso.');
    }
}

