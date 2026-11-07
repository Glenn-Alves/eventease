
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">
                My {{ $type ? \App\Models\Event::TYPES[$type] : '' }} Events
            </h2>

            <a href="{{ route('organizer.events.create') }}"
               class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md">
                New Event
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">

        {{-- Event type menu --}}
        <x-event-type-menu
            route-name="organizer.events.index"
            :type="$type"
            :counts="$counts"
        />

        <div class="flex-1 min-w-0">

            {{-- Flash message --}}
            <x-flash />

            {{-- Event cards --}}
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">

                @forelse ($events as $event)
                    <x-event-card
                        :event="$event"
                        :href="route('organizer.events.show', $event)"
                        organizer
                    />
                @empty
                    <x-empty-state
                        class="col-span-full"
                        icon="calendar"
                        title="No events here yet"
                        text="Create your first event and design its registration form."
                    >
                        <x-action
                            :href="route('organizer.events.create')"
                            icon="plus"
                            variant="primary"
                        >
                            Create an event
                        </x-action>
                    </x-empty-state>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>
