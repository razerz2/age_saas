<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StorePublicPatientRequest;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPatientRegisterController extends Controller
{
    /**
     * Exibe o formulário de cadastro de paciente público
     */
    public function showRegister(Request $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        return view('tenant.public.patient-register', [
            'tenant' => $tenantModel
        ]);
    }

    /**
     * Processa o cadastro de paciente público
     */
    public function register(StorePublicPatientRequest $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        $data = $request->validated();
        
        // Remove formatação do CPF para armazenar apenas números
        if (isset($data['cpf'])) {
            $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
        }

        // Remove formatação do telefone
        if (isset($data['phone'])) {
            $data['phone'] = preg_replace('/\D/', '', $data['phone']);
        }

        // Define valores padrão
        $data['id'] = Str::uuid();
        $data['is_active'] = true; // Pacientes públicos sempre são criados como ativos

        Patient::create($data);

        return redirect()->route('public.patient.identify', ['tenant' => $tenantSlug])
            ->with('success', 'Cadastro realizado com sucesso! Agora você já pode realizar seu agendamento.');
    }
}

