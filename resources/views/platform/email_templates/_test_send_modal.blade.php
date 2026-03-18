@php
    $modalId = $modalId ?? 'emailTemplateTestSendModal';
    $routeName = $routeName ?? '';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route($routeName, $template) }}">
                @csrf
                <input type="hidden" name="test_send_modal" value="1">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $modalId }}Label">Testar envio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        O envio de teste usa o assunto e o body atuais deste template, com preenchimento dummy/fallback para placeholders ausentes.
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="{{ $modalId }}_destination_email">Email de destino</label>
                        <input
                            type="email"
                            class="form-control @error('destination_email') is-invalid @enderror"
                            id="{{ $modalId }}_destination_email"
                            name="destination_email"
                            value="{{ old('destination_email') }}"
                            placeholder="exemplo@dominio.com"
                            required
                        >
                        @error('destination_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Enviar teste</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const mustOpenModal = @json((bool) old('test_send_modal') || $errors->has('destination_email'));
        if (!mustOpenModal) {
            return;
        }

        const modalEl = document.getElementById(@json($modalId));
        if (!modalEl || typeof bootstrap === 'undefined') {
            return;
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    })();
</script>
@endpush

