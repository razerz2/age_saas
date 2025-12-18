<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Patient;
use App\Models\Tenant\PatientLogin;
use App\Models\Tenant\Gender;
use App\Models\Tenant\PatientAddress;
use App\Http\Requests\Tenant\StorePatientRequest;
use App\Http\Requests\Tenant\UpdatePatientRequest;
use App\Mail\PatientLoginCredentials;
use App\Services\WhatsAppService;
use App\Services\WhatsappTenantService;
use App\Services\MailTenantService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Spatie\Multitenancy\Models\Tenant;

class PatientController extends Controller
{
    public function index()
    {
        try {
            // Verifica se a tabela existe antes de tentar carregar o relacionamento
            $tableExists = Schema::connection('tenant')->hasTable('patient_logins');
            
            if ($tableExists) {
                $patients = Patient::with(['login', 'gender'])
                    ->orderBy('full_name')
                    ->paginate(20);
            } else {
                // Se a tabela não existir, carrega pacientes sem o relacionamento
                $patients = Patient::with('gender')->orderBy('full_name')->paginate(20);
            }
        } catch (\Exception $e) {
            // Em caso de erro, carrega pacientes sem relacionamento
            $patients = Patient::with('gender')->orderBy('full_name')->paginate(20);
        }

        return view('tenant.patients.index', compact('patients'));
    }

