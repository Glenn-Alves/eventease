@props(['href' => null, 'icon' => null, 'variant' => 'secondary', 'type' => 'button', 'target' => null, 'trailing' => false])

@php
    $variants = [
        'primary' => 'border border-slate-900 bg-slate-900 text-white hover:bg-slate-700',
        'secondary' => 'border border-slate-900 bg-white text-slate-900 hover:bg-slate-50',
        'danger' => 'border border-red-700 bg-white text-red-800 hover:bg-red-50',
    ];

    $classes = 'tap inline-flex items-center justify-center gap-2 rounded-md px-4 py-2.5 text-sm font-semibold transition '
        .($variants[$variant] ?? $variants['secondary']);
@endphp

@if ($href)
    <a href="{{ $href }}" @if ($target) target="{{ $target }}" rel="noopener noreferrer" @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon && ! $trailing)
            <x-icon :name="$icon" class="h-5 w-5" />
        @endif
        <span>{{ $slot }}</span>
        @if ($icon && $trailing)
            <x-icon :name="$icon" class="h-5 w-5" />
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon && ! $trailing)
            <x-icon :name="$icon" class="h-5 w-5" />
        @endif
        <span>{{ $slot }}</span>
        @if ($icon && $trailing)
            <x-icon :name="$icon" class="h-5 w-5" />
        @endif
    </button>
@endif