<div class="space-y-6">
    <div class="mb-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Templates Oficiais</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Gerencie os templates oficiais da Meta para os eventos clínicos padrão do tenant.
        </p>
    </div>

    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900/50 dark:bg-blue-900/20 dark:text-blue-200">
        Esta aba fica disponível somente quando o tenant usa API própria oficial da Meta
        (`whatsapp.driver=tenancy` e `whatsapp.provider=whatsapp_business`).
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Gestão de Templates Oficiais</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Criação, edição, submissão para Meta, sincronização e teste manual.
                </p>
            </div>
            <a href="{{ workspace_route('tenant.settings.whatsapp-official-tenant-templates.index') }}"
               class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Abrir gestão
            </a>
        </div>
    </div>
</div>

