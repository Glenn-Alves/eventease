@props(['routeName', 'filters', 'type' => null, 'count' => 0])

@php
    $customDates = $filters['from'] || $filters['to'];
    $active = \App\Support\EventFilters::active($filters);

    // Filters that live inside the sliding panel (the search words are already visible in the box)
    $panelCount = ($filters['when'] && ! $customDates ? 1 : 0)
        + ($filters['from'] ? 1 : 0)
        + ($filters['to'] ? 1 : 0)
        + ($filters['open'] ? 1 : 0);

    // One chip for every filter that is on, each with a way to remove it
    $chips = [];

    if ($filters['q'] !== '') {
        $chips[] = ['label' => 'Search: "'.$filters['q'].'"', 'remove' => ['q']];
    }
    if ($filters['when'] && ! $customDates) {
        $chips[] = ['label' => \App\Support\EventFilters::PRESETS[$filters['when']], 'remove' => ['when']];
    }
    if ($filters['from']) {
        $chips[] = ['label' => 'From '.$filters['from']->format('M d, Y'), 'remove' => ['from']];
    }
    if ($filters['to']) {
        $chips[] = ['label' => 'Until '.$filters['to']->format('M d, Y'), 'remove' => ['to']];
    }
    if ($filters['open']) {
        $chips[] = ['label' => 'Open for registration', 'remove' => ['open']];
    }
@endphp

<section aria-label="Search and filter events" class="rounded-xl bg-white p-4 shadow"
         x-data="{ open: false }" x-on:keydown.escape="open = false">

    <form method="GET" action="{{ route($routeName) }}">
        @if ($type)
            <input type="hidden" name="type" value="{{ $type }}">
        @endif

        {{-- The bar: always visible --}}
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[14rem] flex-1">
                <label for="q" class="sr-only">Search by event name or place</label>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-600" />
                <input id="q" name="q" type="search" value="{{ $filters['q'] }}"
                       placeholder="Search by event name or place, for example: night run, Cebu"
                       x-on:focus="open = true" x-on:click="open = true"
                       class="w-full rounded-md border-gray-400 ps-10 text-sm shadow-sm">
            </div>

            <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()"
                    aria-controls="filter-panel"
                    class="tap inline-flex items-center gap-2 rounded-md border border-slate-900 bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-50">
                Filters
                @if ($panelCount > 0)
                    <span class="rounded-full bg-slate-900 px-2 py-0.5 text-xs font-semibold text-white">
                        {{ $panelCount }}<span class="sr-only"> filters on</span>
                    </span>
                @endif
                <x-icon name="chevron" class="h-4 w-4 transition-transform duration-300"
                        x-bind:class="open ? 'rotate-180' : ''" />
            </button>

            <x-action type="submit" icon="search" variant="primary">Search</x-action>
        </div>

        {{-- The panel: slides down when the search box is clicked --}}
        <div id="filter-panel" inert
             class="grid transition-[grid-template-rows] duration-300 ease-out"
             style="grid-template-rows: 0fr;"
             x-bind:style="open ? 'grid-template-rows: 1fr' : 'grid-template-rows: 0fr'"
             x-effect="open ? $el.removeAttribute('inert') : $el.setAttribute('inert', '')">

            <div class="min-h-0 overflow-hidden">
                <div class="space-y-3 pt-4 transition duration-300 ease-out"
                     x-bind:class="open ? 'translate-y-0 opacity-100' : '-translate-y-3 opacity-0'">

                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="when" class="block text-sm font-medium text-gray-800">When</label>
                            <select id="when" name="when" class="mt-1 rounded-md border-gray-400 text-sm shadow-sm">
                                <option value="">Any date</option>
                                @foreach (\App\Support\EventFilters::PRESETS as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['when'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="from" class="block text-sm font-medium text-gray-800">From</label>
                            <input id="from" name="from" type="date" value="{{ $filters['from']?->format('Y-m-d') }}"
                                   class="mt-1 rounded-md border-gray-400 text-sm shadow-sm">
                        </div>

                        <div>
                            <label for="to" class="block text-sm font-medium text-gray-800">To</label>
                            <input id="to" name="to" type="date" value="{{ $filters['to']?->format('Y-m-d') }}"
                                   class="mt-1 rounded-md border-gray-400 text-sm shadow-sm">
                        </div>

                        <label class="flex items-center gap-2 pb-2 text-sm text-gray-800">
                            <input type="checkbox" name="open" value="1" @checked($filters['open'])
                                   class="rounded border-gray-500 text-slate-900">
                            Open for registration
                        </label>
                    </div>

                    <p class="text-xs text-gray-700">
                        Dates in From and To are used instead of When. "Open for registration" hides full events and closed deadlines.
                        Only upcoming events are listed.
                    </p>
                </div>
            </div>
        </div>
    </form>

    {{-- Result count and the filters that are on --}}
    <div class="mt-4 flex flex-wrap items-center gap-2" role="status">
        <p class="text-sm text-gray-800">
            <strong>{{ $count }}</strong> {{ \Illuminate\Support\Str::plural('event', $count) }} found
        </p>

        @foreach ($chips as $chip)
            <a href="{{ request()->fullUrlWithoutQuery(array_merge($chip['remove'], ['page'])) }}"
               class="tap inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-900 ring-1 ring-inset ring-indigo-700/30 hover:bg-indigo-200">
                {{ $chip['label'] }}
                <x-icon name="close" class="h-3.5 w-3.5" />
                <span class="sr-only">Remove this filter</span>
            </a>
        @endforeach

        @if ($active)
            <a href="{{ route($routeName, array_filter(['type' => $type])) }}"
               class="tap inline-flex items-center text-sm font-medium text-indigo-800 underline">Clear all</a>
        @endif
    </div>
</section>