<section>
    <h4 class="card-title">Informações do Perfil</h4>
    <p class="card-subtitle mb-4">
        Atualize as informações do seu perfil e o endereço de e-mail da sua conta.
    </p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">{{ __('Nome') }}</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror"
                   id="name"
                   name="name"
                   value="{{ old('name', $user->name) }}"
                   required
                   autofocus
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">{{ __('E-mail') }}</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror"
                   id="email"
                   name="email"
                   value="{{ old('email', $user->email) }}"
                   required
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning mt-3" role="alert">
                    <p class="mb-2">{{ __('Seu endereço de e-mail ainda não foi verificado.') }}</p>
                    <button type="submit" form="send-verification" class="btn btn-sm btn-outline-primary">
                        {{ __('Reenviar e-mail de verificação') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 mb-0 text-success fw-semibold">
                            {{ __('Um novo link de verificação foi enviado para seu e-mail.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> {{ __('Salvar') }}
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-semibold ms-2" 
                      x-data="{ show: true }"
                      x-show="show"
                      x-transition
                      x-init="setTimeout(() => show = false, 2000)">
                    {{ __('Salvo com sucesso.') }}
                </span>
            @endif
        </div>
    </form>
</section>
