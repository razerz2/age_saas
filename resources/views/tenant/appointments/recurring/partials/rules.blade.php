@php /** @var \App\Models\Tenant\RecurringAppointment $recurring */ @endphp
<div class="flex flex-wrap gap-1">
    @foreach($recurring->rules as $rule)
        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 dark:bg-gray-900\/20 dark:text-gray-300 text-xs font-medium rounded-full">
            {{ ucfirst($rule->weekday) }} {{ $rule->start_time }}–{{ $rule->end_time }}
        </span>
    @endforeach
</div>
