@props([
    'name',
    'maxWidth' => 'md', // sm, md, lg, xl, full
])

@php
    $widthClasses = [
        'sm' => 'sm:max-w-md',
        'md' => 'sm:max-w-lg',
        'lg' => 'sm:max-w-2xl',
        'xl' => 'sm:max-w-4xl',
        'full' => 'sm:max-w-6xl',
    ];

    $dialogWidth = $widthClasses[$maxWidth] ?? $widthClasses['md'];
@endphp

<div
    x-data="{
        show: false,
        name: '{{ $name }}',
        openModalCountKey: '__openModals',
        ensureCounterInit() {
            if (typeof window[this.openModalCountKey] !== 'number') {
                window[this.openModalCountKey] = 0;
            }
        },
        incrementCounter() {
            this.ensureCounterInit();
            window[this.openModalCountKey]++;
            document.body.classList.add('overflow-hidden');
        },
        decrementCounter() {
            this.ensureCounterInit();
            window[this.openModalCountKey] = Math.max(0, window[this.openModalCountKey] - 1);
            if (window[this.openModalCountKey] === 0) {
                document.body.classList.remove('overflow-hidden');
            }
        },
        close() {
            if (!this.show) return;
            this.show = false;
            this.decrementCounter();
        },
        open() {
            if (this.show) return;
            this.show = true;
            this.incrementCounter();
            this.$nextTick(() => {
                const focusable = this.$refs.dialogContainer?.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                if (focusable) focusable.focus();
            });
        }
    }"
    x-init="
        const vm = this;
        vm.ensureCounterInit();

        const openHandler = (event) => {
            if (!event.detail || event.detail.name !== vm.name) return;
            vm.open();
        };
        const closeHandler = (event) => {
            if (!event.detail || !event.detail.name || event.detail.name === vm.name) {
                vm.close();
            }
        };

        window.addEventListener('open-modal', openHandler);
        window.addEventListener('close-modal', closeHandler);

        return () => {
            window.removeEventListener('open-modal', openHandler);
            window.removeEventListener('close-modal', closeHandler);
            if (vm.show) {
                vm.decrementCounter();
            }
        };
    "
    x-on:keydown.escape.window="if (show) close()"
    x-cloak
>
    <div
        x-show="show"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9998] flex items-center justify-center px-4 sm:px-0"
        aria-modal="true"
        role="dialog"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-gray-900/60 dark:bg-black/70"
            x-on:click="close()"
        ></div>

        <!-- Dialog -->
        <div
            x-ref="dialogContainer"
            x-transition.duration.200ms
            x-transition.scale.origin.center
            class="relative z-[9999] w-full {{ $dialogWidth }} max-h-[90vh] overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800 flex flex-col"
        >
            <!-- Header -->
            @if (isset($title))
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $title }}
                    </h2>
                    <button
                        type="button"
                        x-on:click="close()"
                        class="inline-flex items-center justify-center rounded-full p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l8 8M6 14L14 6" />
                        </svg>
                    </button>
                </div>
            @endif

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-5 py-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if (isset($footer))
                <div class="border-t border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-end gap-3 dark:border-gray-700 dark:bg-gray-800/80">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
