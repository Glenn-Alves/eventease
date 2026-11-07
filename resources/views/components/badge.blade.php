@props(['tone' => 'gray', 'icon' => null])

@php
    $tones = [
        'green' => 'bg-green-100 text-green-900 ring-green-700/30',
        'amber' => 'bg-amber-100 text-amber-900 ring-amber-700/30',
        'red' => 'bg-red-100 text-red-900 ring-red-700/30',
        'blue' => 'bg-indigo-100 text-indigo-900 ring-indigo-700/30',
        'gray' => 'bg-gray-100 text-gray-800 ring-gray-600/30',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset '.($tones[$tone] ?? $tones['gray'])]) }}>
    @if ($icon)
        <x-icon :name="$icon" class="h-3.5 w-3.5" />
    @endif
    {{ $slot }}
</span>