    public function create()
    {
        try {
            // Verifica se a tabela existe antes de tentar buscar
            $tableExists = Schema::connection('tenant')->hasTable('genders');
            
            if ($tableExists) {
                $genders = Gender::where('is_active', true)->orderBy('order')->orderBy('name')->get();
            } else {
                // Se a tabela não existir, retorna array vazio
                $genders = collect([]);
            }
        } catch (\Exception $e) {
            // Em caso de erro, retorna array vazio
            $genders = collect([]);
        }
        
        return view('tenant.patients.create', compact('genders'));
    }

    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        // Separar dados do paciente dos dados de endereço
        $addressData = [];
        $addressFields = ['postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'pais_id', 'estado_id', 'cidade_id'];
        
        foreach ($addressFields as $field) {
            if (isset($data[$field])) {
                $addressData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        $data['id'] = Str::uuid();
        $patient = Patient::create($data);

        // Criar endereço se houver dados
        if (!empty(array_filter($addressData))) {
            $addressData['id'] = Str::uuid();
            $addressData['patient_id'] = $patient->id;
            PatientAddress::create($addressData);
        }

        return redirect()->route('tenant.patients.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Paciente cadastrado com sucesso.');
    }


    public function show($slug, $id)
    {
        $patient = Patient::with(['gender', 'address'])->findOrFail($id);
        return view('tenant.patients.show', compact('patient'));
    }

    public function edit($slug, $id)
    {
        $patient = Patient::with('address')->findOrFail($id);
        
        try {
            // Verifica se a tabela existe antes de tentar buscar
            $tableExists = Schema::connection('tenant')->hasTable('genders');
            
            if ($tableExists) {
                $genders = Gender::where('is_active', true)->orderBy('order')->orderBy('name')->get();
            } else {
                // Se a tabela não existir, retorna array vazio
                $genders = collect([]);
            }
        } catch (\Exception $e) {
            // Em caso de erro, retorna array vazio
            $genders = collect([]);
        }
        
        return view('tenant.patients.edit', compact('patient', 'genders'));
    }

    public function update(UpdatePatientRequest $request, $slug, $id)
    {
        $patient = Patient::findOrFail($id);
        $data = $request->validated();

        // Separar dados do paciente dos dados de endereço
        $addressData = [];
        $addressFields = ['postal_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'pais_id', 'estado_id', 'cidade_id'];
        
        foreach ($addressFields as $field) {
            if (isset($data[$field])) {
                $addressData[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        $patient->update($data);

        // Atualizar ou criar endereço
        if (!empty(array_filter($addressData))) {
            if ($patient->address) {
                $patient->address->update($addressData);
            } else {
                $addressData['id'] = Str::uuid();
                $addressData['patient_id'] = $patient->id;
                PatientAddress::create($addressData);
            }
        } elseif ($patient->address) {
            // Se não há dados de endereço mas existe um endereço, remover
            $patient->address->delete();
        }

        return redirect()->route('tenant.patients.index', ['slug' => $slug])
            ->with('success', 'Paciente atualizado com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $patient = Patient::findOrFail($id);
        $patient->delete();

        return redirect()->route('tenant.patients.index', ['slug' => $slug])
            ->with('success', 'Paciente removido.');
    }

    /**
     * Mostra formulário para criar/editar login do paciente
     */
    public function showLoginForm($slug, $id)
    {
        try {
            $patient = Patient::with('login')->findOrFail($id);
        } catch (\Exception $e) {
            // Se houver erro ao carregar relacionamento (tabela não existe), carrega sem relacionamento
            $patient = Patient::findOrFail($id);
        }
        return view('tenant.patients.login-form', compact('patient'));
    }

    /**
     * Cria ou atualiza login do paciente
     */
    public function storeLogin(Request $request, $slug, $id)
    {
        $patient = Patient::findOrFail($id);

        // Verifica se a tabela existe antes de continuar
        try {
            $tableExists = Schema::connection('tenant')->hasTable('patient_logins');
            if (!$tableExists) {
                return back()
                    ->withErrors(['error' => 'A tabela de logins de pacientes não foi criada. Por favor, execute as migrations do tenant primeiro.'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Erro ao verificar estrutura do banco de dados. Verifique se as migrations foram executadas.'])
                ->withInput();
        }

        // Tenta verificar se o paciente já tem login (sem carregar relacionamento)
        $hasLogin = false;
        try {
            $hasLogin = PatientLogin::where('patient_id', $patient->id)->exists();
        } catch (\Exception $e) {
            // Se houver erro, assume que não há login
            $hasLogin = false;
        }

        $rules = [
            'email' => [
                'required',
                'email',
            ],
        ];

        if ($hasLogin) {
            $rules['password'] = 'nullable|min:6';
        } else {
            $rules['password'] = 'required|min:6|confirmed';
        }

        $messages = [
            'email.unique' => 'Este e-mail já está sendo usado por outro paciente.',
            'password.required' => 'A senha é obrigatória ao criar um novo login.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas não coincidem.',
        ];

        $request->validate($rules, $messages);

        // Validação manual de email único (após validar formato)
        // Verifica se a tabela existe antes de fazer a query
        try {
            $tableExists = Schema::connection('tenant')->hasTable('patient_logins');

            if ($tableExists) {
                $query = PatientLogin::where('email', $request->email);
                
                // Verifica se o paciente já tem login
                $existingPatientLogin = PatientLogin::where('patient_id', $patient->id)->first();
                if ($existingPatientLogin) {
                    $query->where('id', '!=', $existingPatientLogin->id);
                }
                
                if ($query->exists()) {
                    return back()
                        ->withErrors(['email' => 'Este e-mail já está sendo usado por outro paciente.'])
                        ->withInput();
                }
            }
        } catch (\Exception $e) {
            // Se houver qualquer erro (tabela não existe, etc), registra mas continua
            \Log::warning('Erro ao validar email único. Tabela pode não existir.', [
                'error' => $e->getMessage(),
                'patient_id' => $patient->id
            ]);
        }

        $data = [
            'patient_id' => $patient->id,
            'email' => $request->email,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        // Se a senha foi fornecida, atualiza (o model já faz o hash automaticamente)
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $isNewLogin = !$hasLogin;
        $plainPassword = null;

        // Busca o login existente se houver
        $existingLogin = null;
        if ($hasLogin) {
            try {
                $existingLogin = PatientLogin::where('patient_id', $patient->id)->first();
            } catch (\Exception $e) {
                $existingLogin = null;
                $hasLogin = false;
            }
        }

        if ($existingLogin) {
            // Atualiza login existente
            if (!$request->filled('password')) {
                unset($data['password']);
            } else {
                $plainPassword = $request->password;
            }
            $existingLogin->update($data);
            $patientLogin = $existingLogin->fresh();
            $message = 'Login atualizado com sucesso.';
        } else {
            // Cria novo login - senha é obrigatória
            if (!$request->filled('password')) {
                return back()->withErrors(['password' => 'A senha é obrigatória ao criar um novo login.'])->withInput();
            }
            $plainPassword = $request->password;
            
            // Cria o login (o model faz hash automaticamente da senha)
            try {
                $patientLogin = PatientLogin::create($data);
                $message = 'Login criado com sucesso.';
            } catch (\Illuminate\Database\QueryException $e) {
                // Se a tabela não existir, informa ao usuário
                if (str_contains($e->getMessage(), 'does not exist') || 
                    str_contains($e->getMessage(), 'não existe') ||
                    str_contains($e->getMessage(), 'Undefined table') ||
                    str_contains($e->getMessage(), 'relation') ||
                    str_contains($e->getMessage(), 'relação')) {
                    return back()
                        ->withErrors(['error' => 'A tabela de logins não foi criada. Execute as migrations do tenant primeiro: php artisan tenant:migrate --all'])
                        ->withInput();
                }
                throw $e;
            }
        }

        // Se é um novo login, redireciona para a página de show com a senha
        if ($isNewLogin && $plainPassword) {
            return redirect()->route('tenant.patients.login.show', ['slug' => $slug, 'id' => $patient->id])
                ->with('password', $plainPassword)
                ->with('success', $message);
        }

        return redirect()->route('tenant.patients.index', ['slug' => $slug])
            ->with('success', $message);
    }

    /**
     * Bloqueia/desbloqueia acesso do paciente
     */
    public function toggleLoginStatus($slug, $id)
    {
        $patient = Patient::with('login')->findOrFail($id);

        if (!$patient->login) {
            return redirect()->route('tenant.patients.index', ['slug' => $slug])
                ->withErrors(['error' => 'Paciente não possui login cadastrado.']);
        }

        $patient->login->update([
            'is_active' => !$patient->login->is_active
        ]);

        $status = $patient->login->is_active ? 'habilitado' : 'bloqueado';

        return redirect()->route('tenant.patients.index', ['slug' => $slug])
            ->with('success', "Acesso do paciente {$status} com sucesso.");
    }

    /**
     * Remove login do paciente
     */
    public function destroyLogin($slug, $id)
    {
        $patient = Patient::with('login')->findOrFail($id);

        if (!$patient->login) {
            return redirect()->route('tenant.patients.index', ['slug' => $slug])
                ->withErrors(['error' => 'Paciente não possui login cadastrado.']);
        }

        $patient->login->delete();

        return redirect()->route('tenant.patients.index', ['slug' => $slug])
            ->with('success', 'Login removido com sucesso.');
    }

    /**
     * Mostra informações do login do paciente
     */
    public function showLogin($slug, $id)
    {
        $patient = Patient::with('login')->findOrFail($id);

        if (!$patient->login) {
            return redirect()->route('tenant.patients.login.form', ['slug' => $slug, 'id' => $patient->id])
                ->withErrors(['error' => 'Paciente não possui login cadastrado.']);
        }

        return view('tenant.patients.login-show', compact('patient'));
    }

    /**
     * Envia informações de acesso por e-mail
     */
    public function sendLoginByEmail(Request $request, $slug, $id)
    {
        $patient = Patient::with('login')->findOrFail($id);

        if (!$patient->login) {
            return back()->withErrors(['error' => 'Paciente não possui login cadastrado.']);
        }

        $password = $request->input('password') ?? session('password');
        
        if (!$password) {
            return back()->withErrors(['error' => 'Senha não disponível. Por favor, redefina a senha do paciente.']);
        }

        try {
            $tenant = Tenant::current();
            $tenantName = $tenant?->trade_name ?? $tenant?->legal_name ?? 'Clínica';
            $tenantSlug = $tenant?->subdomain ?? 'tenant';
            $portalUrl = request()->getSchemeAndHttpHost() . '/customer/' . $tenantSlug . '/paciente/login';

            // Usa MailTenantService para respeitar configurações de SMTP do tenant
            // Nota: Para credenciais de login, sempre envia (não verifica notificações.send_email_to_patients)
            // pois é uma ação administrativa, não uma notificação automática
            MailTenantService::send(
                $patient->login->email,
                "Credenciais de Acesso ao Portal",
                'tenant.patients.emails.login-credentials',
                [
                    'patient' => $patient,
                    'login' => $patient->login,
                    'password' => $password,
                    'portalUrl' => $portalUrl,
                    'tenantName' => $tenantName
                ]
            );

            return back()->with('success', 'E-mail enviado com sucesso para ' . $patient->login->email . '!');
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar e-mail de credenciais', [
                'error' => $e->getMessage(),
                'patient_id' => $patient->id
            ]);
            return back()->withErrors(['error' => 'Erro ao enviar e-mail: ' . $e->getMessage()]);
        }
    }

    /**
     * Envia informações de acesso por WhatsApp
     */
    public function sendLoginByWhatsApp(Request $request, $slug, $id)
    {
        $patient = Patient::with('login')->findOrFail($id);

        if (!$patient->login) {
            return back()->withErrors(['error' => 'Paciente não possui login cadastrado.']);
        }

        if (!$patient->phone) {
            return back()->withErrors(['error' => 'Paciente não possui telefone cadastrado.']);
        }

        $password = $request->input('password') ?? session('password');
        
        if (!$password) {
            return back()->withErrors(['error' => 'Senha não disponível. Por favor, redefina a senha do paciente.']);
        }

        try {
            $tenant = Tenant::current();
            $tenantName = $tenant?->trade_name ?? $tenant?->legal_name ?? 'Clínica';
            $tenantSlug = $tenant?->subdomain ?? 'tenant';
            $portalUrl = request()->getSchemeAndHttpHost() . '/customer/' . $tenantSlug . '/paciente/login';

            $message = "🔐 *Credenciais de Acesso ao Portal*\n\n";
            $message .= "Olá, {$patient->full_name}!\n\n";
            $message .= "Suas credenciais de acesso ao portal do paciente foram criadas:\n\n";
            $message .= "📧 *E-mail:* {$patient->login->email}\n";
            $message .= "🔑 *Senha:* {$password}\n";
            $message .= "🔗 *Acesse:* {$portalUrl}\n\n";
            $message .= "Atenciosamente,\n{$tenantName}";

            // Usa WhatsappTenantService para respeitar configurações do tenant
            // Nota: Para credenciais de login, sempre envia (não verifica notificações.send_whatsapp_to_patients)
            // pois é uma ação administrativa, não uma notificação automática
            $success = WhatsappTenantService::send($patient->phone, $message);

            if ($success) {
                return back()->with('success', 'Mensagem WhatsApp enviada com sucesso para ' . $patient->phone . '!');
            } else {
                return back()->withErrors(['error' => 'Falha ao enviar mensagem WhatsApp. Verifique as configurações.']);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar WhatsApp de credenciais', [
                'error' => $e->getMessage(),
                'patient_id' => $patient->id
            ]);
            return back()->withErrors(['error' => 'Erro ao enviar WhatsApp: ' . $e->getMessage()]);
        }
    }
}
