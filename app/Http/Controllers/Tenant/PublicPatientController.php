<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\IdentifyPatientRequest;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PublicPatientController extends Controller
{
    /**
     * Exibe o formulário de identificação do paciente
     */
    public function showIdentify(Request $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        return view('tenant.public.patient-identify', [
            'tenant' => $tenantModel
        ]);
    }

    /**
     * Processa a identificação do paciente
     */
    public function identify(IdentifyPatientRequest $request, $tenant)
    {
        $tenantSlug = $tenant;
        $tenantModel = Tenant::where('subdomain', $tenantSlug)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        $identifier = trim($request->input('identifier'));
        
        \Log::info('🔍 Buscando paciente', [
            'tenant' => $tenantSlug,
            'identifier' => $identifier
        ]);
        
        // Remove formatação do CPF para verificar se é CPF
        $cpfClean = preg_replace('/\D/', '', $identifier);
        
        // Verifica se é um email válido
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        
        // Busca paciente por CPF ou Email
        $patient = null;
        
        if (strlen($cpfClean) === 11) {
            // Busca por CPF considerando tanto formato com quanto sem formatação
            // Formata o CPF para o formato padrão (000.000.000-00)
            $cpfFormatted = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfClean);
            
            // Busca usando OR para considerar ambos os formatos (com e sem formatação)
            // Também considera o valor original digitado pelo usuário
            $patient = Patient::where(function($query) use ($cpfClean, $cpfFormatted, $identifier) {
                    $query->where('cpf', $cpfClean)  // CPF sem formatação
                          ->orWhere('cpf', $cpfFormatted)  // CPF formatado (000.000.000-00)
                          ->orWhere('cpf', $identifier);  // CPF exatamente como foi digitado
                })
                ->where('is_active', true)
                ->first();
            
            // Se ainda não encontrou, tenta normalizar o CPF do banco usando função SQL
            // Isso é útil quando o CPF no banco está em um formato diferente
            if (!$patient) {
                // Tenta buscar usando REPLACE para remover formatação (compatível com PostgreSQL e MySQL)
                $connection = \DB::connection('tenant')->getDriverName();
                
                if ($connection === 'pgsql') {
                    // PostgreSQL: Remove todos os caracteres não numéricos
                    $patient = Patient::whereRaw('REGEXP_REPLACE(cpf, \'[^0-9]\', \'\', \'g\') = ?', [$cpfClean])
                        ->where('is_active', true)
                        ->first();
                } elseif ($connection === 'mysql') {
                    // MySQL: Remove pontos e hífen
                    $patient = Patient::whereRaw('REPLACE(REPLACE(cpf, \'.\', \'\'), \'-\', \'\') = ?', [$cpfClean])
                        ->where('is_active', true)
                        ->first();
                }
                
                // Última tentativa: busca em memória comparando CPFs normalizados
                // Isso garante encontrar o paciente independentemente do formato
                if (!$patient) {
                    $allPatients = Patient::where('is_active', true)->get();
                    
                    foreach ($allPatients as $p) {
                        $dbCpfClean = preg_replace('/\D/', '', $p->cpf);
                        if ($dbCpfClean === $cpfClean) {
                            $patient = $p;
                            break;
                        }
                    }
                }
            }
        }
        
        // Se não encontrou por CPF e parece ser email, busca por email
        if (!$patient && $isEmail) {
            $patient = Patient::where('email', $identifier)
                ->where('is_active', true)
                ->first();
        }

        // Se paciente não existe, retorna com erro
        if (!$patient) {
            \Log::warning('❌ Paciente não encontrado', [
                'tenant' => $tenantSlug,
                'identifier' => $identifier,
                'cpf_clean' => $cpfClean ?? null,
                'is_email' => $isEmail ?? false
            ]);
            
            return back()
                ->withErrors([
                    'identifier' => 'Você ainda não possui cadastro na clínica.'
                ])
                ->with('patient_not_found', true)
                ->withInput();
        }
        
        \Log::info('✅ Paciente encontrado', [
            'tenant' => $tenantSlug,
            'patient_id' => $patient->id,
            'patient_name' => $patient->full_name,
            'cpf' => $patient->cpf
        ]);

        // Salva o ID do paciente na sessão para uso no fluxo de agendamento
        Session::put('public_patient_id', $patient->id);
        Session::put('public_patient_name', $patient->full_name);

        // Redireciona para o fluxo de agendamento público
        return redirect()->route('public.appointment.create', ['tenant' => $tenant])
            ->with('success', 'Paciente identificado com sucesso!');
    }
}

