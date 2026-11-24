@props(['code', 'title', 'message', 'icon' => 'warning', 'tone' => 'amber'])

@php
    $circle = [
        'amber' => 'bg-amber-50 text-amber-800',
        'red' => 'bg-red-50 text-red-700',
        'blue' => 'bg-indigo-50 text-indigo-700',
    ][$tone] ?? 'bg-gray-100 text-gray-700';
@endphp

<x-public-layout :title="$title.' | EventEase'">
    <div class="mx-auto flex min-h-[60vh] max-w-xl flex-col items-center justify-center px-4 py-16 text-center">
        <span class="flex h-16 w-16 items-center justify-center rounded-full {{ $circle }}">
            <x-icon :name="$icon" class="h-8 w-8" />
        </span>

        <p class="sr-only">Error {{ $code }}</p>
        <p class="mt-6 font-serif text-7xl font-bold tracking-wide text-slate-900" aria-hidden="true">{{ $code }}</p>

        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $title }}</h1>
        <p class="mt-3 text-gray-800">{{ $message }}</p>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            {{ $slot }}
        </div>
    </div>
</x-public-layout>