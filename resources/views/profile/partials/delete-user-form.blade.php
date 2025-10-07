<section>
    <h4 class="card-title text-danger">Excluir Conta</h4>
    <p class="card-subtitle mb-4">
        Após excluir sua conta, todos os dados serão permanentemente removidos.
        Faça o download de quaisquer informações que deseja manter antes de prosseguir.
    </p>

    <button type="button" class="btn btn-danger" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        <i class="fas fa-user-times me-1"></i> {{ __('Excluir Conta') }}
    </button>

    {{-- Modal de confirmação --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ __('Confirmar exclusão de conta') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white"
                            x-on:click="$dispatch('close')"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-3">
                            {{ __('Tem certeza de que deseja excluir permanentemente sua conta?') }}
                        </p>
                        <p class="small text-muted">
                            {{ __('Esta ação é irreversível e todos os seus dados serão apagados do sistema.') }}
                        </p>

                        <div class="mt-4">
                            <label for="password" class="form-label fw-semibold">{{ __('Senha') }}</label>
                            <input id="password" name="password" type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="{{ __('Digite sua senha para confirmar') }}" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" x-on:click="$dispatch('close')">
                            <i class="fas fa-times me-1"></i> {{ __('Cancelar') }}
                        </button>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> {{ __('Excluir Conta') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal>
</section>
