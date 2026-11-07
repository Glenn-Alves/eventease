
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

            {{-- Event cards --}}
            <div class="flex-1 min-w-0 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 content-start">

                @forelse ($events as $event)
                    <x-event-card
                        :event="$event"
                        :href="route('events.show', $event)"
                    />
                @empty
                    <x-empty-state
                        class="col-span-full"
                        icon="calendar"
                        title="No upcoming events here yet"
                        text="Pick another type from the menu, or come back later."
                    />
                @endforelse

            </div>
        </div>
    </div>
</x-public-layout>