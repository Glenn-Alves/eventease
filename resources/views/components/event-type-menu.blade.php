@props(['routeName', 'type' => null, 'counts'])

@php $items = ['' => 'All Events'] + \App\Models\Event::TYPES; @endphp

<aside class="md:w-56 shrink-0">
    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Menu</p>

    <nav class="bg-white rounded shadow p-2 flex md:flex-col gap-1 overflow-x-auto">
        @foreach ($items as $value => $label)
            @php
                $active = ($type ?? '') === (string) $value;
                $count = $value === '' ? $counts->sum() : ($counts[$value] ?? 0);
            @endphp

            <a href="{{ $value === '' ? route($routeName) : route($routeName, ['type' => $value]) }}"
               class="flex items-center justify-between gap-3 px-3 py-2 rounded text-sm whitespace-nowrap {{ $active ? 'bg-gray-800 text-white' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>{{ $label }}</span>
                <span class="text-xs {{ $active ? 'text-gray-300' : 'text-gray-500' }}">{{ $count }}</span>
            </a>
        @endforeach
    </nav>
</aside>