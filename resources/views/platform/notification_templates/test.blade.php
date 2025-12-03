{{-- Modal de Teste --}}
<div class="modal fade" id="testModal" tabindex="-1" aria-labelledby="testModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Teste - <span id="testTemplateName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="testTemplateId">
                <input type="hidden" id="testChannel">

                {{-- Email (se canal = email) --}}
                <div id="testEmailGroup" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" id="testEmail" class="form-control" placeholder="exemplo@email.com">
                    </div>
                </div>

                {{-- Número (se canal = whatsapp) --}}
                <div id="testPhoneGroup" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Número WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" id="testPhone" class="form-control" placeholder="5511999999999">
                        <small class="form-text text-muted">Formato: 5511999999999 (código do país + DDD + número)</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSendTest">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Teste
                </button>
            </div>
        </div>
    </div>
</div>

