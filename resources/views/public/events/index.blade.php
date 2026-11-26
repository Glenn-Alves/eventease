
<x-public-layout title="Events | EventEase">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <h1 class="text-2xl font-bold text-slate-900">
            {{ $type ? \App\Models\Event::TYPES[$type] : 'All' }} events
        </h1>

        <p class="mt-1 text-gray-600">
            Browse upcoming events. You need an account to register.
        </p>

        <div class="mt-8 flex flex-col md:flex-row gap-6">

            {{-- Event type menu --}}
            <x-event-type-menu
                route-name="home"
                :type="$type"
                :counts="$counts"
            />

            {{-- Event filters and cards --}}
            <div class="min-w-0 flex-1 space-y-6"
                x-data="{ loading: false }"
                x-on:events-loading.window="loading = true; setTimeout(() => loading = false, 8000)"
                x-on:pageshow.window="if ($event.persisted) loading = false">

                <x-event-filters
                    route-name="home"
                    :filters="$filters"
                    :type="$type"
                    :count="$events->count()"
                />

                {{-- Placeholder cards, shown the moment a search or filter is sent --}}
                <div x-show="loading" style="display: none;" role="status"
                    class="grid content-start gap-6 sm:grid-cols-2 lg:grid-cols-3">

                    <span class="sr-only">Loading events</span>

                    @for ($i = 0; $i < 6; $i++)
                        <x-event-card-skeleton />
                    @endfor

                </div>

                {{-- Real event cards --}}
                <div x-show="! loading" class="grid content-start gap-6 sm:grid-cols-2 lg:grid-cols-3">

                    @forelse ($events as $event)
                        <x-event-card
                            :event="$event"
                            :href="route('events.show', $event)"
                        />
                    @empty
                        @if (\App\Support\EventFilters::active($filters))
                            <x-empty-state
                                class="col-span-full"
                                icon="search"
                                title="No events match your search"
                                text="Try different words, remove a filter, or pick another type from the menu."
                            >
                                <x-action
                                    :href="route('home', array_filter(['type' => $type]))"
                                    icon="close"
                                >
                                    Clear filters
                                </x-action>
                            </x-empty-state>
                        @else
                            <x-empty-state
                                class="col-span-full"
                                icon="calendar"
                                title="No upcoming events here yet"
                                text="Pick another type from the menu, or come back later."
                            />
                        @endif
                    @endforelse

                </div>
            </div>
        </div>
    </div>
</x-public-layout>
