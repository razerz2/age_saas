@props([
    'name',
    'size' => 'text-base',
])

<i {{ $attributes->merge(['class' => trim("mdi mdi-{$name} {$size}")]) }} aria-hidden="true"></i>

