@props(['value', 'total', 'label' => 'Progress', 'doneLabel' => 'Done', 'todoLabel' => 'Not yet'])

@php
    $percent = $total > 0 ? (int) round($value / $total * 100) : 0;
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-6']) }}>
    <div class="relative h-32 w-32 shrink-0">
        <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90" role="img" aria-label="{{ $label }}: {{ $percent }} percent">
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e5e7eb" stroke-width="4" />
            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#15803d" stroke-width="4" pathLength="100"
                    stroke-dasharray="{{ $percent }} {{ 100 - $percent }}" />
        </svg>
        <span class="absolute inset-0 flex items-center justify-center text-2xl font-bold text-gray-900">{{ $percent }}%</span>
    </div>

    <ul class="space-y-2 text-sm text-gray-800">
        <li class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-sm bg-green-700" aria-hidden="true"></span>
            {{ $doneLabel }}: <strong>{{ $value }}</strong>
        </li>
        <li class="flex items-center gap-2">
            <span class="inline-block h-3 w-3 rounded-sm bg-gray-300 ring-1 ring-gray-500" aria-hidden="true"></span>
            {{ $todoLabel }}: <strong>{{ max($total - $value, 0) }}</strong>
        </li>
    </ul>
</div>