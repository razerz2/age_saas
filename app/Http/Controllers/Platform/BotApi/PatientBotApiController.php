<?php

namespace App\Http\Controllers\Platform\BotApi;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PatientBotApiController extends Controller
{
    /**
     * Criar paciente
     */
    public function create(Request $request)
    {
        $tenant = $request->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string',
            'email' => 'nullable|email',
            'cpf' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            $tenant->makeCurrent();
            
            return (function () use ($validated, $tenant) {
                $phoneClean = preg_replace('/[^0-9]/', '', $validated['phone']);
                
                // Verificar se já existe
                $patient = Patient::where('phone', $phoneClean)->first();

                if ($patient) {
                    // Atualizar dados se necessário
                    $updateData = [];
                    if (isset($validated['name']) && $validated['name'] !== $patient->full_name) {
                        $updateData['full_name'] = $validated['name'];
                    }
                    if (isset($validated['email']) && $validated['email'] !== $patient->email) {
                        $updateData['email'] = $validated['email'];
                    }
                    if (isset($validated['cpf']) && $validated['cpf'] !== $patient->cpf) {
                        $updateData['cpf'] = $validated['cpf'];
                    }
                    
                    if (!empty($updateData)) {
                        $patient->update($updateData);
                    }

                    Log::info('Bot API - Paciente encontrado (atualizado)', [
                        'tenant_id' => $tenant->id,
                        'patient_id' => $patient->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'patient_id' => $patient->id,
                        'message' => 'Paciente encontrado e atualizado.',
                        'patient' => [
                            'id' => $patient->id,
                            'name' => $patient->full_name,
                            'phone' => $patient->phone,
                            'email' => $patient->email,
                            'cpf' => $patient->cpf,
                        ]
                    ]);
                }

                // Criar novo paciente
                $patient = Patient::create([
                    'id' => Str::uuid(),
                    'full_name' => $validated['name'],
                    'phone' => $phoneClean,
                    'email' => $validated['email'] ?? null,
                    'cpf' => $validated['cpf'] ?? null,
                    'is_active' => true,
                ]);

                Log::info('Bot API - Paciente criado', [
                    'tenant_id' => $tenant->id,
                    'patient_id' => $patient->id,
                ]);

                return response()->json([
                    'success' => true,
                    'patient_id' => $patient->id,
                    'message' => 'Paciente criado com sucesso.',
                    'patient' => [
                        'id' => $patient->id,
                        'name' => $patient->full_name,
                        'phone' => $patient->phone,
                        'email' => $patient->email,
                        'cpf' => $patient->cpf,
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao criar paciente', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar paciente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar paciente por telefone
     */
    public function byPhone($phone)
    {
        $tenant = request()->attributes->get('bot_api_tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'error' => 'Tenant não identificado'
            ], 500);
        }

        try {
            $tenant->makeCurrent();
            
            return (function () use ($phone, $tenant) {
                $phoneClean = preg_replace('/[^0-9]/', '', $phone);
                $patient = Patient::where('phone', $phoneClean)->first();

                if (!$patient) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Paciente não encontrado'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'patient' => [
                        'id' => $patient->id,
                        'name' => $patient->full_name,
                        'phone' => $patient->phone,
                        'email' => $patient->email,
                        'cpf' => $patient->cpf,
                        'birth_date' => $patient->birth_date ? $patient->birth_date->format('Y-m-d') : null,
                        'is_active' => $patient->is_active,
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Bot API - Erro ao buscar paciente', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar paciente: ' . $e->getMessage()
            ], 500);
        }
    }
}
