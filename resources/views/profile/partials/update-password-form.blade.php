<section>
    <h4 class="card-title">Atualizar Senha</h4>
    <p class="card-subtitle mb-4">
        Garanta que sua conta esteja protegida com uma senha longa e segura.
    </p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold">
                {{ __('Senha Atual') }}
            </label>
            <div class="input-group">
                <input type="password" 
                       name="current_password" 
                       id="update_password_current_password" 
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('update_password_current_password')" title="Mostrar/Ocultar senha">
                    <i class="fas fa-eye" id="update_password_current_password-eye-icon"></i>
                </button>
            </div>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold">
                {{ __('Nova Senha') }}
            </label>
            <div class="input-group">
                <input type="password" 
                       name="password" 
                       id="update_password_password" 
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('update_password_password')" title="Mostrar/Ocultar senha">
                    <i class="fas fa-eye" id="update_password_password-eye-icon"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()">
                    <i class="fas fa-refresh me-1"></i> Gerar
                </button>
            </div>
            <small class="form-text text-muted">Mínimo 8 caracteres com maiúscula, minúscula, número e caractere especial</small>
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label fw-semibold">
                {{ __('Confirmar Nova Senha') }}
            </label>
            <div class="input-group">
                <input type="password" 
                       name="password_confirmation" 
                       id="update_password_password_confirmation" 
                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                       autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('update_password_password_confirmation')" title="Mostrar/Ocultar senha">
                    <i class="fas fa-eye" id="update_password_password_confirmation-eye-icon"></i>
                </button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-lock me-1"></i> {{ __('Salvar') }}
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small fw-semibold ms-2" 
                      x-data="{ show: true }"
                      x-show="show"
                      x-transition
                      x-init="setTimeout(() => show = false, 2000)">
                    {{ __('Senha atualizada com sucesso.') }}
                </span>
            @endif
        </div>
    </form>
</section>
