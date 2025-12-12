@extends('layouts.freedash.app')

@section('content')

    <div class="container-fluid">
        
        <!-- Título e breadcrumb -->
        <div class="page-breadcrumb">
            <div class="row">
                <div class="col-7 align-self-center">
                    <h4 class="page-title text-dark font-weight-medium mb-1">Autenticação de Dois Fatores</h4>
                    <div class="d-flex align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb m-0 p-0">
                                <li class="breadcrumb-item"><a href="{{ route('Platform.dashboard') }}"
                                        class="text-muted">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item"><a href="{{ route('Platform.profile.edit') }}"
                                        class="text-muted">Perfil</a>
                                </li>
                                <li class="breadcrumb-item text-muted active" aria-current="page">2FA</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulários -->
        <div class="row">
            <div class="col-lg-8 col-md-10">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                {{-- Status do 2FA --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Status da Autenticação de Dois Fatores</h4>
                        <p class="card-subtitle mb-4">
                            A autenticação de dois fatores adiciona uma camada extra de segurança à sua conta.
                        </p>
                        
                        @if($user->hasTwoFactorEnabled())
                            <div class="alert alert-success">
                                <i class="mdi mdi-shield-check me-2"></i>
                                <strong>2FA Ativado</strong> - Sua conta está protegida com autenticação de dois fatores.
                                <br>
                                <small class="mt-2 d-block">
                                    <strong>Método:</strong> 
                                    @if($user->two_factor_method === 'totp')
                                        Aplicativo Autenticador
                                    @elseif($user->two_factor_method === 'email')
                                        E-mail
                                    @elseif($user->two_factor_method === 'whatsapp')
                                        WhatsApp
                                    @else
                                        Não definido
                                    @endif
                                </small>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="mdi mdi-shield-alert me-2"></i>
                                <strong>2FA Desativado</strong> - Sua conta não está protegida com autenticação de dois fatores.
                            </div>
                        @endif
                    </div>
                </div>

                @if(!$user->hasTwoFactorEnabled())
                    {{-- Escolher método de 2FA --}}
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title">Escolher Método de Autenticação</h4>
                            <p class="card-subtitle mb-4">
                                Selecione como deseja receber os códigos de verificação
                            </p>
                            
                            <form method="POST" action="{{ route('Platform.two-factor.set-method') }}" class="mb-3">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border @if($user->two_factor_method === 'totp') border-primary @endif">
                                            <div class="card-body text-center">
                                                <input type="radio" name="method" value="totp" id="method_totp" 
                                                       class="form-check-input" 
                                                       @if($user->two_factor_method === 'totp' || !$user->two_factor_method) checked @endif
                                                       onchange="this.form.submit()">
                                                <label for="method_totp" class="form-check-label w-100">
                                                    <i class="mdi mdi-cellphone-key" style="font-size: 2rem; color: #2563eb;"></i>
                                                    <h6 class="mt-2">Aplicativo Autenticador</h6>
                                                    <small class="text-muted">Google Authenticator, Authy, etc.</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border @if($user->two_factor_method === 'email') border-primary @endif">
                                            <div class="card-body text-center">
                                                <input type="radio" name="method" value="email" id="method_email" 
                                                       class="form-check-input" 
                                                       @if($user->two_factor_method === 'email') checked @endif
                                                       onchange="this.form.submit()">
                                                <label for="method_email" class="form-check-label w-100">
                                                    <i class="mdi mdi-email" style="font-size: 2rem; color: #2563eb;"></i>
                                                    <h6 class="mt-2">E-mail</h6>
                                                    <small class="text-muted">Código enviado por e-mail</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card border @if($user->two_factor_method === 'whatsapp') border-primary @endif">
                                            <div class="card-body text-center">
                                                <input type="radio" name="method" value="whatsapp" id="method_whatsapp" 
                                                       class="form-check-input" 
                                                       @if($user->two_factor_method === 'whatsapp') checked @endif
                                                       onchange="this.form.submit()">
                                                <label for="method_whatsapp" class="form-check-label w-100">
                                                    <i class="mdi mdi-whatsapp" style="font-size: 2rem; color: #25D366;"></i>
                                                    <h6 class="mt-2">WhatsApp</h6>
                                                    <small class="text-muted">Código enviado por WhatsApp</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Ativar 2FA --}}
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Ativar Autenticação de Dois Fatores</h4>
                            
                            @if($user->two_factor_method === 'totp' || !$user->two_factor_method)
                                <p class="card-subtitle mb-4">
                                    Escaneie o QR Code com um aplicativo autenticador (Google Authenticator, Authy, etc.)
                                </p>

                            @if(!$user->two_factor_secret)
                                <form method="POST" action="{{ route('Platform.two-factor.generate-secret') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="mdi mdi-qrcode-scan me-1"></i>
                                        Gerar QR Code
                                    </button>
                                </form>
                            @else
                                <div class="mb-4">
                                    <p class="mb-3">Escaneie este QR Code com seu aplicativo autenticador:</p>
                                    <div class="text-center mb-3">
                                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <strong>Chave secreta:</strong> {{ session('two_factor_secret', 'Já configurada') }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('Platform.two-factor.confirm') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Digite o código de 6 dígitos do seu aplicativo:</label>
                                        <input type="text" 
                                               name="code" 
                                               id="code" 
                                               class="form-control @error('code') is-invalid @enderror" 
                                               placeholder="000000"
                                               maxlength="6"
                                               pattern="[0-9]{6}"
                                               required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="mdi mdi-check-circle me-1"></i>
                                        Confirmar e Ativar
                                    </button>
                                </form>
                            @elseif($user->two_factor_method === 'email' || $user->two_factor_method === 'whatsapp')
                                @if(session('two_factor_pending_activation'))
                                    <div class="alert alert-info">
                                        <i class="mdi mdi-information-outline me-2"></i>
                                        <strong>Código enviado!</strong> Verifique seu {{ $user->two_factor_method === 'email' ? 'e-mail' : 'WhatsApp' }} e digite o código de 6 dígitos recebido abaixo para ativar o 2FA.
                                    </div>
                                    
                                    <form method="POST" action="{{ route('Platform.two-factor.confirm-with-code') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="code_sent" class="form-label">Código de verificação:</label>
                                            <input type="text" 
                                                   name="code" 
                                                   id="code_sent" 
                                                   class="form-control @error('code') is-invalid @enderror" 
                                                   placeholder="000000"
                                                   maxlength="6"
                                                   pattern="[0-9]{6}"
                                                   required
                                                   autofocus>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Digite o código de 6 dígitos que foi enviado via {{ $user->two_factor_method === 'email' ? 'e-mail' : 'WhatsApp' }}.
                                            </small>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-success">
                                                <i class="mdi mdi-check-circle me-1"></i>
                                                Confirmar e Ativar 2FA
                                            </button>
                                            <form method="POST" action="{{ route('Platform.two-factor.activate-with-code') }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="method" value="{{ $user->two_factor_method }}">
                                                <button type="submit" class="btn btn-outline-secondary">
                                                    <i class="mdi mdi-refresh me-1"></i>
                                                    Reenviar Código
                                                </button>
                                            </form>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning">
                                        <i class="mdi mdi-alert-outline me-2"></i>
                                        <strong>Atenção:</strong> Para ativar o 2FA via {{ $user->two_factor_method === 'email' ? 'e-mail' : 'WhatsApp' }}, você precisa receber e confirmar um código de verificação.
                                    </div>
                                    <p class="card-subtitle mb-4">
                                        Clique no botão abaixo para enviar um código de verificação via {{ $user->two_factor_method === 'email' ? 'e-mail' : 'WhatsApp' }}.
                                    </p>
                                    
                                    <form method="POST" action="{{ route('Platform.two-factor.activate-with-code') }}">
                                        @csrf
                                        <input type="hidden" name="method" value="{{ $user->two_factor_method }}">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="mdi mdi-{{ $user->two_factor_method === 'email' ? 'email' : 'whatsapp' }} me-1"></i>
                                            Enviar Código via {{ ucfirst($user->two_factor_method) }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @else
                    {{-- Desativar 2FA --}}
                    <div class="card border-danger">
                        <div class="card-body">
                            <h4 class="card-title text-danger">Desativar Autenticação de Dois Fatores</h4>
                            <p class="card-subtitle mb-4 text-muted">
                                Desativar o 2FA reduzirá a segurança da sua conta.
                            </p>
                            <form method="POST" action="{{ route('Platform.two-factor.disable') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password" class="form-label">Confirme sua senha para desativar:</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="mdi mdi-shield-off me-1"></i>
                                    Desativar 2FA
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Códigos de Recuperação --}}
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Códigos de Recuperação</h4>
                            <p class="card-subtitle mb-4">
                                Use estes códigos para acessar sua conta caso perca acesso ao seu dispositivo autenticador.
                                <strong>Guarde-os em local seguro!</strong>
                            </p>

                            @if(session('two_factor_recovery_codes'))
                                <div class="alert alert-info">
                                    <strong>Novos códigos gerados! Guarde-os agora:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach(session('two_factor_recovery_codes') as $code)
                                            <li><code>{{ $code }}</code></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($recoveryCodes && count($recoveryCodes) > 0)
                                <div class="alert alert-warning">
                                    <strong>Códigos de recuperação disponíveis:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach($recoveryCodes as $code)
                                            <li><code>{{ $code }}</code></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <strong>Nenhum código de recuperação disponível.</strong> Gere novos códigos abaixo.
                                </div>
                            @endif

                            <form method="POST" action="{{ route('Platform.two-factor.regenerate-recovery-codes') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="password_regen" class="form-label">Confirme sua senha para regenerar:</label>
                                    <input type="password" 
                                           name="password" 
                                           id="password_regen" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="mdi mdi-refresh me-1"></i>
                                    Regenerar Códigos de Recuperação
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('layouts.freedash.footer')
@endsection

