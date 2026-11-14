
@props(['routeName', 'filters', 'type' => null, 'count' => 0])

@php
    $customDates = $filters['from'] || $filters['to'];
    $active = \App\Support\EventFilters::active($filters);

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

<section aria-label="Search and filter events"
         class="min-w-0 w-full overflow-hidden rounded-xl bg-white p-4 shadow">

    <form method="GET" action="{{ route($routeName) }}" class="min-w-0 w-full space-y-4">

        @if ($type)
            <input type="hidden" name="type" value="{{ $type }}">
        @endif

        {{-- Search --}}
        <div class="min-w-0 w-full">
            <label for="q" class="block text-sm font-medium text-gray-800">
                Search by event name or place
            </label>

            <div class="relative mt-1 min-w-0">
                <x-icon
                    name="search"
                    class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-600"
                />

                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $filters['q'] }}"
                    placeholder="For example: night run, Cebu"
                    class="block w-full min-w-0 rounded-md border-gray-400 text-sm shadow-sm"
                    style="padding-left: 48px !important;"
                >
            </div>
        </div>

        {{-- Date filters --}}
        <div class="grid min-w-0 grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

            <div class="min-w-0">
                <label for="when" class="block text-sm font-medium text-gray-800">
                    When
                </label>

                <select id="when"
                        name="when"
                        class="mt-1 block w-full min-w-0 rounded-md border-gray-400 text-sm shadow-sm">
                    <option value="">Any date</option>

                    @foreach (\App\Support\EventFilters::PRESETS as $key => $label)
                        <option value="{{ $key }}" @selected($filters['when'] === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-0">
                <label for="from" class="block text-sm font-medium text-gray-800">
                    From
                </label>

                <input id="from"
                       name="from"
                       type="date"
                       value="{{ $filters['from']?->format('Y-m-d') }}"
                       class="mt-1 block w-full min-w-0 rounded-md border-gray-400 text-sm shadow-sm">
            </div>

            <div class="min-w-0">
                <label for="to" class="block text-sm font-medium text-gray-800">
                    To
                </label>

                <input id="to"
                       name="to"
                       type="date"
                       value="{{ $filters['to']?->format('Y-m-d') }}"
                       class="mt-1 block w-full min-w-0 rounded-md border-gray-400 text-sm shadow-sm">
            </div>

        </div>

        {{-- Registration status and actions --}}
        <div class="flex min-w-0 flex-wrap items-center justify-between gap-3">

            <label class="flex min-w-0 items-center gap-2 text-sm text-gray-800">
                <input type="checkbox"
                       name="open"
                       value="1"
                       @checked($filters['open'])
                       class="shrink-0 rounded border-gray-500 text-slate-900">

                <span>Open for registration</span>
            </label>

            <div class="flex min-w-0 flex-wrap items-center gap-2">
                <x-action type="submit" icon="search" variant="primary">
                    Search
                </x-action>

                @if ($active)
                    <x-action :href="route($routeName, array_filter(['type' => $type]))" icon="close">
                        Clear filters
                    </x-action>
                @endif
            </div>

        </div>

        {{-- Help text --}}
        <p class="max-w-full break-words text-xs leading-relaxed text-gray-700">
            Dates in From and To are used instead of When.
            "Open for registration" hides full events and closed deadlines.
            Only upcoming events are listed.
        </p>

    </form>

    {{-- Active filters --}}
    <div class="mt-4 flex min-w-0 flex-wrap items-center gap-2" role="status">

        <p class="text-sm text-gray-800">
            <strong>{{ $count }}</strong>
            {{ \Illuminate\Support\Str::plural('event', $count) }} found
        </p>

        @foreach ($chips as $chip)
            <a href="{{ request()->fullUrlWithoutQuery(array_merge($chip['remove'], ['page'])) }}"
               class="tap inline-flex max-w-full items-center gap-1 break-all rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-900 ring-1 ring-inset ring-indigo-700/30 hover:bg-indigo-200">

                <span>{{ $chip['label'] }}</span>

                <x-icon name="close" class="h-3.5 w-3.5 shrink-0" />

                <span class="sr-only">Remove this filter</span>
            </a>
        @endforeach

    </div>
</section>
