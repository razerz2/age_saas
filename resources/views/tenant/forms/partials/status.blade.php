@if($form->is_active)
    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300 text-xs font-medium rounded-full">
        Ativo
    </span>
@else
    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 text-xs font-medium rounded-full">
        Inativo
    </span>
@endif
