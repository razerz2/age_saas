/**
 * Helper para substituir alert() e confirm() nativos
 * Usa Bootstrap Modal e Toast para uma experiência melhor
 */

(function() {
    'use strict';

    // Container para toasts
    let toastContainer = null;

    /**
     * Inicializa o container de toasts se não existir
     */
    function initToastContainer() {
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        return toastContainer;
    }

    /**
     * Exibe um toast (substitui alert())
     * @param {string} message - Mensagem a ser exibida
     * @param {string} type - Tipo: 'success', 'error', 'warning', 'info' (padrão: 'info')
     * @param {number} duration - Duração em ms (padrão: 5000)
     */
    window.showToast = function(message, type = 'info', duration = 5000) {
        const container = initToastContainer();
        
        const toastId = 'toast-' + Date.now();
        const iconMap = {
            'success': '<i class="fas fa-check-circle me-2"></i>',
            'error': '<i class="fas fa-times-circle me-2"></i>',
            'warning': '<i class="fas fa-exclamation-triangle me-2"></i>',
            'info': '<i class="fas fa-info-circle me-2"></i>'
        };
        
        const bgClassMap = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        };

        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast ${bgClassMap[type] || 'bg-info'} text-white`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <div class="toast-header ${bgClassMap[type] || 'bg-info'} text-white">
                ${iconMap[type] || iconMap.info}
                <strong class="me-auto">${type === 'error' ? 'Erro' : type === 'success' ? 'Sucesso' : type === 'warning' ? 'Atenção' : 'Informação'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        container.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: duration > 0,
            delay: duration
        });
        
        bsToast.show();
        
        // Remove o elemento após ser escondido
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    };

    /**
     * Exibe um modal de confirmação (substitui confirm())
     * @param {string} message - Mensagem de confirmação
     * @param {string} title - Título do modal (padrão: 'Confirmar')
     * @param {string} confirmText - Texto do botão de confirmação (padrão: 'Confirmar')
     * @param {string} cancelText - Texto do botão de cancelamento (padrão: 'Cancelar')
     * @param {string} type - Tipo: 'danger', 'warning', 'info' (padrão: 'warning')
     * @returns {Promise<boolean>} - Promise que resolve com true se confirmado, false se cancelado
     */
    window.showConfirm = function(message, title = 'Confirmar', confirmText = 'Confirmar', cancelText = 'Cancelar', type = 'warning') {
        return new Promise((resolve) => {
            // Remove modal existente se houver
            const existingModal = document.getElementById('platform-confirm-modal');
            if (existingModal) {
                existingModal.remove();
            }

            const iconMap = {
                'danger': '<i class="fas fa-exclamation-circle text-danger me-2"></i>',
                'warning': '<i class="fas fa-exclamation-triangle text-warning me-2"></i>',
                'info': '<i class="fas fa-info-circle text-info me-2"></i>'
            };

            const btnClassMap = {
                'danger': 'btn-danger',
                'warning': 'btn-warning',
                'info': 'btn-primary'
            };

            const modal = document.createElement('div');
            modal.id = 'platform-confirm-modal';
            modal.className = 'modal fade';
            modal.setAttribute('tabindex', '-1');
            modal.setAttribute('aria-labelledby', 'platform-confirm-modal-label');
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="platform-confirm-modal-label">
                                ${iconMap[type] || iconMap.warning}
                                ${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText}</button>
                            <button type="button" class="btn ${btnClassMap[type] || btnClassMap.warning}" id="platform-confirm-btn">${confirmText}</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            const bsModal = new bootstrap.Modal(modal);
            
            const confirmBtn = modal.querySelector('#platform-confirm-btn');
            const cancelBtn = modal.querySelector('.btn-secondary');

            const cleanup = () => {
                modal.remove();
            };

            confirmBtn.addEventListener('click', () => {
                bsModal.hide();
                resolve(true);
                cleanup();
            });

            cancelBtn.addEventListener('click', () => {
                bsModal.hide();
                resolve(false);
                cleanup();
            });

            modal.addEventListener('hidden.bs.modal', cleanup);

            bsModal.show();
        });
    };

    /**
     * Helper para substituir confirm() em forms
     * Uso: onsubmit="return confirmSubmit(event, 'Mensagem')"
     */
    window.confirmSubmit = async function(event, message, title = 'Confirmar') {
        event.preventDefault();
        const confirmed = await window.showConfirm(message, title);
        if (confirmed) {
            event.target.submit();
        }
        return false;
    };

    /**
     * Helper para substituir confirm() em botões
     * Uso: onclick="return confirmAction(event, 'Mensagem', function() { ... })"
     */
    window.confirmAction = async function(event, message, callback, title = 'Confirmar') {
        event.preventDefault();
        const confirmed = await window.showConfirm(message, title);
        if (confirmed && typeof callback === 'function') {
            callback();
        }
        return false;
    };

    // Compatibilidade: substituir alert() globalmente (opcional, pode ser removido se preferir usar showToast explicitamente)
    const originalAlert = window.alert;
    window.alert = function(message) {
        window.showToast(message, 'info', 4000);
    };

})();

