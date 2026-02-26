<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Doctor;
use App\Models\Tenant\User;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Module;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Http\Requests\Tenant\ChangePasswordUserRequest;
use App\Traits\HasFeatureAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HasFeatureAccess;
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('tenant.users.index', [
            'users' => $users,
            'doctorsOnly' => false,
        ]);
    }

    public function doctorsIndex()
    {
        $query = User::query();
        $this->applyDoctorUsersFilter($query);

        $users = $query->orderBy('name')->paginate(15);

        return view('tenant.users.index', [
            'users' => $users,
            'doctorsOnly' => true,
        ]);
    }

    public function create()
    {
        return view('tenant.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $doctorData = $data['doctor'] ?? [];
        unset($data['doctor']);
        
        // Garante que role tenha um valor padrão
        $data['role'] = $data['role'] ?? 'user';

        // Verifica os limites do plano antes de criar o usuário
        $role = $data['role'];
        $limitType = match ($role) {
            'admin' => 'max_admin_users',
            'user' => 'max_common_users',
            'doctor' => 'max_doctors',
            default => null,
        };

        if ($limitType) {
            $maxLimit = $this->getPlanLimit($limitType);
            
            if ($maxLimit !== null) {
                // Conta quantos usuários já existem com este role
                $usersQuery = User::where('role', $role);

                // Tenants antigos podem ainda nÃ£o ter a coluna `is_system` aplicada.
                if (Schema::connection((new User())->getConnectionName())->hasColumn('users', 'is_system')) {
                    $usersQuery->where(function ($query) {
                        $query->whereNull('is_system')->orWhere('is_system', false);
                    });
                }

                $currentCount = $usersQuery->count();
                
                if ($currentCount >= $maxLimit) {
                    $roleLabel = match ($role) {
                        'admin' => 'administradores',
                        'user' => 'usuários comuns',
                        'doctor' => 'médicos',
                        default => 'usuários',
                    };
                    
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', "Limite de {$maxLimit} {$roleLabel} atingido no seu plano. Faça upgrade para adicionar mais usuários.");
                }
            }
        }
        
        // Se a senha não foi informada, gera uma senha aleatória
        if (empty($data['password'])) {
            $data['password'] = Str::random(12);
        }
        
        $data['password'] = Hash::make($data['password']);
        
        // Remove password_confirmation dos dados antes de salvar
        unset($data['password_confirmation']);

        // Verifica o role do usuário logado
        $loggedUser = Auth::guard('tenant')->user();

        // Processar módulos: se foram selecionados manualmente, usar esses
        // Caso contrário, aplicar módulos padrão baseado no role
        // O model User tem cast 'array' para modules, então passamos arrays diretamente
        if ($request->has('modules_present') && !isset($data['modules'])) {
            $data['modules'] = [];
        }

        if (array_key_exists('modules', $data) && is_array($data['modules'])) {
            // Se módulos foram selecionados manualmente (mesmo que vazio), usar esses
            // O cast do model converte automaticamente para JSON
            $data['modules'] = $data['modules'];
        } else {
            // Se não foram selecionados, aplicar padrões baseado no role
            if ($data['role'] === 'admin') {
                // Admin não precisa de módulos (tem acesso total)
                unset($data['modules']);
            } elseif ($data['role'] === 'doctor') {
                // Aplicar módulos padrão para médico
                $defaultDoctorModules = json_decode(TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                $data['modules'] = $defaultDoctorModules;
            } elseif ($data['role'] === 'user') {
                // Aplicar módulos padrão para usuário comum
                $defaultCommonModules = json_decode(TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                $data['modules'] = $defaultCommonModules;
            } else {
                // Fallback: garantir que modules seja um array vazio
                $data['modules'] = [];
            }
        }

        // Upload do avatar se fornecido
        if ($data['role'] === 'admin') {
            $data['modules'] = [];
        } else {
            $data['modules'] = $this->sanitizeModulesForRole($data['role'], $data['modules'] ?? []);
        }

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = 'avatars/' . time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public', $avatarName);
            $data['avatar'] = $avatarName;
        }

        // Extrair doctor_ids antes de criar o usuário
        // Se o usuário logado é médico ou admin, ignora doctor_ids
        $doctorIds = [];
        if ($loggedUser && $loggedUser->role !== 'doctor' && $loggedUser->role !== 'admin') {
            $doctorIds = $request->input('doctor_ids', []);
        }
        unset($data['doctor_ids']);

        // Garantir que todo novo usuário esteja ativo por padrão
        $data['status'] = 'active';
        $data['is_doctor'] = $data['role'] === 'doctor' ? true : (bool) ($data['is_doctor'] ?? false);

        $user = null;
        DB::transaction(function () use (&$user, $data, $doctorData, $doctorIds) {
            $user = User::create($data);

            if ($user->role === 'doctor') {
                $this->createDoctorForUser($user, $doctorData);
            }

            // Se for role 'user', salvar permissões de médicos
            if ($user->role === 'user' && !empty($doctorIds)) {
                foreach ($doctorIds as $docId) {
                    \App\Models\Tenant\UserDoctorPermission::create([
                        'user_id' => $user->id,
                        'doctor_id' => $docId,
                    ]);
                }
            }
        });

        return redirect()->route('tenant.users.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Usuário criado com sucesso.');
    }
    /**
     * Cria e vincula o registro de médico ao usuário recém-criado.
     */
    protected function createDoctorForUser(User $user, array $doctorData): Doctor
    {
        $doctor = Doctor::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'crm_number' => $doctorData['crm_number'] ?? null,
            'crm_state' => $doctorData['crm_state'] ?? null,
            'signature' => $doctorData['signature'] ?? null,
            'label_singular' => $doctorData['label_singular'] ?? null,
            'label_plural' => $doctorData['label_plural'] ?? null,
            'registration_label' => $doctorData['registration_label'] ?? null,
            'registration_value' => $doctorData['registration_value'] ?? null,
        ]);

        $specialties = collect($doctorData['specialties'] ?? [])
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->unique()
            ->values()
            ->all();

        if (!empty($specialties)) {
            $doctor->specialties()->sync($specialties);
        }

        return $doctor;
    }

    protected function sanitizeModulesForRole(string $role, array $modules): array
    {
        if ($role === 'admin') {
            return [];
        }

        $allowedKeys = collect(Module::available())
            ->pluck('key')
            ->reject(fn ($key) => in_array($key, ['settings', 'users'], true))
            ->values()
            ->all();

        $modules = array_values(array_filter($modules, fn ($value) => is_string($value) && $value !== ''));
        $modules = array_values(array_unique($modules));

        return array_values(array_intersect($modules, $allowedKeys));
    }

    public function show($slug, $id)
    {
        $user = User::with(['allowedDoctors.user'])->findOrFail($id);  // Utilizando o ID passado na rota

        return view('tenant.users.show', compact('user'));
    }

    /**
     * Exibe o formulário de edição do usuário.
     * Agora usando o ID explícito.
     */
    public function edit($slug, $id)
    {
        $user = User::findOrFail($id);  // Utilizando o ID passado na rota

        return view('tenant.users.edit', compact('user'));
    }

    /**
     * Atualiza os dados do usuário.
     * Agora usando o ID explícito.
     */
    public function update(UpdateUserRequest $request, $slug, $id)
    {
        $user = User::findOrFail($id);

        // Valida os dados da requisição
        $data = $request->validated();

        // Verifica o role do usuário logado
        $loggedUser = Auth::guard('tenant')->user();

        // Garante que role tenha um valor padrão
        if (!isset($data['role'])) {
            $data['role'] = $user->role ?? 'user';
        }

        // Processar módulos: se foram selecionados manualmente, usar esses
        // Caso contrário, aplicar módulos padrão baseado no role
        // O model User tem cast 'array' para modules, então passamos arrays diretamente
        if ($request->has('modules_present') && !isset($data['modules'])) {
            $data['modules'] = [];
        }

        if (array_key_exists('modules', $data) && is_array($data['modules'])) {
            // Se módulos foram selecionados manualmente (mesmo que vazio), usar esses
            // O cast do model converte automaticamente para JSON
            $data['modules'] = $data['modules'];
        } else {
            // Se não foram selecionados, aplicar padrões baseado no role (apenas se role mudou)
            $newRole = $data['role'];
            $oldRole = $user->role;
            
            // Se o role mudou, aplicar módulos padrão do novo role
            if ($newRole !== $oldRole) {
                if ($newRole === 'admin') {
                    // Admin não precisa de módulos (tem acesso total)
                    unset($data['modules']);
                } elseif ($newRole === 'doctor') {
                    // Aplicar módulos padrão para médico
                    $defaultDoctorModules = json_decode(TenantSetting::get('user_defaults.modules_doctor', '[]'), true) ?? [];
                    $data['modules'] = $defaultDoctorModules;
                } elseif ($newRole === 'user') {
                    // Aplicar módulos padrão para usuário comum
                    $defaultCommonModules = json_decode(TenantSetting::get('user_defaults.modules_common_user', '[]'), true) ?? [];
                    $data['modules'] = $defaultCommonModules;
                }
            }
            // Se o role não mudou e não foram selecionados módulos, manter os existentes
        }

        // Upload do novo avatar se fornecido
        if ($data['role'] === 'admin') {
            $data['modules'] = [];
        } elseif (array_key_exists('modules', $data)) {
            $data['modules'] = $this->sanitizeModulesForRole($data['role'], $data['modules'] ?? []);
        }

        if ($request->hasFile('avatar')) {
            // Remove avatar antigo se existir
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatar = $request->file('avatar');
            $avatarName = 'avatars/' . time() . '_' . Str::random(10) . '.' . $avatar->getClientOriginalExtension();
            $avatar->storeAs('public', $avatarName);
            $data['avatar'] = $avatarName;
        }

        // Extrair doctor_ids e doctor_id antes de atualizar o usuário
        // Se o usuário logado é médico ou admin, ignora doctor_ids
        $doctorIds = [];
        if ($loggedUser && $loggedUser->role !== 'doctor' && $loggedUser->role !== 'admin') {
            $doctorIds = $request->input('doctor_ids', []);
        }
        $doctorId = $request->input('doctor_id');
        unset($data['doctor_ids'], $data['doctor_id']);

        // Atualiza os dados do usuário (sem a senha)
        $user->update($data);

        // Atualizar permissões de médicos se for role 'user'
        if ($user->role === 'user') {
            // Remove todas as permissões existentes
            $user->doctorPermissions()->delete();
            // Adiciona as novas permissões
            if (!empty($doctorIds)) {
                foreach ($doctorIds as $docId) {
                    \App\Models\Tenant\UserDoctorPermission::create([
                        'user_id' => $user->id,
                        'doctor_id' => $docId,
                    ]);
                }
            }
        }

        // Se for role 'doctor', vincular ao médico selecionado
        if ($user->role === 'doctor' && $doctorId) {
            $doctor = \App\Models\Tenant\Doctor::find($doctorId);
            if ($doctor) {
                $doctor->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('tenant.users.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Usuário atualizado com sucesso!');
    }


    /**
     * Remove o usuário.
     * Agora usando o ID explícito.
     * Valida se não há registros vinculados antes de excluir.
     */
    public function destroy($slug, $id)
    {
        $user = User::findOrFail($id);

        // Verifica se há registros vinculados ao usuário
        $relatedRecords = $this->checkRelatedRecords($user);

        if (!empty($relatedRecords)) {
            $message = "Não é possível excluir o usuário porque existem registros vinculados:<br><br>";
            $message .= implode("<br>", $relatedRecords);
            $message .= "<br><br>Remova ou transfira esses registros antes de excluir o usuário.";

            return redirect()
                ->route('tenant.users.index', ['slug' => tenant()->subdomain])
                ->with('error', $message);
        }

        $user->delete();

        return redirect()
            ->route('tenant.users.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Usuário removido com sucesso.');
    }

    /**
     * Verifica se o usuário possui registros vinculados
     *
     * @param User $user
     * @return array Array com mensagens sobre registros vinculados encontrados
     */
    protected function checkRelatedRecords(User $user): array
    {
        $relatedRecords = [];

        // Verifica se é médico e tem registros vinculados
        if ($user->role === 'doctor' || $user->is_doctor) {
            $doctor = $user->doctor;

            if ($doctor) {
                // Verifica se tem calendários
                $calendarsCount = $doctor->calendars()->count();
                if ($calendarsCount > 0) {
                    $relatedRecords[] = "• {$calendarsCount} calendário(s) vinculado(s)";
                }

                // Verifica se tem agendamentos
                $appointmentsCount = $doctor->appointments()->count();
                if ($appointmentsCount > 0) {
                    $relatedRecords[] = "• {$appointmentsCount} agendamento(s) vinculado(s)";
                }

                // Verifica se tem horários comerciais
                $businessHoursCount = $doctor->businessHours()->count();
                if ($businessHoursCount > 0) {
                    $relatedRecords[] = "• {$businessHoursCount} horário(s) comercial(is) vinculado(s)";
                }

                // Verifica se tem tipos de atendimento
                $appointmentTypesCount = $doctor->appointmentTypes()->count();
                if ($appointmentTypesCount > 0) {
                    $relatedRecords[] = "• {$appointmentTypesCount} tipo(s) de atendimento vinculado(s)";
                }

                // Verifica se tem formulários
                $formsCount = $doctor->forms()->count();
                if ($formsCount > 0) {
                    $relatedRecords[] = "• {$formsCount} formulário(s) vinculado(s)";
                }

                // Verifica se tem transações financeiras
                $transactionsCount = \App\Models\Tenant\FinancialTransaction::where('doctor_id', $doctor->id)->count();
                if ($transactionsCount > 0) {
                    $relatedRecords[] = "• {$transactionsCount} transação(ões) financeira(s) vinculada(s)";
                }
            }
        }

        // Verifica transações financeiras criadas pelo usuário
        $createdTransactionsCount = \App\Models\Tenant\FinancialTransaction::where('created_by', $user->id)->count();
        if ($createdTransactionsCount > 0) {
            $relatedRecords[] = "• {$createdTransactionsCount} transação(ões) financeira(s) criada(s) por este usuário";
        }

        // Verifica contas OAuth vinculadas
        $oauthAccountsCount = \App\Models\Tenant\OauthAccount::where('user_id', $user->id)->count();
        if ($oauthAccountsCount > 0) {
            $relatedRecords[] = "• {$oauthAccountsCount} conta(s) OAuth vinculada(s)";
        }

        // Verifica códigos de autenticação de dois fatores
        $twoFactorCodesCount = \App\Models\Tenant\TwoFactorCode::where('user_id', $user->id)->count();
        if ($twoFactorCodesCount > 0) {
            // Códigos 2FA podem ser excluídos, mas vamos informar
            // Na verdade, esses podem ser excluídos automaticamente, então não vamos bloquear
        }

        return $relatedRecords;
    }

    public function showChangePasswordForm($slug, $id)
    {
        // Recupera o usuário pelo ID
        $user = User::findOrFail($id);

        // Retorna a view, passando o usuário
        return view('tenant.users.change-password', compact('user'));
    }

    public function changePassword(ChangePasswordUserRequest $request, $slug, $id)
    {
        // Valida os dados com a ChangePasswordRequest
        $validated = $request->validated();

        // Recupera o usuário pelo ID
        $user = User::findOrFail($id);

        // Verifica se a senha atual está correta
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        // Atualiza a senha
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Redireciona com sucesso
        return redirect()->route('tenant.users.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Retorna dados para Grid.js na tela de usuários.
     */
    public function gridData(Request $request, $slug)
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $query = User::query();
        if ($this->shouldFilterDoctorsOnly($request)) {
            $this->applyDoctorUsersFilter($query);
        }

        // Busca simples em nome e email
        $search = $request->input('search');
        if (is_array($search) && !empty($search['value'])) {
            $term = trim($search['value']);
            $query->where(function ($q) use ($term) {
                $q->where('name_full', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Ordenação básica
        $sort = $request->input('sort');
        if (is_array($sort) && isset($sort['column'], $sort['direction'])) {
            $column = $sort['column'];
            $direction = strtolower($sort['direction']) === 'asc' ? 'asc' : 'desc';

            // Permitir ordenar apenas por colunas conhecidas
            $sortable = [
                'name_full' => 'name_full',
                'email' => 'email',
                'role_label' => 'role',
            ];

            if (isset($sortable[$column])) {
                $query->orderBy($sortable[$column], $direction);
            } else {
                $query->orderBy('name_full');
            }
        } else {
            $query->orderBy('name_full');
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $rows = $paginator->items();

        $data = [];
        foreach ($rows as $user) {
            // Badge de status (HTML)
            if ($user->status === 'active') {
                $statusBadge =
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">' .
                        '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">' .
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>' .
                        '</svg>' .
                        'Ativo' .
                    '</span>';
            } else {
                $statusBadge =
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">' .
                        '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">' .
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>' .
                        '</svg>' .
                        'Bloqueado' .
                    '</span>';
            }

            $roleLabel = match ($user->role) {
                'admin'  => 'Administrador',
                'doctor' => 'Médico',
                'user'   => 'Usuário',
                default  => ucfirst($user->role ?? 'Indefinido'),
            };

            // HTML das ações (inline, compatível com gridjs.html)
            $showUrl           = workspace_route('tenant.users.show', $user->id);
            $editUrl           = workspace_route('tenant.users.edit', $user->id);
            $changePasswordUrl = workspace_route('tenant.users.change-password', $user->id);
            $doctorPermUrl     = !$user->is_doctor
                ? workspace_route('tenant.users.doctor-permissions', $user->id)
                : null;
            $destroyUrl        = workspace_route('tenant.users.destroy', $user->id);

            $csrf   = csrf_field();
            $method = method_field('DELETE');

            $actions = '';

            // Ver
            $actions .=
                '<a href="' . e($showUrl) . '" ' .
                   'title="Ver" ' .
                   'onclick="event.stopPropagation()" ' .
                   'class="inline-flex items-center justify-center rounded-xl border border-transparent px-2.5 py-1.5 text-xs font-medium tenant-action-view table-action-btn">' .
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>' .
                    '</svg>' .
                '</a>';

            // Editar
            $actions .=
                '<a href="' . e($editUrl) . '" ' .
                   'title="Editar" ' .
                   'onclick="event.stopPropagation()" ' .
                   'class="inline-flex items-center justify-center rounded-xl border border-transparent px-2.5 py-1.5 text-xs font-medium tenant-action-edit table-action-btn">' .
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>' .
                    '</svg>' .
                '</a>';

            // Senha
            $actions .=
                '<a href="' . e($changePasswordUrl) . '" ' .
                   'title="Senha" ' .
                   'onclick="event.stopPropagation()" ' .
                   'class="inline-flex items-center justify-center rounded-xl border border-purple-100 bg-purple-50 px-2.5 py-1.5 text-xs font-medium text-purple-700 hover:bg-purple-100 dark:border-purple-900/40 dark:bg-purple-900/20 dark:text-purple-300 table-action-btn">' .
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>' .
                    '</svg>' .
                '</a>';

            // Permissões de médicos (se não for médico)
            if ($doctorPermUrl) {
                $actions .=
                    '<a href="' . e($doctorPermUrl) . '" ' .
                       'title="Gerenciar Permissões de Médicos" ' .
                       'onclick="event.stopPropagation()" ' .
                       'class="inline-flex items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 px-2.5 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100 dark:border-indigo-900/40 dark:bg-indigo-900/20 dark:text-indigo-300 table-action-btn">' .
                        '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>' .
                        '</svg>' .
                    '</a>';
            }

            // Excluir (form)
            $actions .=
                '<form id="user-delete-form-' . (int) $user->id . '" action="' . e($destroyUrl) . '" method="POST" class="inline-flex delete-user-form" ' .
                      'data-confirm-user-delete="true" data-user-name="' . e('o usuário ' . $user->name_full) . '">' .
                    $csrf .
                    $method .
                    '<button type="button" title="Excluir" data-delete-trigger="1" data-delete-form="#user-delete-form-' . (int) $user->id . '" ' .
                        'data-delete-title="Excluir usuário" data-delete-message="' . e('Tem certeza que deseja excluir o usuário ' . $user->name_full . '?') . '" onclick="event.stopPropagation()" ' .
                        'class="inline-flex items-center justify-center rounded-xl border border-transparent px-2.5 py-1.5 text-xs font-medium tenant-action-delete table-action-btn">' .
                        '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">' .
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>' .
                        '</svg>' .
                    '</button>' .
                '</form>';

            $data[] = [
                'name_full'    => e($user->name_full),
                'email'        => e($user->email),
                'role_label'   => e($roleLabel),
                'status_badge' => $statusBadge,
                'actions'      => $actions,
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function shouldFilterDoctorsOnly(Request $request): bool
    {
        return strtolower((string) $request->input('role', '')) === 'doctor';
    }

    protected function applyDoctorUsersFilter(Builder $query): void
    {
        $query->where(function (Builder $builder) {
            $builder->where('role', 'doctor')
                ->orWhere('is_doctor', true)
                ->orWhereHas('doctor');
        });
    }
}
