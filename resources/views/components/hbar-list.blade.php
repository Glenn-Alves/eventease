@props(['items', 'empty' => 'No data yet.'])

@php
    $items = collect($items);
    $sum = max((int) $items->sum('value'), 1);
    $max = max((int) $items->max('value'), 1);
@endphp

@if ($items->isEmpty())
    <p class="text-sm text-gray-700">{{ $empty }}</p>
@else
    <ul {{ $attributes->merge(['class' => 'space-y-3']) }}>
        @foreach ($items as $item)
            <li>
                <div class="flex justify-between gap-3 text-sm">
                    <span class="font-medium text-gray-900">{{ $item['label'] }}</span>
                    <span class="text-gray-700">{{ $item['value'] }} ({{ round($item['value'] / $sum * 100) }}%)</span>
                </div>
                <div class="mt-1 h-2.5 rounded bg-gray-200" aria-hidden="true">
                    <div class="h-2.5 rounded bg-slate-900" style="width: {{ round($item['value'] / $max * 100) }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
@endif