<?php

namespace App\Http\Controllers\Tenant\Concerns;

use App\Models\Tenant\Doctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait HasDoctorFilter
{
    /**
     * Aplica filtro de médico em uma query baseado no role do usuário
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $doctorIdColumn Nome da coluna que contém o doctor_id (padrão: 'doctor_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDoctorFilter($query, $doctorIdColumn = 'doctor_id')
    {
        $user = Auth::guard('tenant')->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Não autenticado = sem acesso
        }

        // Admin vê tudo (sem filtro)
        if ($user->role === 'admin') {
            return $query;
        }

        // Detectar se estamos filtrando a tabela doctors
        // Se for, usar 'id' ao invés de 'doctor_id'
        $model = $query->getModel();
        $isDoctorsTable = ($model instanceof Doctor) || ($model->getTable() === 'doctors');
        
        if ($isDoctorsTable) {
            $doctorIdColumn = 'id';
        }

        // Médico só vê seus próprios dados
        if ($user->role === 'doctor') {
            // Buscar o doctor diretamente do banco para garantir que existe
            $doctor = Doctor::where('user_id', $user->id)->first();
            
            if ($doctor) {
                Log::info('Aplicando filtro de médico', [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'column' => $doctorIdColumn,
                    'table' => $model->getTable()
                ]);
                
                return $query->where($doctorIdColumn, $doctor->id);
            } else {
                Log::warning('Usuário com role doctor mas sem registro na tabela doctors', [
                    'user_id' => $user->id
                ]);
                return $query->whereRaw('1 = 0');
            }
        }

        // Usuário comum só vê médicos relacionados
        if ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            
            if (!empty($allowedDoctorIds)) {
                Log::info('Aplicando filtro de médicos permitidos', [
                    'user_id' => $user->id,
                    'allowed_doctor_ids' => $allowedDoctorIds,
                    'column' => $doctorIdColumn,
                    'table' => $model->getTable()
                ]);
                
                return $query->whereIn($doctorIdColumn, $allowedDoctorIds);
            } else {
                Log::info('Usuário comum sem médicos permitidos', [
                    'user_id' => $user->id
                ]);
                return $query->whereRaw('1 = 0');
            }
        }

        // Role desconhecido = sem acesso
        Log::warning('Role desconhecido ao aplicar filtro de médico', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        return $query->whereRaw('1 = 0');
    }

    /**
     * Aplica filtro de médico usando whereHas para relacionamentos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relation Nome do relacionamento (ex: 'calendar')
     * @param string $doctorIdColumn Nome da coluna que contém o doctor_id (padrão: 'doctor_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDoctorFilterWhereHas($query, $relation, $doctorIdColumn = 'doctor_id')
    {
        $user = Auth::guard('tenant')->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // Não autenticado = sem acesso
        }

        // Admin vê tudo (sem filtro)
        if ($user->role === 'admin') {
            return $query;
        }

        // Médico só vê seus próprios dados
        if ($user->role === 'doctor') {
            // Buscar o doctor diretamente do banco para garantir que existe
            $doctor = Doctor::where('user_id', $user->id)->first();
            
            if ($doctor) {
                Log::info('Aplicando filtro de médico com whereHas', [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'relation' => $relation,
                    'column' => $doctorIdColumn
                ]);
                
                return $query->whereHas($relation, function($q) use ($doctor, $doctorIdColumn) {
                    $q->where($doctorIdColumn, $doctor->id);
                });
            } else {
                Log::warning('Usuário com role doctor mas sem registro na tabela doctors', [
                    'user_id' => $user->id
                ]);
                return $query->whereRaw('1 = 0');
            }
        }

        // Usuário comum só vê médicos relacionados
        if ($user->role === 'user') {
            $allowedDoctorIds = $user->allowedDoctors()->pluck('doctors.id')->toArray();
            
            if (!empty($allowedDoctorIds)) {
                Log::info('Aplicando filtro de médicos permitidos com whereHas', [
                    'user_id' => $user->id,
                    'allowed_doctor_ids' => $allowedDoctorIds,
                    'relation' => $relation,
                    'column' => $doctorIdColumn
                ]);
                
                return $query->whereHas($relation, function($q) use ($allowedDoctorIds, $doctorIdColumn) {
                    $q->whereIn($doctorIdColumn, $allowedDoctorIds);
                });
            } else {
                Log::info('Usuário comum sem médicos permitidos', [
                    'user_id' => $user->id
                ]);
                return $query->whereRaw('1 = 0');
            }
        }

        // Role desconhecido = sem acesso
        Log::warning('Role desconhecido ao aplicar filtro de médico com whereHas', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        return $query->whereRaw('1 = 0');
    }

    /**
     * Retorna os IDs dos médicos que o usuário pode acessar
     * 
     * @return array
     */
    protected function getAllowedDoctorIds(): array
    {
        $user = Auth::guard('tenant')->user();
        
        if (!$user) {
            return [];
        }

        // Admin pode acessar todos
        if ($user->role === 'admin') {
            return Doctor::pluck('id')->toArray();
        }

        // Médico só pode acessar a si mesmo
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor ? [$doctor->id] : [];
        }

        // Usuário comum só pode acessar médicos relacionados
        if ($user->role === 'user') {
            return $user->allowedDoctors()->pluck('doctors.id')->toArray();
        }

        return [];
    }
}

