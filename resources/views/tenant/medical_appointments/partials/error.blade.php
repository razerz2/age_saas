<div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
    <div class="flex items-start gap-2 text-red-700 dark:text-red-300 text-sm">
        <x-icon name="alert-circle-outline" size="text-base" class="mt-0.5" />
        <span>{{ $message ?? 'Erro ao carregar detalhes do agendamento.' }}</span>
    </div>
</div>
