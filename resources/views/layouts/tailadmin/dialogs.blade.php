<!-- Global TailAdmin dialogs (Alert + Confirm) -->
<div
    data-ui-dialog-root
    x-cloak
    x-data="{
        open: false,
        variant: 'confirm', // 'confirm' | 'alert'
        type: 'info', // success | error | warning | info
        title: '',
        message: '',
        confirmText: 'Confirmar',
        cancelText: 'Cancelar',
        allowOutsideClose: true,
        onConfirm: null,
        onCancel: null,
        focusConfirm: true,

        openConfirm(opts = {}) {
            this.variant = 'confirm'
            this.type = opts.type ?? 'warning'
            this.title = opts.title ?? 'Confirmação'
            this.message = opts.message ?? ''
            this.confirmText = opts.confirmText ?? 'Confirmar'
            this.cancelText = opts.cancelText ?? 'Cancelar'
            this.allowOutsideClose = opts.allowOutsideClose ?? true
            this.onConfirm = opts.onConfirm ?? null
            this.onCancel = opts.onCancel ?? null
            this.focusConfirm = opts.focusConfirm ?? true
            this.open = true
            this.$nextTick(() => {
                if (this.focusConfirm && this.$refs.confirmBtn) this.$refs.confirmBtn.focus()
            })
        },

        openAlert(opts = {}) {
            this.variant = 'alert'
            this.type = opts.type ?? 'info'
            this.title = opts.title ?? 'Aviso'
            this.message = opts.message ?? ''
            this.confirmText = opts.confirmText ?? 'OK'
            this.allowOutsideClose = opts.allowOutsideClose ?? true
            this.onConfirm = opts.onConfirm ?? null
            this.onCancel = null
            this.focusConfirm = opts.focusConfirm ?? true
            this.open = true
            this.$nextTick(() => {
                if (this.focusConfirm && this.$refs.confirmBtn) this.$refs.confirmBtn.focus()
            })
        },

        close(reason = 'close') {
            this.open = false
        },

        cancel(reason = 'cancel') {
            try { if (typeof this.onCancel === 'function') this.onCancel(reason) } catch (e) {}
            this.close(reason)
        },

        accept() {
            try { if (typeof this.onConfirm === 'function') this.onConfirm() } catch (e) {}
            this.close('confirm')
        },

        onOverlayClick() {
            if (!this.allowOutsideClose) return
            if (this.variant === 'confirm') return this.cancel('outside')
            return this.close('outside')
        },

        onEsc() {
            if (!this.open) return
            if (this.variant === 'confirm') return this.cancel('escape')
            return this.close('escape')
        },
    }"
    @ui-confirm.window="openConfirm($event.detail)"
    @ui-alert.window="openAlert($event.detail)"
    @keydown.escape.window="onEsc()"
>
    <!-- Overlay -->
    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-50 z-999999 flex items-center justify-center p-4"
        aria-live="polite"
        aria-atomic="true"
    >
        <div
            class="absolute inset-0 bg-black/50 dark:bg-black/70"
            @click="onOverlayClick()"
        ></div>

        <!-- Dialog -->
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            role="dialog"
            aria-modal="true"
            class="shadow-xl shadow-theme-lg dark:bg-gray-900 dark:bg-gray-dark relative z-10 w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800"
            @click.stop
        >
            <div class="flex items-start gap-4">
                <!-- Icon -->
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                    :class="{
                        'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300': type === 'success',
                        'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300': type === 'error',
                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': type === 'warning',
                        'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300': type === 'info',
                    }"
                >
                    <svg x-show="type === 'success'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="type === 'error'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <svg x-show="type === 'warning'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <svg x-show="type === 'info'" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"></path>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90" x-text="title"></h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300" x-text="message"></p>
                </div>

                <button
                    type="button"
                    class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                    @click="variant === 'confirm' ? cancel('x') : close('x')"
                    aria-label="Fechar"
                >
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z" fill=""></path>
                    </svg>
                </button>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    x-show="variant === 'confirm'"
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-600/30 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-white/5 sm:w-auto"
                    @click="cancel('cancel')"
                    x-text="cancelText"
                ></button>

                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary px-5 py-2.5 text-sm font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/40 sm:w-auto"
                    @click="accept()"
                    x-ref="confirmBtn"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>
</div>

